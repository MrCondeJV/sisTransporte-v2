<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\GpsTelemetryService;
use Illuminate\View\View;

class WallboardController extends Controller
{
    /**
     * Centro de Control y Monitoreo GPS de Flota en Vivo (Wallboard).
     */
    public function __invoke(GpsTelemetryService $service): View
    {
        $positions = $service->getFleetLastPositions();

        $totalVehicles = Vehicle::where('status', 'Activo')->count();
        $onlineCount = $positions->count();
        $movingCount = $positions->where('speed', '>', 5)->count();
        $stoppedCount = $positions->where('speed', '<=', 5)->count();
        $speedingCount = $positions->where('speed', '>', 80)->count();

        $positionsJson = $positions->map(function ($pos) {
            return [
                'id' => $pos->id,
                'vehicle_id' => $pos->vehicle_id,
                'plate' => $pos->vehicle?->plate ?? 'N/A',
                'brand' => $pos->vehicle?->brand ?? '',
                'driver' => $pos->driver?->name ?? 'Sin Conductor Asignado',
                'order' => $pos->serviceOrder?->order_number ?? 'Sin Orden Activa',
                'lat' => (float) $pos->latitude,
                'lng' => (float) $pos->longitude,
                'speed' => (float) $pos->speed,
                'heading' => (float) $pos->heading,
                'is_speeding' => (float) $pos->speed > 80.0,
                'time' => $pos->device_timestamp ? $pos->device_timestamp->format('H:i:s d/m') : '',
            ];
        })->values()->toJson();

        return view('monitoreo.index', compact(
            'positions',
            'totalVehicles',
            'onlineCount',
            'movingCount',
            'stoppedCount',
            'speedingCount',
            'positionsJson'
        ));
    }
}
