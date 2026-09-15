<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Client;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->toDateString());
        $clienteId = $request->input('client_id');
        $estadoFacturacion = $request->input('estado_facturacion');

        $ordenesQuery = ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->whereDate('scheduled_start_time', '>=', $fechaInicio)
            ->whereDate('scheduled_start_time', '<=', $fechaFin);

        if ($clienteId) {
            $ordenesQuery->where('client_id', $clienteId);
        }

        if ($estadoFacturacion === 'facturada') {
            $ordenesQuery->whereNotNull('invoice_number')->where('invoice_number', '!=', '');
        } elseif ($estadoFacturacion === 'pendiente') {
            $ordenesQuery->where(function ($q) {
                $q->whereNull('invoice_number')->orWhere('invoice_number', '');
            });
        }

        $ordenes = (clone $ordenesQuery)->orderByDesc('scheduled_start_time')->paginate(15)->withQueryString();

        $allPeriodOrders = (clone $ordenesQuery)->get();
        $totalOrdenes = $allPeriodOrders->count();
        $ordenesFacturadas = $allPeriodOrders->filter(fn ($o) => ! empty($o->invoice_number))->count();
        $ordenesPendientes = $totalOrdenes - $ordenesFacturadas;

        $totalKmRecorridos = $allPeriodOrders->sum(function ($o) {
            if ($o->end_mileage && $o->start_mileage && $o->end_mileage >= $o->start_mileage) {
                return $o->end_mileage - $o->start_mileage;
            }

            return 0;
        });

        $fuelQuery = FuelRefill::whereDate('refill_date', '>=', $fechaInicio)
            ->whereDate('refill_date', '<=', $fechaFin);

        $totalCostoCombustible = (clone $fuelQuery)->sum('total_cost') ?? 0;
        $totalGalonesCombustible = (clone $fuelQuery)->sum('gallons') ?? 0;
        $costoPorKm = $totalKmRecorridos > 0 ? ($totalCostoCombustible / $totalKmRecorridos) : 0;

        $resumenClientes = Client::whereHas('serviceOrders', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereDate('scheduled_start_time', '>=', $fechaInicio)
                ->whereDate('scheduled_start_time', '<=', $fechaFin);
        })
            ->withCount(['serviceOrders as total_viajes' => function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereDate('scheduled_start_time', '>=', $fechaInicio)
                    ->whereDate('scheduled_start_time', '<=', $fechaFin);
            }])
            ->orderByDesc('total_viajes')
            ->limit(10)
            ->get();

        $vehiculos = Vehicle::orderBy('plate')->get();
        $clientes = Client::orderBy('business_name')->get();

        return view('reportes.index', compact(
            'ordenes',
            'fechaInicio',
            'fechaFin',
            'clienteId',
            'estadoFacturacion',
            'totalOrdenes',
            'ordenesFacturadas',
            'ordenesPendientes',
            'totalKmRecorridos',
            'totalCostoCombustible',
            'totalGalonesCombustible',
            'costoPorKm',
            'resumenClientes',
            'clientes',
            'vehiculos'
        ));
    }

    public function liquidar(Request $request, ServiceOrder $ordene): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:100',
        ]);

        $ordene->update([
            'invoice_number' => $validated['invoice_number'],
        ]);

        return back()->with('success', "Orden #{$ordene->order_number} liquidada y vinculada a la factura/comprobante {$validated['invoice_number']}.");
    }
}
