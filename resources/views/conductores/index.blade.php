@extends('layouts.app')

@section('title', 'Padrón de Conductores')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Personal & Seguridad</span>
                <span>/</span>
                <span class="text-slate-800">Tripulación & Conductores</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Conductores y Licencias</h1>
            <p class="text-sm text-slate-500 mt-1">
                Registro del personal de conducción, categorías de pase y semáforo de vigencia de licencias.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total:</span>
                <span class="font-bold text-slate-900">{{ $totalConductores }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-amber-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-amber-500 {{ $licenciasPorVencer > 0 ? 'animate-pulse' : '' }}"></span>
                    {{ $licenciasPorVencer }} Licencias en Alerta
                </span>
            </div>
            <a href="{{ route('conductores.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nuevo Conductor
            </a>
        </div>
    </div>

    <!-- Barra de Búsqueda -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('conductores.index') }}" class="flex items-center gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar conductor por nombre, cédula o número de licencia...">
            </div>

            @if(!empty($term))
            <a href="{{ route('conductores.index') }}"
                class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-2.5 rounded-xl transition flex items-center gap-1">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Tabla Conductores -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Conductor</th>
                        <th class="px-4 py-3.5">Identificación / Teléfono</th>
                        <th class="px-4 py-3.5">Licencia & Categoría</th>
                        <th class="px-4 py-3.5 text-center">Vencimiento Licencia</th>
                        <th class="px-4 py-3.5 text-center">Vehículo Habitual</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($conductores as $c)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-700 font-bold flex items-center justify-center text-xs border border-blue-200">
                                    {{ mb_strtoupper(mb_substr($c->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $c->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $c->email ?? 'Sin email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-mono text-slate-800 text-xs font-semibold">C.C. {{ $c->document_number }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $c->phone ?? 'Sin teléfono' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-mono text-slate-900 text-xs font-bold">N° {{ $c->driver_license_number ?? 'S/N' }}</div>
                            <div class="text-xs text-slate-400">Categoría: <strong class="text-slate-700">{{ $c->driver_license_category ?? 'C2' }}</strong></div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $licStatus = $c->license_status;
                            @endphp
                            @if($licStatus === 'Vencida')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span> {{ $c->driver_license_expiration?->format('d/m/Y') }} (Vencida)
                                </span>
                            @elseif($licStatus === 'Por Vencer')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span> {{ $c->driver_license_expiration?->format('d/m/Y') }}
                                </span>
                            @elseif($licStatus === 'Al Día')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $c->driver_license_expiration?->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">N/R</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($c->vehicles->count() > 0)
                                @foreach($c->vehicles as $v)
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xs font-bold">
                                    {{ $v->plate }}
                                </span>
                                @endforeach
                            @else
                                <span class="text-xs text-slate-400 italic">Rotativo</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $c->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                                {{ $c->status ?? 'Activo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('conductores.show', $c->id) }}"
                                    class="p-1.5 text-slate-500 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Ver Hoja de Vida">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('conductores.edit', $c->id) }}"
                                    class="p-1.5 text-slate-500 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition" title="Editar Conductor">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay conductores registrados</h3>
                                <p class="text-xs text-slate-400 mt-1">Registra personal operativo y conductores para asignar rutas y monitorear sus licencias.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conductores->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $conductores->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
