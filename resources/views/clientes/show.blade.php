@extends('layouts.app')

@section('title', 'Ficha de Cliente - ' . $cliente->display_name)

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Comercial</span>
                <span>/</span>
                <a href="{{ route('clientes.index') }}" class="hover:text-blue-600 transition">Clientes Corporativos</a>
                <span>/</span>
                <span class="text-slate-800">{{ $cliente->display_name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $cliente->display_name }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $cliente->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                    {{ $cliente->status ?? 'Activo' }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $cliente->type }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Identificación: <span class="font-mono font-bold text-slate-700">{{ $cliente->document_number }}</span></p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Volver
            </a>
            <a href="{{ route('clientes.edit', $cliente->id) }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Editar Cliente
            </a>
        </div>
    </div>

    <!-- Resumen y Datos de Contacto -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tarjeta de Datos de Contacto -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                Información del Cliente
            </h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Tipo de Persona</span>
                    <span class="font-semibold text-slate-800">{{ $cliente->type }}</span>
                </div>
                @if($cliente->type === 'Empresa')
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Razón Social</span>
                    <span class="font-semibold text-slate-800">{{ $cliente->business_name ?? 'N/A' }}</span>
                </div>
                @else
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Nombre Completo</span>
                    <span class="font-semibold text-slate-800">{{ $cliente->first_name }} {{ $cliente->last_name }}</span>
                </div>
                @endif
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Identificación / NIT</span>
                    <span class="font-mono font-bold text-slate-800">{{ $cliente->document_number }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Teléfono / Celular</span>
                    <span class="font-medium text-slate-700">{{ $cliente->phone ?? 'No registrado' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Correo Electrónico</span>
                    <span class="font-medium text-slate-700">{{ $cliente->email ?? 'No registrado' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Dirección</span>
                    <span class="font-medium text-slate-700">{{ $cliente->address ?? 'No registrada' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Fecha de Registro</span>
                    <span class="text-xs text-slate-500">{{ $cliente->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Métricas Rápidas -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 content-start">
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Contratos Registrados</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ $cliente->contracts->count() }}</p>
                    <span class="text-xs text-slate-500 font-medium">Contratos suscritos</span>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Órdenes de Servicio</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">{{ $cliente->serviceOrders->count() }}</p>
                    <span class="text-xs text-slate-500 font-medium">Despachos asociados</span>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
            </div>

            <!-- Contratos Asociados Card List -->
            <div class="sm:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Contratos Vigentes y Registrados
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">N° Contrato</th>
                                <th class="px-4 py-3">Objeto / Tipo</th>
                                <th class="px-4 py-3">Vigencia</th>
                                <th class="px-4 py-3 text-right">Valor</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($cliente->contracts as $ctr)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-blue-600">
                                    {{ $ctr->contract_number }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-700 max-w-xs truncate">
                                    {{ $ctr->contract_object ?? 'Servicio de transporte especial' }}
                                    <div class="text-[10px] text-slate-400">{{ $ctr->contract_type ?? 'Empresarial' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $ctr->start_date?->format('d/m/Y') }} - {{ $ctr->end_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-xs text-right font-mono font-semibold text-slate-800">
                                    ${{ number_format($ctr->value, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ctr->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $ctr->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('contratos.show', $ctr->id) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                                        Ver &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-xs text-slate-400">
                                    No hay contratos registrados para este cliente.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Órdenes de Servicio Recientes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Órdenes de Servicio Recientes
            </h3>
            <a href="{{ route('ordenes.create', ['client_id' => $cliente->id]) }}"
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
                        <th class="px-4 py-3">Ruta / Origen - Destino</th>
                        <th class="px-4 py-3">Vehículo</th>
                        <th class="px-4 py-3">Conductor</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cliente->serviceOrders->take(10) as $orden)
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
                                <span class="text-slate-400 text-[11px] block">{{ $orden->vehicle->brand }}</span>
                            @else
                                <span class="text-slate-400 italic">No asignado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            {{ $orden->driver?->name ?? 'No asignado' }}
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
                        <td colspan="7" class="px-5 py-8 text-center text-xs text-slate-400">
                            No hay órdenes de servicio registradas para este cliente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
