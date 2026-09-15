@extends('layouts.app')

@section('title', 'Órdenes Completadas Pagadas')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('gerencial.reportes.dashboard') }}" class="hover:text-blue-600 transition">Panel Gerencial</a>
                <span>/</span>
                <span class="text-slate-800">Órdenes Pagadas</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center">
                    <span class="h-3 w-3 rounded-full bg-emerald-500 mr-2"></span>
                    Órdenes Completadas Pagadas
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">
                    {{ $totalOrdenes }} servicios liquidados
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Histórico y trazabilidad de servicios de transporte finalizados y pagados por clientes.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('gerencial.reportes.dashboard') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Volver al Dashboard
            </a>

            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Exportar a Excel / CSV
            </a>
        </div>
    </div>

    <!-- Tarjeta de Resumen Financiero -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Recaudación Total Acumulada</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">${{ number_format($totalRecaudado, 2) }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Suma de tarifas de órdenes liquidadas</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Servicios Pagados</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">{{ number_format($totalOrdenes) }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Órdenes con confirmación de pago</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ticket Promedio por Servicio</span>
            <div class="text-2xl sm:text-3xl font-black text-blue-600 mt-2">
                ${{ $totalOrdenes > 0 ? number_format($totalRecaudado / $totalOrdenes, 2) : '0.00' }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">Promedio de ingreso por viaje pagado</span>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
        <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Buscar Cliente</label>
                <input type="text" name="cliente_filtro" value="{{ request('cliente_filtro') }}" placeholder="Nombre o NIT..."
                    class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Año</label>
                <select name="anio_filtro" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    @php $actual = now()->year; @endphp
                    @for($i = $actual; $i >= $actual - 5; $i--)
                        <option value="{{ $i }}" {{ (request('anio_filtro', $anio) == $i) ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mes</label>
                <select name="mes_filtro" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <option value="">Todos los Meses</option>
                    @php
                        $mesesNombres = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
                    @endphp
                    @foreach($mesesNombres as $num => $nom)
                        <option value="{{ $num }}" {{ request('mes_filtro') == $num ? 'selected' : '' }}>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Filtrar Reporte
                </button>
                <a href="{{ route('gerencial.reportes.ordenes-pagadas') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Detallada -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4">Orden / FUEC</th>
                        <th class="px-5 py-4">Cliente Contratante</th>
                        <th class="px-5 py-4">Trayecto</th>
                        <th class="px-5 py-4">Fecha Servicio</th>
                        <th class="px-5 py-4">Vehículo & Conductor</th>
                        <th class="px-5 py-4">Factura</th>
                        <th class="px-5 py-4 text-right">Tarifa Pagada</th>
                        <th class="px-5 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ordenes as $o)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-blue-600">
                                <a href="{{ route('ordenes.show', $o->id) }}" class="hover:underline">
                                    {{ $o->order_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-800 block">{{ $o->client?->business_name ?? 'Particular' }}</span>
                                <span class="text-xs text-slate-400 font-mono">{{ $o->client?->document_number }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600">
                                <div><strong class="text-slate-700">Origen:</strong> {{ $o->origin }}</div>
                                <div><strong class="text-slate-700">Destino:</strong> {{ $o->destination }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600">
                                {{ $o->scheduled_start_time?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span class="font-bold text-slate-800 block font-mono">{{ $o->vehicle?->plate ?? 'Sin placa' }}</span>
                                <span class="text-slate-500">{{ $o->driver?->name ?? 'Sin conductor' }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                @if($o->invoice_number)
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 font-mono font-bold rounded-lg border border-blue-200">
                                        {{ $o->invoice_number }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Sin factura</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-extrabold text-emerald-600 text-sm">
                                ${{ number_format($o->fare, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('ordenes.show', $o->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">
                                No se encontraron órdenes pagadas con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ordenes->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
                {{ $ordenes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
