@extends('layouts.app')

@section('title', 'Contrato #' . $contrato->contract_number)

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Comercial</span>
                <span>/</span>
                <a href="{{ route('contratos.index') }}" class="hover:text-blue-600 transition">Contratos de Transporte</a>
                <span>/</span>
                <span class="text-slate-800">#{{ $contrato->contract_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Contrato #{{ $contrato->contract_number }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $contrato->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                    {{ $contrato->status ?? 'Activo' }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $contrato->contract_type }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Cliente Contratante: <span class="font-bold text-slate-800">{{ $contrato->client?->display_name }}</span> (NIT/CC: {{ $contrato->client?->document_number }})</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('contratos.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Volver
            </a>
            <a href="{{ route('contratos.edit', $contrato->id) }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Editar Contrato
            </a>
        </div>
    </div>

    <!-- Detalle Legal y Métricas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ficha de Condiciones -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Cláusulas & Parámetros
            </h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Cliente</span>
                    <a href="{{ route('clientes.show', $contrato->client_id) }}" class="font-bold text-blue-600 hover:underline">
                        {{ $contrato->client?->display_name }} &rarr;
                    </a>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Modalidad Contractual</span>
                    <span class="font-semibold text-slate-800">{{ $contrato->contract_type }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Objeto del Contrato</span>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 leading-relaxed mt-1">
                        {{ $contrato->contract_object }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Vigencia Inicio</span>
                        <span class="font-semibold text-slate-800">{{ $contrato->start_date?->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Vigencia Fin</span>
                        <span class="font-semibold text-slate-800">{{ $contrato->end_date?->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Valor Total o Estimado</span>
                    <span class="text-lg font-black text-slate-900 font-mono">${{ number_format($contrato->value, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Órdenes Asociadas a este Contrato -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Despachos y Órdenes Amparadas ({{ $contrato->serviceOrders->count() }})
                    </h3>
                    <a href="{{ route('ordenes.create', ['contract_id' => $contrato->id]) }}"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        + Nueva Orden de Despacho
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Consecutivo</th>
                                <th class="px-4 py-3">Fecha Servicio</th>
                                <th class="px-4 py-3">Origen - Destino</th>
                                <th class="px-4 py-3">Vehículo</th>
                                <th class="px-4 py-3">Conductor</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-5 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($contrato->serviceOrders as $orden)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-blue-600">
                                    {{ $orden->order_number ?? 'OS-' . $orden->id }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $orden->service_date?->format('d/m/Y') }} {{ $orden->pickup_time }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-800">
                                    <span class="font-semibold">{{ $orden->origin }}</span> &rarr; {{ $orden->destination }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($orden->vehicle)
                                        <span class="font-bold text-slate-800">{{ $orden->vehicle->plate }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $orden->driver?->name ?? 'Sin asignar' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        @if($orden->status === 'Finalizada') bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif($orden->status === 'En Curso') bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($orden->status === 'Programada') bg-amber-50 text-amber-700 border border-amber-200
                                        @else bg-slate-100 text-slate-600 @endif">
                                        {{ $orden->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('ordenes.show', $orden->id) }}"
                                        class="p-1.5 text-slate-500 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Ver Detalle de Orden">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-xs text-slate-400">
                                    No hay órdenes de despacho asociadas a este contrato.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
