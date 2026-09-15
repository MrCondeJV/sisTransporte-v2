<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Partner;
use App\Models\Tenant\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * Catálogo General de Vehículos (Inspirado directamente en productos/index de sys-POS).
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $tipo = $request->input('tipo');
        $estado = $request->input('estado');
        $alerta = $request->boolean('alerta');

        $query = Vehicle::with(['partner', 'defaultDriver'])->orderBy('plate');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('plate', 'like', "%{$term}%")
                    ->orWhere('internal_number', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('line', 'like', "%{$term}%");
            });
        }

        if ($tipo) {
            $query->where('vehicle_type', $tipo);
        }

        if ($estado) {
            $query->where('status', $estado);
        }

        $hoy = now()->toDateString();
        $limite30Dias = now()->addDays(30)->toDateString();

        if ($alerta) {
            $query->where(function ($q) use ($limite30Dias) {
                $q->where('soat_expiration', '<=', $limite30Dias)
                    ->orWhere('technomechanical_expiration', '<=', $limite30Dias)
                    ->orWhere('operation_card_expiration', '<=', $limite30Dias)
                    ->orWhere('contractual_policy_expiration', '<=', $limite30Dias)
                    ->orWhere('extra_contractual_policy_expiration', '<=', $limite30Dias);
            });
        }

        $totalVehiculos = Vehicle::count();
        $totalAlertas = Vehicle::where(function ($q) use ($limite30Dias) {
            $q->where('soat_expiration', '<=', $limite30Dias)
                ->orWhere('technomechanical_expiration', '<=', $limite30Dias)
                ->orWhere('operation_card_expiration', '<=', $limite30Dias);
        })->count();

        $vehiculos = $query->paginate(15)->withQueryString();
        $tiposDisponibles = Vehicle::select('vehicle_type')->distinct()->whereNotNull('vehicle_type')->pluck('vehicle_type');

        return view('vehiculos.index', compact(
            'vehiculos',
            'term',
            'tipo',
            'estado',
            'alerta',
            'totalVehiculos',
            'totalAlertas',
            'tiposDisponibles'
        ));
    }

    /**
     * Formulario de creación de vehículo.
     */
    public function create(): View
    {
        $aliados = Partner::orderBy('name')->get();
        $conductores = Employee::orderBy('name')->get();

        return view('vehiculos.create', compact('aliados', 'conductores'));
    }

    /**
     * Almacena un nuevo vehículo.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plate' => ['required', 'string', 'max:10', 'unique:vehicles,plate'],
            'internal_number' => ['nullable', 'string', 'max:20'],
            'brand' => ['required', 'string', 'max:100'],
            'line' => ['nullable', 'string', 'max:100'],
            'model_year' => ['required', 'integer', 'min:1990', 'max:'.(date('Y') + 2)],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'passenger_capacity' => ['required', 'integer', 'min:1'],
            'current_mileage' => ['nullable', 'numeric', 'min:0'],
            'soat_number' => ['nullable', 'string', 'max:50'],
            'soat_expiration' => ['nullable', 'date'],
            'technomechanical_number' => ['nullable', 'string', 'max:50'],
            'technomechanical_expiration' => ['nullable', 'date'],
            'contractual_policy_number' => ['nullable', 'string', 'max:50'],
            'contractual_policy_expiration' => ['nullable', 'date'],
            'extra_contractual_policy_number' => ['nullable', 'string', 'max:50'],
            'extra_contractual_policy_expiration' => ['nullable', 'date'],
            'operation_card_number' => ['nullable', 'string', 'max:50'],
            'operation_card_expiration' => ['nullable', 'date'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'default_driver_id' => ['nullable', 'exists:employees,id'],
            'status' => ['required', 'in:Activo,Mantenimiento,Inactivo'],
        ]);

        $vehiculo = Vehicle::create($validated);

        return redirect()->route('vehiculos.index')
            ->with('success', "Vehículo con placa {$vehiculo->plate} registrado exitosamente.");
    }

    /**
     * Ficha técnica y hoja de vida del vehículo.
     */
    public function show(Vehicle $vehiculo): View
    {
        $vehiculo->load(['partner', 'defaultDriver', 'maintenances', 'fuelRefills', 'serviceOrders']);

        return view('vehiculos.show', compact('vehiculo'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Vehicle $vehiculo): View
    {
        $aliados = Partner::orderBy('name')->get();
        $conductores = Employee::orderBy('name')->get();

        return view('vehiculos.edit', compact('vehiculo', 'aliados', 'conductores'));
    }

    /**
     * Actualizar vehículo.
     */
    public function update(Request $request, Vehicle $vehiculo): RedirectResponse
    {
        $validated = $request->validate([
            'plate' => ['required', 'string', 'max:10', 'unique:vehicles,plate,'.$vehiculo->id],
            'internal_number' => ['nullable', 'string', 'max:20'],
            'brand' => ['required', 'string', 'max:100'],
            'line' => ['nullable', 'string', 'max:100'],
            'model_year' => ['required', 'integer', 'min:1990', 'max:'.(date('Y') + 2)],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'passenger_capacity' => ['required', 'integer', 'min:1'],
            'current_mileage' => ['nullable', 'numeric', 'min:0'],
            'soat_number' => ['nullable', 'string', 'max:50'],
            'soat_expiration' => ['nullable', 'date'],
            'technomechanical_number' => ['nullable', 'string', 'max:50'],
            'technomechanical_expiration' => ['nullable', 'date'],
            'contractual_policy_number' => ['nullable', 'string', 'max:50'],
            'contractual_policy_expiration' => ['nullable', 'date'],
            'extra_contractual_policy_number' => ['nullable', 'string', 'max:50'],
            'extra_contractual_policy_expiration' => ['nullable', 'date'],
            'operation_card_number' => ['nullable', 'string', 'max:50'],
            'operation_card_expiration' => ['nullable', 'date'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'default_driver_id' => ['nullable', 'exists:employees,id'],
            'status' => ['required', 'in:Activo,Mantenimiento,Inactivo'],
        ]);

        $vehiculo->update($validated);

        return redirect()->route('vehiculos.index')
            ->with('success', "Datos del vehículo {$vehiculo->plate} actualizados correctamente.");
    }

    /**
     * Alternar estado activo / mantenimiento / inactivo.
     */
    public function toggleStatus(Vehicle $vehiculo): RedirectResponse
    {
        $nuevoEstado = $vehiculo->status === 'Activo' ? 'Inactivo' : 'Activo';
        $vehiculo->update(['status' => $nuevoEstado]);

        return back()->with('success', "Estado del vehículo {$vehiculo->plate} cambiado a {$nuevoEstado}.");
    }

    /**
     * Eliminar vehículo (Soft Delete).
     */
    public function destroy(Vehicle $vehiculo): RedirectResponse
    {
        $placa = $vehiculo->plate;
        $vehiculo->delete();

        return redirect()->route('vehiculos.index')
            ->with('success', "Vehículo {$placa} eliminado satisfactoriamente.");
    }
}
