@extends('layouts.transport')

@section('title', 'Portal de Clientes')

@section('content')
<div class="space-y-6">
    <!-- Header del Portal Cliente -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl mr-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </span>
                Portal de Seguimiento de Clientes
            </h1>
            <p class="text-sm text-slate-500 mt-1">Consulta tus servicios de transporte contratados, monitoreo en vivo y descarga del FUEC oficial.</p>
        </div>
    </div>

    <!-- Lista de Servicios del Cliente -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Historial y Programación de Viajes</h2>
            <span class="text-xs text-slate-400">Total: {{ $ordenes->total() }} servicios</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Orden #</th>
                        <th class="px-5 py-3">Ruta</th>
                        <th class="px-5 py-3">Fecha & Hora</th>
                        <th class="px-5 py-3">Vehículo / Conductor</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">FUEC Digital</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ordenes as $ord)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-mono font-bold text-slate-900">
                            #{{ $ord->order_number }}
                        </td>
                        <td class="px-5 py-4 font-medium text-slate-800">
                            {{ $ord->origin }} <span class="text-blue-600 font-bold">➔</span> {{ $ord->destination }}
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500">
                            {{ $ord->service_date }} {{ $ord->service_time }}
                        </td>
                        <td class="px-5 py-4 text-xs">
                            <span class="font-bold text-slate-800">{{ $ord->vehicle?->plate ?? 'Asignando' }}</span>
                            <span class="block text-slate-400">{{ $ord->driver?->name ?? 'Conductor por confirmar' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if($ord->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Programado</span>
                            @elseif($ord->status === 'in_progress')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center w-max">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-ping mr-1"></span> En Ruta
                                </span>
                            @elseif($ord->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Cumplido</span>
                            @elseif($ord->status === 'cancelled')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Cancelado</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($ord->fuecDocument)
                                <a href="{{ route('tenant.fuec.pdf', $ord->fuecDocument->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200 transition">
                                    <svg class="h-4 w-4 mr-1 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Descargar FUEC
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">En trámite</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                            No se registran órdenes de servicio a su nombre.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $ordenes->links() }}
        </div>
    </div>
</div>
@endsection
