<?php

namespace App\Filament\App\Pages;

use App\Models\Tenant\Vehicle;
use App\Services\Tenant\GpsTelemetryService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class OpsWallboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Monitoreo y GPS';

    protected static ?string $navigationLabel = 'Centro de Control (Wallboard)';

    protected static ?string $title = 'Centro de Monitoreo de Flota en Vivo';

    protected string $view = 'filament.app.pages.ops-wallboard';

    public function getHeading(): string|Htmlable
    {
        return 'Centro de Monitoreo de Flota y Telemetría GPS';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Supervisión en tiempo real de unidades en ruta, excesos de velocidad y estado operativo.';
    }

    public function getViewData(): array
    {
        $service = app(GpsTelemetryService::class);
        $positions = $service->getFleetLastPositions();

        $totalVehicles = Vehicle::where('status', 'Activo')->count();
        $onlineCount = $positions->count();
        $movingCount = $positions->where('speed', '>', 5)->count();
        $stoppedCount = $positions->where('speed', '<=', 5)->count();
        $speedingCount = $positions->where('speed', '>', 80)->count();

        return [
            'positions' => $positions,
            'totalVehicles' => $totalVehicles,
            'onlineCount' => $onlineCount,
            'movingCount' => $movingCount,
            'stoppedCount' => $stoppedCount,
            'speedingCount' => $speedingCount,
            'positionsJson' => $positions->map(function ($pos) {
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
            })->values()->toJson(),
        ];
    }
}
