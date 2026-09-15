<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\ServiceIncident;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Portal Mobile-First del Conductor (Inspirado en sys-POS y sisTransporte legacy)
     */
    public function conductor(): View
    {
        $ordenes = ServiceOrder::with(['vehicle', 'driver', 'client', 'fuecDocument'])
            ->orderByDesc('scheduled_start_time')
            ->limit(10)
            ->get();

        $vehiculos = Vehicle::where('status', 'Activo')->get();

        $ultimoChecklist = PreoperationalChecklist::with('vehicle')
            ->orderByDesc('created_at')
            ->first();

        $novedadesRecientes = ServiceIncident::with('serviceOrder')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('portal.conductor', compact('ordenes', 'vehiculos', 'ultimoChecklist', 'novedadesRecientes'));
    }

    /**
     * Iniciar un viaje desde el portal del conductor (captura de odómetro inicial)
     */
    public function iniciarViaje(Request $request, int $id): RedirectResponse
    {
        $orden = ServiceOrder::findOrFail($id);

        $request->validate([
            'initial_odometer' => 'required|numeric|min:0',
        ]);

        $orden->update([
            'status' => 'En Progreso',
            'start_mileage' => $request->input('initial_odometer'),
            'actual_start_time' => now(),
        ]);

        if ($orden->vehicle_id) {
            Vehicle::where('id', $orden->vehicle_id)->update([
                'current_mileage' => $request->input('initial_odometer'),
            ]);
        }

        return back()->with('success', "¡Viaje #{$orden->order_number} iniciado exitosamente! Odómetro: {$request->input('initial_odometer')} Km.");
    }

    /**
     * Finalizar un viaje desde el portal del conductor (captura de odómetro final)
     */
    public function finalizarViaje(Request $request, int $id): RedirectResponse
    {
        $orden = ServiceOrder::findOrFail($id);

        $request->validate([
            'final_odometer' => 'required|numeric|min:'.($orden->start_mileage ?? 0),
        ]);

        $orden->update([
            'status' => 'Finalizada',
            'end_mileage' => $request->input('final_odometer'),
            'actual_end_time' => now(),
        ]);

        if ($orden->vehicle_id) {
            Vehicle::where('id', $orden->vehicle_id)->update([
                'current_mileage' => $request->input('final_odometer'),
            ]);
        }

        return back()->with('success', "¡Viaje #{$orden->order_number} finalizado exitosamente! Odómetro final: {$request->input('final_odometer')} Km.");
    }

    /**
     * Diligenciamiento rápido del Checklist Preoperacional Diario
     */
    public function guardarChecklist(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'current_odometer' => 'required|numeric|min:0',
        ]);

        $items = [
            'frenos' => $request->boolean('frenos', true),
            'direccion' => $request->boolean('direccion', true),
            'luces_altas_bajas' => $request->boolean('luces', true),
            'llantas' => $request->boolean('llantas', true),
            'limpiabrisas' => $request->boolean('limpiabrisas', true),
            'espejos' => $request->boolean('espejos', true),
            'cinturones' => $request->boolean('cinturones', true),
            'extintor' => $request->boolean('extintor', true),
            'botiquin' => $request->boolean('botiquin', true),
            'equipo_carretera' => $request->boolean('equipo_carretera', true),
        ];

        // Determinar si es apto: todos los ítems de seguridad deben estar en buen estado
        $esApto = ! in_array(false, $items, true);
        $driver = Employee::first();

        PreoperationalChecklist::create([
            'vehicle_id' => $request->input('vehicle_id'),
            'driver_id' => $driver?->id ?? 1,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'mileage' => $request->input('current_odometer'),
            'is_approved' => $esApto,
            'fluid_levels' => ['frenos' => true, 'aceite' => true, 'refrigerante' => true],
            'lights_and_electrical' => ['luces' => $request->boolean('luces', true)],
            'tires_and_brakes' => ['llantas' => $request->boolean('llantas', true), 'frenos' => $request->boolean('frenos', true)],
            'safety_kit' => ['extintor' => $request->boolean('extintor', true), 'botiquin' => $request->boolean('botiquin', true)],
            'cabin_and_belts' => ['cinturones' => $request->boolean('cinturones', true)],
            'observations' => $request->input('notes', 'Inspección preoperacional móvil'),
        ]);

        return back()->with('success', '¡Checklist preoperacional registrado correctamente! Estado: '.($esApto ? 'Aprobado (Apto para operar)' : 'Rechazado (Requiere revisión técnica)'));
    }

    /**
     * Registro rápido de recarga de combustible
     */
    public function guardarCombustible(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $gallons = (float) ($request->input('gallons') ?? $request->input('liters') ?? 10.0);
        $cost = (float) ($request->input('total_cost') ?? $request->input('cost') ?? 0.0);
        $odometer = (float) ($request->input('odometer_mileage') ?? $request->input('odometer') ?? 0.0);
        $gasStation = $request->input('gas_station_name') ?? $request->input('gas_station') ?? 'Estación Terpel';

        FuelRefill::create([
            'vehicle_id' => $request->input('vehicle_id'),
            'driver_id' => null,
            'refill_date' => now()->toDateString(),
            'gallons' => $gallons,
            'total_cost' => $cost,
            'odometer_mileage' => $odometer,
            'gas_station_name' => $gasStation,
        ]);

        if ($odometer > 0) {
            Vehicle::where('id', $request->input('vehicle_id'))->update([
                'current_mileage' => $odometer,
            ]);
        }

        return back()->with('success', '¡Recarga de combustible registrada exitosamente!');
    }

    /**
     * Reporte rápido de novedad o contingencia vial
     */
    public function reportarNovedad(Request $request): RedirectResponse
    {
        $request->validate([
            'service_order_id' => 'required|exists:service_orders,id',
            'incident_type' => 'required|string',
            'description' => 'required|string|min:5',
        ]);

        ServiceIncident::create([
            'service_order_id' => $request->input('service_order_id'),
            'reported_by_id' => auth()->id() ?? 1,
            'incident_type' => $request->input('incident_type'),
            'severity' => $request->input('severity', 'medium'),
            'description' => $request->input('description'),
            'status' => 'open',
            'reported_at' => now(),
        ]);

        return back()->with('success', '¡Novedad reportada a la central de operaciones correctamente!');
    }

    /**
     * Portal de Clientes (Seguimiento ODS, FUEC y facturación)
     */
    public function cliente(): View
    {
        $ordenes = ServiceOrder::with(['vehicle', 'driver', 'client', 'fuecDocument'])
            ->orderByDesc('scheduled_start_time')
            ->paginate(15);

        return view('portal.cliente', compact('ordenes'));
    }

    /**
     * Portal de Aliados / Propietarios
     */
    public function aliado(): View
    {
        $vehiculosAliados = Vehicle::with(['maintenances', 'fuelRefills'])
            ->whereNotNull('partner_id')
            ->get();

        if ($vehiculosAliados->isEmpty()) {
            $vehiculosAliados = Vehicle::limit(10)->get();
        }

        $ordenesAliadas = ServiceOrder::with(['vehicle', 'driver', 'client'])
            ->whereIn('vehicle_id', $vehiculosAliados->pluck('id'))
            ->orderByDesc('scheduled_start_time')
            ->limit(20)
            ->get();

        return view('portal.aliado', compact('vehiculosAliados', 'ordenesAliadas'));
    }
}
