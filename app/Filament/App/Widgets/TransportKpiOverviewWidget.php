<?php

namespace App\Filament\App\Widgets;

use App\Models\Tenant\Employee;
use App\Models\Tenant\FuecDocument;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Filament\Widgets\Widget;

class TransportKpiOverviewWidget extends Widget
{
    protected string $view = 'filament.app.widgets.transport-kpi-overview';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -5;

    protected static bool $isLazy = false;

    public function getKpiData(): array
    {
        $today = now()->toDateString();

        $ordenesActivas = ServiceOrder::whereIn('status', ['Asignada', 'En Progreso'])->count();
        $ordenesHoy = ServiceOrder::whereDate('scheduled_date', $today)->count();

        $fuecsMes = FuecDocument::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $fuecsTotal = FuecDocument::count();

        $vehiculosActivos = Vehicle::where('status', 'Activo')->count();
        $vehiculosTaller = Vehicle::where('status', 'Mantenimiento')->count();

        // Alertas documentales (SOAT, Tecnomecánica, Tarjeta Operación próximas a vencer en 30 días o vencidas)
        $limite30Dias = now()->addDays(30)->toDateString();
        $alertasVencimiento = Vehicle::where(function ($query) use ($limite30Dias) {
            $query->where('soat_expires_at', '<=', $limite30Dias)
                ->orWhere('technomechanical_expires_at', '<=', $limite30Dias)
                ->orWhere('operation_card_expires_at', '<=', $limite30Dias);
        })->count();

        $totalConductores = Employee::where('position', 'Conductor')->where('status', 'Activo')->count();

        return [
            'ordenesActivas' => $ordenesActivas,
            'ordenesHoy' => $ordenesHoy,
            'fuecsMes' => $fuecsMes,
            'fuecsTotal' => $fuecsTotal,
            'vehiculosActivos' => $vehiculosActivos,
            'vehiculosTaller' => $vehiculosTaller,
            'alertasVencimiento' => $alertasVencimiento,
            'totalConductores' => $totalConductores,
        ];
    }
}
