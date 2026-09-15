@extends('layouts.app')

@section('title', 'Reportes Gerenciales y Liquidación de Servicios')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{
    liquidarModalOpen: false,
    selectedOrderId: null,
    selectedOrderNumber: '',
    actionUrl: '',
    openLiquidar(orderId, orderNumber, url) {
        this.selectedOrderId = orderId;
        this.selectedOrderNumber = orderNumber;
        this.actionUrl = url;
        this.liquidarModalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Auditoría & Control Gerencial</span>
                <span>/</span>
                <span class="text-slate-800">Liquidación y Reportes Financieros</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reportes Gerenciales & Liquidación</h1>
            <p class="text-sm text-slate-500 mt-1">
                Consolidado financiero y operativo de órdenes de servicio, estado de facturación de clientes y balance de combustible.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button onclick="window.print()"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Imprimir Resumen
            </button>
            <a href="{{ route('gerencial.aprobaciones.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                Aprobaciones Gerenciales
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Fecha Desde:</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Fecha Hasta:</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Cliente:</label>
                <select name="client_id"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los Clientes</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ ($clienteId == $c->id) ? 'selected' : '' }}>
                            {{ $c->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Facturación:</label>
                <select name="estado_facturacion"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <option value="facturada" {{ ($estadoFacturacion === 'facturada') ? 'selected' : '' }}>Facturadas / Saldadas</option>
                    <option value="pendiente" {{ ($estadoFacturacion === 'pendiente') ? 'selected' : '' }}>Pendientes de Facturar</option>
                </select>
            </div>

            <div class="md:col-span-1 flex items-center justify-end">
                <button type="submit"
                    class="w-full py-2.5 px-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Card 1: Total Viajes -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Servicios</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalOrdenes) }}</p>
            <p class="text-xs text-slate-500 mt-1">Órdenes en el período</p>
        </div>

        <!-- Card 2: Facturadas -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Facturadas / Saldadas</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($ordenesFacturadas) }}</p>
            <p class="text-xs text-slate-500 mt-1">Con soporte contable</p>
        </div>

        <!-- Card 3: Pendientes -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pendientes de Cobro</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($ordenesPendientes) }}</p>
            <p class="text-xs text-slate-500 mt-1">Sin factura asignada</p>
        </div>

        <!-- Card 4: Kilometraje -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Kilómetros Totales</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($totalKmRecorridos, 0) }}</p>
            <p class="text-xs text-slate-500 mt-1">Km recorridos auditados</p>
        </div>

        <!-- Card 5: Combustible -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-purple-600 uppercase tracking-wider">Combustible Total</p>
            <p class="text-2xl font-extrabold text-purple-600 mt-1">${{ number_format($totalCostoCombustible, 0) }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ number_format($totalGalonesCombustible, 1) }} Gal &bull; ${{ number_format($costoPorKm, 0) }}/km</p>
        </div>
    </div>

    <!-- Tabla Principal de Liquidación -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900">Detalle de Servicios para Liquidación</h3>
                <p class="text-xs text-slate-500 mt-0.5">Listado pormenorizado para cierre contable con número de factura y recorrido.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $ordenes->total() }} registros</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">ODS # & Fecha</th>
                        <th class="px-4 py-3.5">Cliente Contratante</th>
                        <th class="px-4 py-3.5">Trayecto / Ruta</th>
                        <th class="px-4 py-3.5">Vehículo & Conductor</th>
                        <th class="px-4 py-3.5 text-center">Recorrido</th>
                        <th class="px-4 py-3.5 text-center">Estado Operacional</th>
                        <th class="px-4 py-3.5 text-center">Factura / Liquidación</th>
                        <th class="px-5 py-3.5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($ordenes as $ord)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <a href="{{ route('ordenes.show', $ord->id) }}" class="font-bold text-blue-600 hover:underline">
                                #{{ $ord->order_number }}
                            </a>
                            <div class="text-xs text-slate-400">{{ $ord->scheduled_start_time?->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-slate-800 text-xs">{{ $ord->client?->business_name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">NIT: {{ $ord->client?->document_number }}</div>
                        </td>
                        <td class="px-4 py-4 max-w-xs">
                            <div class="text-xs text-slate-800 font-medium truncate">{{ $ord->origin }} &rarr; {{ $ord->destination }}</div>
                            <div class="text-[11px] text-slate-400">{{ $ord->passengers_count }} pasajeros</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xs font-bold">
                                {{ $ord->vehicle?->plate ?? 'S/A' }}
                            </span>
                            <div class="text-xs text-slate-600 mt-1">{{ $ord->driver?->first_name }} {{ $ord->driver?->last_name }}</div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap font-mono text-xs">
                            @if($ord->end_mileage && $ord->start_mileage)
                                <span class="font-bold text-emerald-700">
                                    {{ number_format($ord->end_mileage - $ord->start_mileage, 0) }} km
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $statusBadges = [
                                    'Pendiente' => 'bg-slate-100 text-slate-700',
                                    'Asignada' => 'bg-blue-50 text-blue-700',
                                    'En Progreso' => 'bg-amber-50 text-amber-700',
                                    'Finalizada' => 'bg-emerald-50 text-emerald-700',
                                    'Cancelada' => 'bg-rose-50 text-rose-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusBadges[$ord->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $ord->status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($ord->invoice_number)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    FAC: {{ $ord->invoice_number }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Sin Liquidar
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    @click="openLiquidar({{ $ord->id }}, '{{ $ord->order_number }}', '{{ route('reportes.liquidar', $ord->id) }}')"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 transition" title="Registrar Factura">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    {{ $ord->invoice_number ? 'Modificar FAC' : 'Liquidar' }}
                                </button>
                                <a href="{{ route('ordenes.show', $ord->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Ver ODS">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay órdenes en este rango de fechas</h3>
                                <p class="text-xs text-slate-400 mt-1">Ajusta los filtros de fecha o cliente para consultar el consolidado de liquidaciones.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ordenes->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $ordenes->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Liquidar / Asignar Factura -->
    <div x-show="liquidarModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="liquidarModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-extrabold text-slate-900">Asignar Factura / Liquidación</h3>
                    <button @click="liquidarModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form :action="actionUrl" method="POST" class="space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600">
                        Vincular número de factura electrónica o recibo de cobro a la orden <strong class="text-blue-600 font-mono" x-text="'#' + selectedOrderNumber"></strong>.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Número de Factura / Liquidación:</label>
                        <input type="text" name="invoice_number" required placeholder="FAC-2026-0045"
                            class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="liquidarModalOpen = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                            Guardar Liquidación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
