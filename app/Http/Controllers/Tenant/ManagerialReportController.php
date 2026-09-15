<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Maintenance;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagerialReportController extends Controller
{
    /**
     * Dashboard Gerencial Principal (Paridad V1 reportes_gerenciales/index.php + area_chart.php).
     */
    public function dashboard(Request $request): View
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $thisMonth = $now->month;
        $thisYear = $now->year;

        $lastYearSameDay = (clone $now)->subYear()->toDateString();
        $lastYear = $thisYear - 1;

        // Contadores Rápidos Superiores
        $contadorEmpleados = Employee::where('is_active', true)->count();
        $contadorVehiculos = Vehicle::where('status', 'Activo')->count();
        $contadorCompletadas = ServiceOrder::whereIn('status', ['Finalizada', 'Completado', 'Completada'])->count();
        $contadorPendientes = ServiceOrder::whereIn('status', ['Pendiente', 'Asignada'])->count();
        $contadorMantenimientos = Maintenance::count();
        $contadorClientes = Client::count();

        // Métricas de Ingresos por Servicio (Tarjetas Año Actual)
        $ordenesCompletadas = ServiceOrder::whereIn('status', ['Finalizada', 'Completado', 'Completada']);

        // Hoy
        $sumaDiaria = (clone $ordenesCompletadas)->whereDate('scheduled_start_time', $today)->sum('fare') ?? 0;
        $conteoDiario = (clone $ordenesCompletadas)->whereDate('scheduled_start_time', $today)->count();

        // Este Mes
        $sumaMensual = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $thisYear)
            ->whereMonth('scheduled_start_time', $thisMonth)
            ->sum('fare') ?? 0;
        $conteoMensual = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $thisYear)
            ->whereMonth('scheduled_start_time', $thisMonth)
            ->count();

        // Este Año
        $sumaAnual = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $thisYear)
            ->sum('fare') ?? 0;
        $conteoAnual = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $thisYear)
            ->count();

        // Métricas de Ingresos por Servicio (Tarjetas Año Anterior)
        $sumaDiariaAnterior = (clone $ordenesCompletadas)->whereDate('scheduled_start_time', $lastYearSameDay)->sum('fare') ?? 0;
        $conteoDiarioAnterior = (clone $ordenesCompletadas)->whereDate('scheduled_start_time', $lastYearSameDay)->count();

        $sumaMensualAnterior = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $lastYear)
            ->whereMonth('scheduled_start_time', $thisMonth)
            ->sum('fare') ?? 0;
        $conteoMensualAnterior = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $lastYear)
            ->whereMonth('scheduled_start_time', $thisMonth)
            ->count();

        $sumaAnualAnterior = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $lastYear)
            ->sum('fare') ?? 0;
        $conteoAnualAnterior = (clone $ordenesCompletadas)
            ->whereYear('scheduled_start_time', $lastYear)
            ->count();

        return view('gerencial.reportes.dashboard', compact(
            'contadorEmpleados',
            'contadorVehiculos',
            'contadorCompletadas',
            'contadorPendientes',
            'contadorMantenimientos',
            'contadorClientes',
            'sumaDiaria',
            'conteoDiario',
            'sumaMensual',
            'conteoMensual',
            'sumaAnual',
            'conteoAnual',
            'sumaDiariaAnterior',
            'conteoDiarioAnterior',
            'sumaMensualAnterior',
            'conteoMensualAnterior',
            'sumaAnualAnterior',
            'conteoAnualAnterior',
            'thisYear',
            'lastYear'
        ));
    }

    /**
     * Datos Semanales de Ingresos (barChartSemanal y barChartSemanalAnterior).
     */
    public function chartSemanal(): JsonResponse
    {
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $lunesActual = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $lunesAnterior = (clone $lunesActual)->subYear();

        $valoresActual = [];
        $detallesActual = [];
        $fechasActual = [];

        $valoresAnterior = [];
        $detallesAnterior = [];
        $fechasAnterior = [];

        for ($i = 0; $i < 7; $i++) {
            // Semana Actual
            $diaAct = (clone $lunesActual)->addDays($i);
            $fechaActStr = $diaAct->toDateString();
            $fechasActual[] = $diaAct->format('d/m');

            $resAct = ServiceOrder::whereIn('status', ['Finalizada', 'Completado', 'Completada'])
                ->whereDate('scheduled_start_time', $fechaActStr)
                ->selectRaw('COALESCE(SUM(fare), 0) as total, COUNT(id) as cantidad')
                ->first();

            $valoresActual[] = (float) ($resAct->total ?? 0);
            $detallesActual[] = [
                'fecha' => $diaAct->format('d/m/Y'),
                'total' => (float) ($resAct->total ?? 0),
                'cantidad' => (int) ($resAct->cantidad ?? 0),
            ];

            // Semana Año Anterior
            $diaAnt = (clone $lunesAnterior)->addDays($i);
            $fechaAntStr = $diaAnt->toDateString();
            $fechasAnterior[] = $diaAnt->format('d/m');

            $resAnt = ServiceOrder::whereIn('status', ['Finalizada', 'Completado', 'Completada'])
                ->whereDate('scheduled_start_time', $fechaAntStr)
                ->selectRaw('COALESCE(SUM(fare), 0) as total, COUNT(id) as cantidad')
                ->first();

            $valoresAnterior[] = (float) ($resAnt->total ?? 0);
            $detallesAnterior[] = [
                'fecha' => $diaAnt->format('d/m/Y'),
                'total' => (float) ($resAnt->total ?? 0),
                'cantidad' => (int) ($resAnt->cantidad ?? 0),
            ];
        }

        return response()->json([
            'success' => true,
            'dias' => $dias,
            'actual' => [
                'valores' => $valoresActual,
                'detalles' => $detallesActual,
                'fechas' => $fechasActual,
                'semana' => 'Semana '.$lunesActual->weekOfYear.' del '.$lunesActual->year,
                'rango' => 'Del '.$fechasActual[0].' al '.$fechasActual[6].'/'.$lunesActual->year,
            ],
            'anterior' => [
                'valores' => $valoresAnterior,
                'detalles' => $detallesAnterior,
                'fechas' => $fechasAnterior,
                'semana' => 'Semana '.$lunesAnterior->weekOfYear.' del '.$lunesAnterior->year,
                'rango' => 'Del '.$fechasAnterior[0].' al '.$fechasAnterior[6].'/'.$lunesAnterior->year,
            ],
        ]);
    }

    /**
     * Kilometraje vs Gasolina Semanal (chartCombustibleSemana).
     */
    public function chartCombustible(): JsonResponse
    {
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $lunes = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $kmValores = [];
        $combustibleValores = [];
        $fechas = [];

        for ($i = 0; $i < 7; $i++) {
            $dia = (clone $lunes)->addDays($i);
            $fechaStr = $dia->toDateString();
            $fechas[] = $dia->format('d/m');

            $kmDia = ServiceOrder::whereDate('scheduled_start_time', $fechaStr)
                ->selectRaw('COALESCE(SUM(CASE WHEN end_mileage >= start_mileage THEN end_mileage - start_mileage ELSE 0 END), 0) as km')
                ->value('km') ?? 0;

            $gasDia = FuelRefill::whereDate('refill_date', $fechaStr)
                ->sum('gallons') ?? 0;

            $kmValores[] = (float) $kmDia;
            $combustibleValores[] = (float) $gasDia;
        }

        return response()->json([
            'success' => true,
            'dias' => $dias,
            'km' => $kmValores,
            'combustible' => $combustibleValores,
            'rango' => 'Del '.$fechas[0].' al '.$fechas[6].'/'.$lunes->year,
        ]);
    }

    /**
     * Órdenes Pagadas vs No Pagadas por Mes (barChart1 y barChart2).
     */
    public function chartPagos(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $pagadas = array_fill(0, 12, 0.0);
        $noPagadas = array_fill(0, 12, 0.0);

        // Órdenes Pagadas
        $pagadasQuery = ServiceOrder::whereYear('scheduled_start_time', $year)
            ->whereIn('status', ['Finalizada', 'Completado', 'Completada'])
            ->where('is_paid', true)
            ->selectRaw('strftime("%m", scheduled_start_time) as mes, COALESCE(SUM(fare), 0) as total')
            ->groupBy('mes')
            ->get();

        foreach ($pagadasQuery as $row) {
            $idx = (int) $row->mes - 1;
            if ($idx >= 0 && $idx < 12) {
                $pagadas[$idx] = (float) $row->total;
            }
        }

        // Órdenes No Pagadas
        $noPagadasQuery = ServiceOrder::whereYear('scheduled_start_time', $year)
            ->whereIn('status', ['Finalizada', 'Completado', 'Completada'])
            ->where(function ($q) {
                $q->where('is_paid', false)->orWhereNull('is_paid');
            })
            ->selectRaw('strftime("%m", scheduled_start_time) as mes, COALESCE(SUM(fare), 0) as total')
            ->groupBy('mes')
            ->get();

        foreach ($noPagadasQuery as $row) {
            $idx = (int) $row->mes - 1;
            if ($idx >= 0 && $idx < 12) {
                $noPagadas[$idx] = (float) $row->total;
            }
        }

        return response()->json([
            'labels' => $meses,
            'pagadas' => $pagadas,
            'no_pagadas' => $noPagadas,
        ]);
    }

    /**
     * Facturas Emitidas vs Pendientes por Mes (facturasMesChart & noFacturadasChart).
     */
    public function chartFacturas(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $facturadas = array_fill(0, 12, 0);
        $noFacturadas = array_fill(0, 12, 0);

        // Facturadas
        $factQuery = ServiceOrder::whereYear('scheduled_start_time', $year)
            ->whereNotNull('invoice_number')
            ->where('invoice_number', '!=', '')
            ->selectRaw('strftime("%m", scheduled_start_time) as mes, COUNT(id) as total')
            ->groupBy('mes')
            ->get();

        foreach ($factQuery as $row) {
            $idx = (int) $row->mes - 1;
            if ($idx >= 0 && $idx < 12) {
                $facturadas[$idx] = (int) $row->total;
            }
        }

        // No Facturadas (completadas sin factura)
        $noFactQuery = ServiceOrder::whereYear('scheduled_start_time', $year)
            ->whereIn('status', ['Finalizada', 'Completado', 'Completada'])
            ->where(function ($q) {
                $q->whereNull('invoice_number')->orWhere('invoice_number', '');
            })
            ->selectRaw('strftime("%m", scheduled_start_time) as mes, COUNT(id) as total')
            ->groupBy('mes')
            ->get();

        foreach ($noFactQuery as $row) {
            $idx = (int) $row->mes - 1;
            if ($idx >= 0 && $idx < 12) {
                $noFacturadas[$idx] = (int) $row->total;
            }
        }

        return response()->json([
            'labels' => $meses,
            'facturadas' => $facturadas,
            'no_facturadas' => $noFacturadas,
        ]);
    }

    /**
     * Órdenes por Estado en el Año (lineChart2).
     */
    public function chartEstados(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $pendientes = array_fill(0, 12, 0);
        $enProgreso = array_fill(0, 12, 0);
        $completadas = array_fill(0, 12, 0);
        $canceladas = array_fill(0, 12, 0);

        $query = ServiceOrder::whereYear('scheduled_start_time', $year)
            ->selectRaw('strftime("%m", scheduled_start_time) as mes, status, COUNT(id) as count')
            ->groupBy('mes', 'status')
            ->get();

        foreach ($query as $row) {
            $idx = (int) $row->mes - 1;
            if ($idx < 0 || $idx >= 12) {
                continue;
            }

            match ($row->status) {
                'Pendiente' => $pendientes[$idx] += (int) $row->count,
                'Asignada', 'En Progreso' => $enProgreso[$idx] += (int) $row->count,
                'Finalizada', 'Completado', 'Completada' => $completadas[$idx] += (int) $row->count,
                'Cancelada' => $canceladas[$idx] += (int) $row->count,
                default => null,
            };
        }

        return response()->json([
            'labels' => $meses,
            'pendiente' => $pendientes,
            'en_progreso' => $enProgreso,
            'completado' => $completadas,
            'cancelado' => $canceladas,
        ]);
    }

    /**
     * Rutas Más Usadas (donutChart).
     */
    public function chartRutas(): JsonResponse
    {
        $rutas = ServiceOrder::selectRaw('COALESCE(route_name, origin || " - " || destination) as ruta, COUNT(id) as cantidad')
            ->whereNotNull('origin')
            ->groupBy('ruta')
            ->orderByDesc('cantidad')
            ->limit(6)
            ->get();

        $labels = $rutas->pluck('ruta')->toArray();
        $data = $rutas->pluck('cantidad')->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Eventos para FullCalendar.
     */
    public function eventosCalendario(Request $request): JsonResponse
    {
        $start = $request->input('start', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->input('end', Carbon::now()->endOfMonth()->toDateString());

        $ordenes = ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->whereDate('scheduled_start_time', '>=', $start)
            ->whereDate('scheduled_start_time', '<=', $end)
            ->limit(200)
            ->get();

        $eventos = $ordenes->map(function ($o) {
            $cliente = $o->client?->business_name ?? 'Particular';
            $placa = $o->vehicle?->plate ?? 'Sin vehículo';
            $conductor = $o->driver?->name ?? 'Sin conductor';

            $color = match ($o->status) {
                'Finalizada', 'Completado', 'Completada' => '#10b981', // green
                'En Progreso' => '#0ea5e9', // blue
                'Cancelada' => '#ef4444', // red
                default => '#f59e0b', // amber
            };

            return [
                'id' => $o->id,
                'title' => "ODS #{$o->id} - {$cliente}",
                'start' => $o->scheduled_start_time?->toIso8601String(),
                'end' => $o->scheduled_end_time?->toIso8601String(),
                'color' => $color,
                'description' => "Cliente: {$cliente}\nPlaca: {$placa}\nConductor: {$conductor}\nRuta: {$o->origin} -> {$o->destination}\nTarifa: $".number_format($o->fare, 0),
            ];
        });

        return response()->json($eventos);
    }

    /**
     * Subreporte: Órdenes Completadas Pagadas (Paridad ordenes_pagadas.php).
     */
    public function ordenesPagadas(Request $request): View|StreamedResponse
    {
        $anio = $request->input('anio_filtro', Carbon::now()->year);
        $mes = $request->input('mes_filtro');
        $clienteFiltro = $request->input('cliente_filtro');

        $query = ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->whereIn('status', ['Finalizada', 'Completado', 'Completada'])
            ->where('is_paid', true);

        if ($anio) {
            $query->whereYear('scheduled_start_time', $anio);
        }
        if ($mes) {
            $query->whereMonth('scheduled_start_time', $mes);
        }
        if ($clienteFiltro) {
            $query->whereHas('client', function ($q) use ($clienteFiltro) {
                $q->where('business_name', 'like', "%{$clienteFiltro}%")
                    ->orWhere('document_number', 'like', "%{$clienteFiltro}%");
            });
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get(), 'ordenes_pagadas_'.$anio.'.csv');
        }

        $totalRecaudado = (clone $query)->sum('fare') ?? 0;
        $totalOrdenes = (clone $query)->count();
        $ordenes = $query->orderByDesc('scheduled_start_time')->paginate(20)->withQueryString();
        $clientes = Client::orderBy('business_name')->get();

        return view('gerencial.reportes.ordenes_pagadas', compact(
            'ordenes',
            'totalRecaudado',
            'totalOrdenes',
            'anio',
            'mes',
            'clienteFiltro',
            'clientes'
        ));
    }

    /**
     * Subreporte: Órdenes Completadas Sin Pagar (Paridad ordenes_sin_pagar.php).
     */
    public function ordenesSinPagar(Request $request): View|StreamedResponse
    {
        $anio = $request->input('anio_filtro', Carbon::now()->year);
        $mes = $request->input('mes_filtro');
        $clienteFiltro = $request->input('cliente_filtro');

        $query = ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->whereIn('status', ['Finalizada', 'Completado', 'Completada'])
            ->where(function ($q) {
                $q->where('is_paid', false)->orWhereNull('is_paid');
            });

        if ($anio) {
            $query->whereYear('scheduled_start_time', $anio);
        }
        if ($mes) {
            $query->whereMonth('scheduled_start_time', $mes);
        }
        if ($clienteFiltro) {
            $query->whereHas('client', function ($q) use ($clienteFiltro) {
                $q->where('business_name', 'like', "%{$clienteFiltro}%")
                    ->orWhere('document_number', 'like', "%{$clienteFiltro}%");
            });
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get(), 'ordenes_sin_pagar_'.$anio.'.csv');
        }

        $totalPendiente = (clone $query)->sum('fare') ?? 0;
        $totalOrdenes = (clone $query)->count();
        $ordenes = $query->orderByDesc('scheduled_start_time')->paginate(20)->withQueryString();
        $clientes = Client::orderBy('business_name')->get();

        return view('gerencial.reportes.ordenes_sin_pagar', compact(
            'ordenes',
            'totalPendiente',
            'totalOrdenes',
            'anio',
            'mes',
            'clienteFiltro',
            'clientes'
        ));
    }

    /**
     * Subreporte: Órdenes Facturadas (Paridad ordenes_facturadas.php).
     */
    public function ordenesFacturadas(Request $request): View|StreamedResponse
    {
        $anio = $request->input('anio_filtro', Carbon::now()->year);
        $mes = $request->input('mes_filtro');
        $clienteFiltro = $request->input('cliente_filtro');

        $query = ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->whereNotNull('invoice_number')
            ->where('invoice_number', '!=', '');

        if ($anio) {
            $query->whereYear('scheduled_start_time', $anio);
        }
        if ($mes) {
            $query->whereMonth('scheduled_start_time', $mes);
        }
        if ($clienteFiltro) {
            $query->whereHas('client', function ($q) use ($clienteFiltro) {
                $q->where('business_name', 'like', "%{$clienteFiltro}%")
                    ->orWhere('document_number', 'like', "%{$clienteFiltro}%");
            });
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get(), 'ordenes_facturadas_'.$anio.'.csv');
        }

        $totalFacturado = (clone $query)->sum('fare') ?? 0;
        $totalOrdenes = (clone $query)->count();
        $ordenes = $query->orderByDesc('scheduled_start_time')->paginate(20)->withQueryString();

        return view('gerencial.reportes.ordenes_facturadas', compact(
            'ordenes',
            'totalFacturado',
            'totalOrdenes',
            'anio',
            'mes',
            'clienteFiltro'
        ));
    }

    /**
     * Subreporte: Órdenes No Facturadas (Paridad ordenes_no_facturadas.php).
     */
    public function ordenesNoFacturadas(Request $request): View|StreamedResponse
    {
        $anio = $request->input('anio_filtro', Carbon::now()->year);
        $mes = $request->input('mes_filtro');
        $clienteFiltro = $request->input('cliente_filtro');

        $query = ServiceOrder::with(['client', 'vehicle', 'driver'])
            ->whereIn('status', ['Finalizada', 'Completado', 'Completada'])
            ->where(function ($q) {
                $q->whereNull('invoice_number')->orWhere('invoice_number', '');
            });

        if ($anio) {
            $query->whereYear('scheduled_start_time', $anio);
        }
        if ($mes) {
            $query->whereMonth('scheduled_start_time', $mes);
        }
        if ($clienteFiltro) {
            $query->whereHas('client', function ($q) use ($clienteFiltro) {
                $q->where('business_name', 'like', "%{$clienteFiltro}%")
                    ->orWhere('document_number', 'like', "%{$clienteFiltro}%");
            });
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get(), 'ordenes_no_facturadas_'.$anio.'.csv');
        }

        $totalPendienteFacturar = (clone $query)->sum('fare') ?? 0;
        $totalOrdenes = (clone $query)->count();
        $ordenes = $query->orderByDesc('scheduled_start_time')->paginate(20)->withQueryString();

        return view('gerencial.reportes.ordenes_no_facturadas', compact(
            'ordenes',
            'totalPendienteFacturar',
            'totalOrdenes',
            'anio',
            'mes',
            'clienteFiltro'
        ));
    }

    /**
     * Exportar colección a archivo CSV con cabeceras correctas.
     */
    protected function exportCsv($orders, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            // BOM para Excel UTF-8
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'ID', 'No. Orden', 'Cliente', 'NIT/Doc', 'Origen', 'Destino',
                'Fecha Servicio', 'Placa Vehiculo', 'Conductor', 'Tarifa ($)',
                'Factura', 'Estado Pago', 'Estado Servicio',
            ], ';');

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->order_number,
                    $order->client?->business_name ?? 'Particular',
                    $order->client?->document_number ?? 'N/A',
                    $order->origin,
                    $order->destination,
                    $order->scheduled_start_time?->format('Y-m-d H:i') ?? '',
                    $order->vehicle?->plate ?? 'N/A',
                    $order->driver?->name ?? 'N/A',
                    $order->fare ?? 0,
                    $order->invoice_number ?? 'Sin factura',
                    $order->is_paid ? 'Pagado' : 'Pendiente',
                    $order->status,
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
