@extends('layouts.app')

@section('title', 'Catálogo de Vehículos')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header Principal con KPIs y Acciones (Idéntico a sys-POS) -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Parque Automotor</span>
                <span>/</span>
                <span class="text-slate-800">Catálogo General de Flota</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Catálogo de Vehículos</h1>
            <p class="text-sm text-slate-500 mt-1">
                Parque automotor, número interno, estado operativo y vigencia de documentos oficiales de <span class="font-semibold text-slate-700">{{ tenancy()->initialized ? tenant('name') : 'Transportes Kemuel S.A.S.' }}</span>.
            </p>
        </div>

        <!-- Indicadores Rápidos y Botones de Acción -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total:</span>
                <span class="font-bold text-slate-900">{{ $totalVehiculos }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-rose-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-rose-500 {{ $totalAlertas > 0 ? 'animate-pulse' : '' }}"></span>
                    {{ $totalAlertas }} Alertas
                </span>
            </div>

            <a href="{{ route('vehiculos.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Vehículo
            </a>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros Rápidos (Estilo sys-POS) -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('vehiculos.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <!-- Input Búsqueda -->
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar por placa, número interno, marca o modelo (Enter)...">
            </div>

            <!-- Filtro Tipo de Vehículo -->
            <div class="md:col-span-3">
                <select name="tipo" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Tipos</option>
                    @foreach($tiposDisponibles as $t)
                    <option value="{{ $t }}" {{ ($tipo ?? '') == $t ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Estado -->
            <div class="md:col-span-2">
                <select name="estado" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Estados</option>
                    <option value="Activo" {{ ($estado ?? '') == 'Activo' ? 'selected' : '' }}>Activo</option>
                    <option value="Mantenimiento" {{ ($estado ?? '') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="Inactivo" {{ ($estado ?? '') == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>

            <!-- Filtro Solo Alertas & Limpiar -->
            <div class="md:col-span-2 flex items-center justify-between md:justify-end gap-2">
                <label class="inline-flex items-center space-x-2 cursor-pointer text-xs font-bold text-slate-700 select-none bg-slate-50 hover:bg-slate-100 px-3 py-2.5 rounded-xl border border-slate-200 transition">
                    <input type="checkbox" name="alerta" value="1" {{ ($alerta ?? false) ? 'checked' : '' }} onchange="this.form.submit()"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                    <span class="flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full {{ $totalAlertas > 0 ? 'bg-rose-500 animate-pulse' : 'bg-slate-300' }}"></span>
                        Alertas
                    </span>
                </label>

                @if(!empty($term) || !empty($tipo) || !empty($estado) || !empty($alerta))
                <a href="{{ route('vehiculos.index') }}"
                    class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-2.5 py-2.5 rounded-xl transition flex items-center gap-1"
                    title="Limpiar filtros">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Vehículos (Dual: Tabla Completa en Desktop / Cards en Móvil y Tablet) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Vista Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 table-auto text-left">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Vehículo / Placa</th>
                        <th class="px-4 py-3.5">Clase & Capacidad</th>
                        <th class="px-4 py-3.5">Conductor Asignado</th>
                        <th class="px-4 py-3.5 text-center">SOAT</th>
                        <th class="px-4 py-3.5 text-center">RTM</th>
                        <th class="px-4 py-3.5 text-center">T. Operación</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-sm">
                    @forelse($vehiculos as $v)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- 1. Vehículo / Placa -->
                        <td class="px-5 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 font-black text-xs shadow-xs flex-shrink-0">
                                    {{ $v->internal_number ? '#' . $v->internal_number : 'VEH' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-slate-900 border border-amber-300 font-mono text-sm font-extrabold tracking-wider shadow-2xs">
                                            {{ $v->plate }}
                                        </span>
                                        <span class="text-xs text-slate-500 font-semibold">{{ $v->model_year }}</span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 truncate">{{ $v->brand }} {{ $v->line }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. Clase & Capacidad -->
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-800">{{ $v->vehicle_type }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $v->passenger_capacity }} Pasajeros</div>
                        </td>

                        <!-- 3. Conductor Asignado -->
                        <td class="px-4 py-4">
                            @if($v->defaultDriver)
                                <div class="font-medium text-slate-800">{{ $v->defaultDriver->first_name }} {{ $v->defaultDriver->last_name }}</div>
                                <div class="text-xs text-slate-400 font-mono">Lic: {{ $v->defaultDriver->license_number ?? 'S/N' }}</div>
                            @else
                                <span class="text-xs text-slate-400 italic">Sin conductor fijo</span>
                            @endif
                        </td>

                        <!-- 4. SOAT (Semáforo) -->
                        <td class="px-4 py-4 text-center">
                            @php
                                $soatDate = $v->soat_expiration;
                                $soatStatus = 'none';
                                if ($soatDate) {
                                    if ($soatDate->lt(now()->startOfDay())) {
                                        $soatStatus = 'expired';
                                    } elseif ($soatDate->lte(now()->addDays(30)->startOfDay())) {
                                        $soatStatus = 'warning';
                                    } else {
                                        $soatStatus = 'ok';
                                    }
                                }
                            @endphp
                            @if($soatStatus === 'expired')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200" title="Vencido el {{ $soatDate->format('d/m/Y') }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span> {{ $soatDate->format('d/m/y') }}
                                </span>
                            @elseif($soatStatus === 'warning')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Vence el {{ $soatDate->format('d/m/Y') }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span> {{ $soatDate->format('d/m/y') }}
                                </span>
                            @elseif($soatStatus === 'ok')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $soatDate->format('d/m/y') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">N/R</span>
                            @endif
                        </td>

                        <!-- 5. RTM (Semáforo) -->
                        <td class="px-4 py-4 text-center">
                            @php
                                $rtmDate = $v->technomechanical_expiration;
                                $rtmStatus = 'none';
                                if ($rtmDate) {
                                    if ($rtmDate->lt(now()->startOfDay())) {
                                        $rtmStatus = 'expired';
                                    } elseif ($rtmDate->lte(now()->addDays(30)->startOfDay())) {
                                        $rtmStatus = 'warning';
                                    } else {
                                        $rtmStatus = 'ok';
                                    }
                                }
                            @endphp
                            @if($rtmStatus === 'expired')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span> {{ $rtmDate->format('d/m/y') }}
                                </span>
                            @elseif($rtmStatus === 'warning')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span> {{ $rtmDate->format('d/m/y') }}
                                </span>
                            @elseif($rtmStatus === 'ok')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $rtmDate->format('d/m/y') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">N/R</span>
                            @endif
                        </td>

                        <!-- 6. Tarjeta de Operación -->
                        <td class="px-4 py-4 text-center">
                            @php
                                $toDate = $v->operation_card_expiration;
                                $toStatus = 'none';
                                if ($toDate) {
                                    if ($toDate->lt(now()->startOfDay())) {
                                        $toStatus = 'expired';
                                    } elseif ($toDate->lte(now()->addDays(30)->startOfDay())) {
                                        $toStatus = 'warning';
                                    } else {
                                        $toStatus = 'ok';
                                    }
                                }
                            @endphp
                            @if($toStatus === 'expired')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $toDate->format('d/m/y') }}
                                </span>
                            @elseif($toStatus === 'warning')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ $toDate->format('d/m/y') }}
                                </span>
                            @elseif($toStatus === 'ok')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $toDate->format('d/m/y') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">N/R</span>
                            @endif
                        </td>

                        <!-- 7. Estado -->
                        <td class="px-4 py-4 text-center">
                            @php
                                $estadoColors = [
                                    'Activo' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Mantenimiento' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Inactivo' => 'bg-slate-100 text-slate-600 border-slate-300',
                                ];
                            @endphp
                            <form action="{{ route('vehiculos.toggle-status', $v->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="Click para cambiar estado"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border transition hover:opacity-80 {{ $estadoColors[$v->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                    {{ $v->status }}
                                </button>
                            </form>
                        </td>

                        <!-- 8. Acciones -->
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('vehiculos.show', $v->id) }}"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Ver ficha técnica">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <a href="{{ route('vehiculos.edit', $v->id) }}"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Editar datos y renovar documentos">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <svg class="h-12 w-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <p class="font-medium text-slate-600">No se encontraron vehículos que coincidan con la búsqueda.</p>
                            <p class="text-xs text-slate-400 mt-1">Prueba cambiando los filtros o registra un nuevo vehículo en la flota.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil (Cards Táctiles) -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($vehiculos as $v)
            <div class="p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-sm font-bold">
                            {{ $v->plate }}
                        </span>
                        <span class="text-xs font-semibold text-slate-500">{{ $v->internal_number ? 'Int ' . $v->internal_number : '' }}</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold border {{ $estadoColors[$v->status] ?? 'bg-slate-50 text-slate-700' }}">
                        {{ $v->status }}
                    </span>
                </div>

                <div class="text-xs text-slate-600">
                    <span class="font-bold text-slate-800">{{ $v->brand }} {{ $v->line }} ({{ $v->model_year }})</span> • {{ $v->vehicle_type }} ({{ $v->passenger_capacity }} Pasajeros)
                </div>

                <!-- Semáforos en Móvil -->
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-[11px]">
                    <div>
                        <span class="text-slate-400 block">SOAT:</span>
                        <span class="font-mono font-semibold {{ $v->soat_expiration?->lt(now()) ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $v->soat_expiration?->format('d/m/y') ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">RTM:</span>
                        <span class="font-mono font-semibold {{ $v->technomechanical_expiration?->lt(now()) ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $v->technomechanical_expiration?->format('d/m/y') ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">T. Operación:</span>
                        <span class="font-mono font-semibold {{ $v->operation_card_expiration?->lt(now()) ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $v->operation_card_expiration?->format('d/m/y') ?? 'N/A' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('vehiculos.show', $v->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Ver Ficha
                    </a>
                    <a href="{{ route('vehiculos.edit', $v->id) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold hover:bg-blue-100 transition">
                        Editar
                    </a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                No hay vehículos disponibles.
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($vehiculos->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $vehiculos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
