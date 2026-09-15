<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Route as TenantRoute;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\FuecGeneratorService;
use App\Services\Tenant\OperationValidationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    /**
     * Listado de Órdenes de Servicio (Estilo sys-POS).
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');

        $query = ServiceOrder::with(['client', 'vehicle', 'driver', 'fuecDocument'])
            ->orderByDesc('scheduled_start_time');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhere('origin', 'like', "%{$term}%")
                    ->orWhere('destination', 'like', "%{$term}%")
                    ->orWhereHas('client', function ($clientQ) use ($term) {
                        $clientQ->where('business_name', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('document_number', 'like', "%{$term}%");
                    })
                    ->orWhereHas('vehicle', function ($vehicleQ) use ($term) {
                        $vehicleQ->where('plate', 'like', "%{$term}%");
                    });
            });
        }

        if ($estado) {
            $query->where('status', $estado);
        }

        if ($fecha) {
            $query->whereDate('scheduled_start_time', $fecha);
        }

        $totalOrdenes = ServiceOrder::count();
        $ordenesEnRuta = ServiceOrder::where('status', 'En Progreso')->count();
        $enProgreso = $ordenesEnRuta;
        $programadas = ServiceOrder::where('status', 'Programada')->count();
        $finalizadasHoy = ServiceOrder::where('status', 'Finalizada')
            ->whereDate('actual_end_time', now()->toDateString())
            ->count();

        $ordenes = $query->paginate(12)->withQueryString();

        return view('ordenes.index', compact(
            'ordenes',
            'term',
            'estado',
            'fecha',
            'totalOrdenes',
            'ordenesEnRuta',
            'enProgreso',
            'programadas',
            'finalizadasHoy'
        ));
    }

    /**
     * Formulario de creación de orden de servicio.
     */
    public function create(): View
    {
        $clientes = Client::orderBy('business_name')->get();
        $contratos = Contract::whereIn('status', ['Vigente', 'Activo'])->orderBy('contract_number')->get();
        $vehiculos = Vehicle::where('status', 'Activo')->orderBy('plate')->get();
        $conductores = Employee::orderBy('name')->get();
        $rutas = TenantRoute::where('is_active', true)->orderBy('name')->get();

        return view('ordenes.create', compact('clientes', 'contratos', 'vehiculos', 'conductores', 'rutas'));
    }

    /**
     * Guarda una nueva orden de servicio.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:employees,id'],
            'support_driver_id' => ['nullable', 'exists:employees,id'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'scheduled_start_time' => ['required', 'date'],
            'scheduled_end_time' => ['nullable', 'date', 'after_or_equal:scheduled_start_time'],
            'passengers_count' => ['required', 'integer', 'min:1'],
            'passenger_contact_name' => ['nullable', 'string', 'max:150'],
            'passenger_contact_phone' => ['nullable', 'string', 'max:50'],
            'service_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:Pendiente,Asignada,En Progreso,Finalizada,Cancelada'],
        ]);

        // Generar número correlativo si no existe
        $consecutive = ServiceOrder::count() + 1;
        $validated['order_number'] = 'ODS-'.now()->format('Y').'-'.str_pad((string) $consecutive, 4, '0', STR_PAD_LEFT);

        if (empty($validated['scheduled_end_time'])) {
            $validated['scheduled_end_time'] = Carbon::parse($validated['scheduled_start_time'])->addHours(4);
        }

        $orden = ServiceOrder::create($validated);

        return redirect()->route('ordenes.show', $orden->id)
            ->with('success', "Orden de Servicio #{$orden->order_number} creada exitosamente.");
    }

    /**
     * Detalle operativo de la orden y estado de emisión de FUEC.
     */
    public function show(ServiceOrder $ordene): View
    {
        $ordene->load(['client', 'contract', 'vehicle', 'driver', 'supportDriver', 'fuecDocument', 'incidents', 'approvalRequests.requestedBy', 'approvalRequests.reviewedBy']);

        $today = now()->toDateString();
        $hasChecklistToday = $ordene->vehicle
            ? $ordene->vehicle->checklists()->whereDate('date', $today)->where('is_approved', true)->exists()
            : false;

        return view('ordenes.show', compact('ordene', 'hasChecklistToday'));
    }

    /**
     * Formulario de edición de la orden.
     */
    public function edit(ServiceOrder $ordene): View
    {
        $clientes = Client::orderBy('business_name')->get();
        $contratos = Contract::whereIn('status', ['Vigente', 'Activo'])->orderBy('contract_number')->get();
        $vehiculos = Vehicle::orderBy('plate')->get();
        $conductores = Employee::orderBy('name')->get();
        $rutas = TenantRoute::where('is_active', true)->orderBy('name')->get();

        return view('ordenes.edit', compact('ordene', 'clientes', 'contratos', 'vehiculos', 'conductores', 'rutas'));
    }

    /**
     * Actualiza los datos de la orden de servicio.
     */
    public function update(Request $request, ServiceOrder $ordene): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:employees,id'],
            'support_driver_id' => ['nullable', 'exists:employees,id'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'scheduled_start_time' => ['required', 'date'],
            'scheduled_end_time' => ['nullable', 'date', 'after_or_equal:scheduled_start_time'],
            'passengers_count' => ['required', 'integer', 'min:1'],
            'passenger_contact_name' => ['nullable', 'string', 'max:150'],
            'passenger_contact_phone' => ['nullable', 'string', 'max:50'],
            'service_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:Pendiente,Asignada,En Progreso,Finalizada,Cancelada'],
        ]);

        $ordene->update($validated);

        return redirect()->route('ordenes.show', $ordene->id)
            ->with('success', "Orden de Servicio #{$ordene->order_number} actualizada exitosamente.");
    }

    /**
     * Actualiza el estado de la orden de servicio con validaciones normativas.
     */
    public function updateStatus(Request $request, ServiceOrder $ordene, OperationValidationService $validationService): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Pendiente,Asignada,En Progreso,Finalizada,Cancelada'],
            'mileage' => ['nullable', 'numeric', 'min:0'],
            'service_notes' => ['nullable', 'string'],
        ]);

        try {
            $validationService->validateStatusTransition($ordene, $validated['status']);

            $updateData = ['status' => $validated['status']];

            if ($validated['status'] === 'En Progreso') {
                $updateData['actual_start_time'] = now();
                if (! empty($validated['mileage'])) {
                    $updateData['start_mileage'] = $validated['mileage'];
                    $ordene->vehicle?->update(['current_mileage' => $validated['mileage']]);
                }
            } elseif ($validated['status'] === 'Finalizada') {
                $updateData['actual_end_time'] = now();
                if (! empty($validated['mileage'])) {
                    $updateData['end_mileage'] = $validated['mileage'];
                    $ordene->vehicle?->update(['current_mileage' => $validated['mileage']]);
                }
            }

            if (! empty($validated['service_notes'])) {
                $updateData['service_notes'] = $ordene->service_notes
                    ? $ordene->service_notes."\n".$validated['service_notes']
                    : $validated['service_notes'];
            }

            $ordene->update($updateData);

            return redirect()->route('ordenes.show', $ordene->id)
                ->with('success', "Estado de la orden actualizado a '{$validated['status']}' exitosamente.");
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo actualizar el estado: '.$e->getMessage());
        }
    }

    /**
     * Generar FUEC Oficial para esta orden.
     */
    public function emitirFuec(ServiceOrder $ordene, FuecGeneratorService $generator): RedirectResponse
    {
        try {
            $fuec = $generator->generate($ordene);

            return redirect()->route('ordenes.show', $ordene->id)
                ->with('success', "¡FUEC Oficial emitido con éxito! Número reglamentario: {$fuec->fuec_number}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al emitir FUEC: '.$e->getMessage());
        }
    }

    /**
     * Clonar / Duplicar una Orden de Servicio (Funcionalidad Clave de V1 duplicar.php).
     */
    public function duplicate(ServiceOrder $ordene): RedirectResponse
    {
        $newOrderNumber = 'ODS-'.date('Y').'-'.str_pad((string) ((ServiceOrder::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

        $newOrder = $ordene->replicate([
            'order_number',
            'status',
            'start_mileage',
            'end_mileage',
            'actual_start_time',
            'actual_end_time',
            'invoice_number',
            'invoice_file',
        ]);

        $newOrder->order_number = $newOrderNumber;
        $newOrder->status = 'Pendiente';
        $newOrder->scheduled_start_time = now()->addHours(2);
        $newOrder->scheduled_end_time = now()->addHours(4);
        $newOrder->created_at = now();
        $newOrder->updated_at = now();
        $newOrder->save();

        return redirect()->route('ordenes.edit', $newOrder->id)
            ->with('success', "Orden clonada con éxito como #{$newOrder->order_number}. Puedes ajustar horarios, vehículo y conductor.");
    }

    /**
     * Eliminar orden (Soft Delete).
     */
    public function destroy(ServiceOrder $ordene): RedirectResponse
    {
        $numero = $ordene->order_number;
        $ordene->delete();

        return redirect()->route('ordenes.index')
            ->with('success', "Orden #{$numero} eliminada satisfactoriamente.");
    }
}
