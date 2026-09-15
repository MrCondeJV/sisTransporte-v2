@extends('layouts.app')

@section('title', 'Órdenes de Servicio')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Operaciones & Despachos</span>
                <span>/</span>
                <span class="text-slate-800">Órdenes de Servicio</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Órdenes de Servicio (ODS)</h1>
            <p class="text-sm text-slate-500 mt-1">Control de viajes, programación de servicios especiales y emisión reglamentaria de FUEC.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total:</span>
                <span class="font-bold text-slate-900">{{ $totalOrdenes }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-amber-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-amber-500 {{ $ordenesEnRuta > 0 ? 'animate-pulse' : '' }}"></span>
                    {{ $ordenesEnRuta }} En Ruta
                </span>
            </div>

            <a href="{{ route('ordenes.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Orden
            </a>
        </div>
    </div>

    <!-- Barra de Filtros (sys-POS style) -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('ordenes.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar por # Orden, cliente, origen, destino o placa (Enter)...">
            </div>

            <div class="md:col-span-3">
                <select name="estado" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" {{ ($estado ?? '') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Asignada" {{ ($estado ?? '') == 'Asignada' ? 'selected' : '' }}>Asignada</option>
                    <option value="En Progreso" {{ ($estado ?? '') == 'En Progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="Finalizada" {{ ($estado ?? '') == 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                    <option value="Cancelada" {{ ($estado ?? '') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <input type="date" name="fecha" value="{{ $fecha ?? '' }}" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <div class="md:col-span-1 flex justify-end">
                @if(!empty($term) || !empty($estado) || !empty($fecha))
                <a href="{{ route('ordenes.index') }}"
                    class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 p-2.5 rounded-xl transition flex items-center justify-center"
                    title="Limpiar filtros">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Órdenes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Orden #</th>
                        <th class="px-4 py-3.5">Cliente & Contacto</th>
                        <th class="px-4 py-3.5">Ruta & Pasajeros</th>
                        <th class="px-4 py-3.5">Vehículo & Conductor</th>
                        <th class="px-4 py-3.5 text-center">Fecha y Hora</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($ordenes as $o)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 font-bold text-slate-900 whitespace-nowrap">
                            <a href="{{ route('ordenes.show', $o->id) }}" class="text-blue-600 hover:underline">
                                #{{ $o->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-800">{{ $o->client?->business_name ?? 'Particular' }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $o->client?->phone ?? 'Sin teléfono' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-slate-800">{{ $o->origin }} &rarr; {{ $o->destination }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $o->passengers_count }} Pasajeros</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-xs font-bold border border-slate-200">
                                    {{ $o->vehicle?->plate ?? 'Sin asignar' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">{{ $o->driver?->first_name }} {{ $o->driver?->last_name }}</div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $o->scheduled_start_time?->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-400">{{ $o->scheduled_start_time?->format('H:i A') }}</div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $statusClasses = [
                                    'En Progreso' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Programada' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Finalizada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Cancelada' => 'bg-rose-50 text-rose-700 border-rose-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClasses[$o->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                {{ $o->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                @if($o->fuecDocument)
                                    <a href="{{ route('tenant.fuec.pdf', $o->fuecDocument->id) }}" target="_blank"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 hover:bg-emerald-100 transition" title="Descargar FUEC PDF Oficial">
                                        FUEC PDF
                                    </a>
                                @else
                                    <form action="{{ route('ordenes.emitir-fuec', $o->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200 hover:bg-blue-100 transition" title="Emitir FUEC Oficial">
                                            Emitir FUEC
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('ordenes.show', $o->id) }}"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition" title="Ver Detalle">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('ordenes.edit', $o->id) }}"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Editar Orden">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('ordenes.duplicar', $o->id) }}" method="POST" onsubmit="return confirm('¿Desea duplicar la orden #{{ $o->order_number }}?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Duplicar Orden">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay órdenes registradas</h3>
                                <p class="text-xs text-slate-400 mt-1">Crea una nueva orden de despacho para coordinar el viaje, asignar vehículo y emitir el FUEC.</p>
                                <a href="{{ route('ordenes.create') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                                    + Nueva Orden de Servicio
                                </a>
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
</div>
@endsection
