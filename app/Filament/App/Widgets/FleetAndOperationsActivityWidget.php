<?php

namespace App\Filament\App\Widgets;

use App\Models\Tenant\Contract;
use App\Models\Tenant\Maintenance;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Filament\Widgets\Widget;

class FleetAndOperationsActivityWidget extends Widget
{
    protected string $view = 'filament.app.widgets.fleet-and-operations-activity';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    public function getRecentOrders()
    {
        return ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->latest('id')
            ->limit(5)
            ->get();
    }

    public function getFleetSummary(): array
    {
        $totalVehiculos = Vehicle::count();
        $vehiculosActivos = Vehicle::where('status', 'Activo')->count();
        $vehiculosMantenimiento = Vehicle::where('status', 'Mantenimiento')->count();
        $contratosVigentes = Contract::where('status', 'Vigente')->count();
        $mantenimientosRecientes = Maintenance::latest('id')->limit(3)->get();

        return [
            'total' => $totalVehiculos,
            'activos' => $vehiculosActivos,
            'mantenimiento' => $vehiculosMantenimiento,
            'contratos' => $contratosVigentes,
            'mantenimientos' => $mantenimientosRecientes,
        ];
    }
}
