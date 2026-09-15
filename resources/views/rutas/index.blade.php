@extends('layouts.app')

@section('title', 'Catálogo de Rutas Frecuentes')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{ modalCrear: false, modalEditar: false, rutaEdit: {} }">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Operaciones & Logística</span>
                <span>/</span>
                <span class="text-slate-800">Rutas Frecuentes</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Catálogo Maestro de Rutas</h1>
            <p class="text-sm text-slate-500 mt-1">
                Trayectos predeterminados con kilometraje y tiempos estimados para agilizar el despacho y la cotización.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total Rutas:</span>
                <span class="font-bold text-slate-900">{{ $totalRutas }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-emerald-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $rutasActivas }} Activas
                </span>
            </div>

            <button @click="modalCrear = true"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Ruta
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('rutas.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <div class="md:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar por nombre de ruta, origen o destino (Enter)...">
            </div>

            <div class="md:col-span-4">
                <select name="tipo" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Tipos de Ruta</option>
                    @foreach(['Urbana', 'Rural', 'Intermunicipal', 'Escolar', 'Empresarial', 'Turismo'] as $t)
                        <option value="{{ $t }}" {{ ($tipo ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Tabla de Rutas -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nombre de Ruta</th>
                        <th class="px-4 py-3.5">Trayecto (Origen &rarr; Destino)</th>
                        <th class="px-4 py-3.5">Tipo</th>
                        <th class="px-4 py-3.5 text-center">Distancia Est.</th>
                        <th class="px-4 py-3.5 text-center">Tiempo Est.</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($rutas as $r)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 font-bold text-slate-900">
                            {{ $r->name }}
                            @if($r->description)
                                <div class="text-xs font-normal text-slate-400 mt-0.5 truncate max-w-xs">{{ $r->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                <span class="font-semibold text-slate-800">{{ $r->origin }}</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-slate-600">{{ $r->destination }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $r->route_type }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center font-mono font-bold text-slate-800">
                            {{ $r->estimated_distance_km ? number_format($r->estimated_distance_km, 1) . ' km' : 'N/D' }}
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 text-xs">
                            {{ $r->estimated_duration_minutes ? $r->estimated_duration_minutes . ' min' : 'N/D' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $r->status === 'Activa' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="rutaEdit = {{ json_encode($r) }}; modalEditar = true"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Editar Ruta">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form action="{{ route('rutas.destroy', $r->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta ruta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Eliminar Ruta">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay rutas registradas</h3>
                                <p class="text-xs text-slate-400 mt-1">Crea rutas frecuentes para autocompletar origen, destino y kilometraje en los despachos.</p>
                                <button @click="modalCrear = true" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                                    + Registrar Primera Ruta
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rutas->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $rutas->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Crear Ruta -->
    <div x-show="modalCrear" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4" @click.away="modalCrear = false">
            <h3 class="text-lg font-bold text-slate-900">Registrar Nueva Ruta Frecuente</h3>
            <form action="{{ route('rutas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Nombre Descriptivo de la Ruta:</label>
                    <input type="text" name="name" required placeholder="Ej: Ruta Escolar Norte - Chía"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Origen:</label>
                        <input type="text" name="origin" required placeholder="Ej: Calle 170 # 15-20, Bogotá"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Destino:</label>
                        <input type="text" name="destination" required placeholder="Ej: Parque Principal, Chía"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Tipo de Ruta:</label>
                        <select name="route_type" required class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach(['Urbana', 'Rural', 'Intermunicipal', 'Escolar', 'Empresarial', 'Turismo'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Distancia (km):</label>
                        <input type="number" step="0.1" name="estimated_distance_km" placeholder="25.5"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Duración (min):</label>
                        <input type="number" name="estimated_duration_minutes" placeholder="45"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Estado:</label>
                    <select name="status" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="Activa">Activa</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="modalCrear = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700">Guardar Ruta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Ruta -->
    <div x-show="modalEditar" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4" @click.away="modalEditar = false">
            <h3 class="text-lg font-bold text-slate-900">Editar Ruta Frecuente</h3>
            <form :action="'{{ url('/rutas') }}/' + rutaEdit.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Nombre Descriptivo de la Ruta:</label>
                    <input type="text" name="name" required x-model="rutaEdit.name"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Origen:</label>
                        <input type="text" name="origin" required x-model="rutaEdit.origin"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Destino:</label>
                        <input type="text" name="destination" required x-model="rutaEdit.destination"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Tipo de Ruta:</label>
                        <select name="route_type" required x-model="rutaEdit.route_type" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach(['Urbana', 'Rural', 'Intermunicipal', 'Escolar', 'Empresarial', 'Turismo'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Distancia (km):</label>
                        <input type="number" step="0.1" name="estimated_distance_km" x-model="rutaEdit.estimated_distance_km"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Duración (min):</label>
                        <input type="number" name="estimated_duration_minutes" x-model="rutaEdit.estimated_duration_minutes"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Estado:</label>
                    <select name="status" x-model="rutaEdit.status" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="Activa">Activa</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="modalEditar = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700">Actualizar Ruta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
