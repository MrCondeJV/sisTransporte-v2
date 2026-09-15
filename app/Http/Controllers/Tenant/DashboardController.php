<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FuecDocument;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el Dashboard Operativo con la estética y arquitectura de sys-POS.
     */
    public function __invoke(): View
    {
        // 1. KPIs de Flota
        $totalVehiculos = Vehicle::count();
        $vehiculosActivos = Vehicle::where('status', 'Activo')->count();
        $vehiculosEnMantenimiento = Vehicle::where('status', 'Mantenimiento')->count();

        // 2. KPIs de Operaciones y Órdenes
        $ordenesHoy = ServiceOrder::whereDate('scheduled_start_time', today())->count();
        $ordenesEnCurso = ServiceOrder::where('status', 'En Progreso')->count();
        $ordenesProgramadas = ServiceOrder::where('status', 'Programada')->count();

        // 3. FUECs Emitidos
        $totalFuecs = FuecDocument::count();
        $fuecsActivos = FuecDocument::where('status', 'Vigente')->count();

        // 4. Semáforo de Documentos por Vencer (< 30 días) o Vencidos
        $limite30Dias = now()->addDays(30)->toDateString();
        $hoy = now()->toDateString();

        $documentosPorVencer = Vehicle::where(function ($query) use ($hoy, $limite30Dias) {
            $query->whereBetween('soat_expiration', [$hoy, $limite30Dias])
                ->orWhereBetween('technomechanical_expiration', [$hoy, $limite30Dias])
                ->orWhereBetween('operation_card_expiration', [$hoy, $limite30Dias])
                ->orWhereBetween('contractual_policy_expiration', [$hoy, $limite30Dias])
                ->orWhereBetween('extra_contractual_policy_expiration', [$hoy, $limite30Dias]);
        })->count();

        $documentosVencidos = Vehicle::where(function ($query) use ($hoy) {
            $query->where('soat_expiration', '<', $hoy)
                ->orWhere('technomechanical_expiration', '<', $hoy)
                ->orWhere('operation_card_expiration', '<', $hoy)
                ->orWhere('contractual_policy_expiration', '<', $hoy)
                ->orWhere('extra_contractual_policy_expiration', '<', $hoy);
        })->count();

        // 5. Órdenes Recientes / En Curso para el Despacho
        $ordenesRecientes = ServiceOrder::with(['client', 'vehicle', 'driver', 'fuecDocument'])
            ->orderByDesc('scheduled_start_time')
            ->limit(8)
            ->get();

        // 6. Vehículos con alertas documentales prioritarias
        $vehiculosAlerta = Vehicle::where(function ($query) use ($limite30Dias) {
            $query->where('soat_expiration', '<=', $limite30Dias)
                ->orWhere('technomechanical_expiration', '<=', $limite30Dias)
                ->orWhere('operation_card_expiration', '<=', $limite30Dias);
        })
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalVehiculos',
            'vehiculosActivos',
            'vehiculosEnMantenimiento',
            'ordenesHoy',
            'ordenesEnCurso',
            'ordenesProgramadas',
            'totalFuecs',
            'fuecsActivos',
            'documentosPorVencer',
            'documentosVencidos',
            'ordenesRecientes',
            'vehiculosAlerta'
        ));
    }
}
