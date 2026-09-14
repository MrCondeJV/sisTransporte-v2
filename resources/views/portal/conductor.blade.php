@extends('layouts.transport')

@section('title', 'Portal del Conductor')

@section('content')
<div class="space-y-6" x-data="{ modalCombustible: false, modalNovedad: false, ordenNovedadId: null }">
    <!-- Header del Portal Conductor -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl mr-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
                Terminal Móvil del Conductor
            </h1>
            <p class="text-sm text-slate-500 mt-1">Gestión rápida de viajes asignados, odómetro en ruta y checklist preoperacional.</p>
        </div>

        <div class="mt-4 sm:mt-0 flex items-center space-x-2">
            <button @click="modalCombustible = true" type="button" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Recargar Combustible
            </button>
        </div>
    </div>

    <!-- KPI Cards (Estilo sys-POS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Card 1: Servicios Asignados -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Viajes Asignados</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $ordenes->count() }}</span>
                <span class="text-xs text-blue-600 font-medium mt-1 inline-block">Programados hoy</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Estado Checklist -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Inspección Diaria</span>
                <span class="text-2xl font-black {{ ($ultimoChecklist?->status === 'passed') ? 'text-emerald-600' : 'text-amber-600' }} mt-1 block">
                    {{ ($ultimoChecklist?->status === 'passed') ? 'Aprobado' : 'Pendiente' }}
                </span>
                <span class="text-xs text-slate-400 mt-1 inline-block">{{ $ultimoChecklist ? 'Último: ' . $ultimoChecklist->checklist_date : 'Sin registro hoy' }}</span>
            </div>
            <div class="h-12 w-12 rounded-xl {{ ($ultimoChecklist?->status === 'passed') ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Novedades Activas -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Novedades Reportadas</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $novedadesRecientes->count() }}</span>
                <span class="text-xs text-rose-600 font-medium mt-1 inline-block">Atención operativa</span>
            </div>
            <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Formulario Colapsable de Checklist Preoperacional Diario -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ checklistAbierto: false }">
        <div class="p-5 flex items-center justify-between cursor-pointer bg-slate-50/70" @click="checklistAbierto = !checklistAbierto">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Diligenciar Checklist Preoperacional Diario</h3>
                    <p class="text-xs text-slate-500">Obligatorio por normativa de transporte antes de iniciar turno de conducción.</p>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 p-2">
                <svg class="h-5 w-5 transform transition-transform" :class="checklistAbierto ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <div x-show="checklistAbierto" class="p-6 border-t border-slate-200">
            <form action="{{ route('portal.conductor.checklist') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Vehículo a Inspeccionar</label>
                        <select name="vehicle_id" required>
                            @foreach($vehiculos as $v)
                                <option value="{{ $v->id }}">{{ $v->plate }} - {{ $v->brand }} {{ $v->model }} ({{ $v->current_mileage }} Km)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Odómetro Actual (Km)</label>
                        <input type="number" name="current_odometer" placeholder="Ej: 145200" required>
                    </div>
                </div>

                <div class="pt-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Verificación de Sistemas de Seguridad</label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="frenos" value="1" checked class="rounded text-blue-600">
                            <span>Frenos</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="direccion" value="1" checked class="rounded text-blue-600">
                            <span>Dirección</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="luces" value="1" checked class="rounded text-blue-600">
                            <span>Luces</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="llantas" value="1" checked class="rounded text-blue-600">
                            <span>Llantas</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="limpiabrisas" value="1" checked class="rounded text-blue-600">
                            <span>Limpiabrisas</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="espejos" value="1" checked class="rounded text-blue-600">
                            <span>Espejos</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="cinturones" value="1" checked class="rounded text-blue-600">
                            <span>Cinturones</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="extintor" value="1" checked class="rounded text-blue-600">
                            <span>Extintor</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="botiquin" value="1" checked class="rounded text-blue-600">
                            <span>Botiquín</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50">
                            <input type="checkbox" name="equipo_carretera" value="1" checked class="rounded text-blue-600">
                            <span>Equipo Carr.</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones</label>
                    <textarea name="notes" rows="2" placeholder="Novedad en vehículo o estado de llantas..."></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-sm transition shadow-sm">
                    Firmar y Registrar Checklist Preoperacional
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de Servicios Asignados (Órdenes de Servicio) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center">
                <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Órdenes de Servicio en Curso y Programadas
            </h2>
            <span class="text-xs text-slate-400 font-medium">Control de Odómetro & FUEC</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($ordenes as $ord)
            <div class="p-5 hover:bg-slate-50/80 transition" x-data="{ modalInicio: false, modalFin: false }">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-mono font-bold text-slate-900 text-sm">#{{ $ord->order_number }}</span>
                            <!-- Badge de Estado -->
                            @if($ord->status === 'pending')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Pendiente</span>
                            @elseif($ord->status === 'in_progress')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-ping mr-1"></span> En Ruta
                                </span>
                            @elseif($ord->status === 'completed')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Completado</span>
                            @endif

                            <span class="px-2 py-0.5 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700">
                                🚗 {{ $ord->vehicle?->plate ?? 'Sin Placa' }}
                            </span>
                        </div>

                        <div class="text-sm font-semibold text-slate-800">
                            {{ $ord->origin }} <span class="text-blue-600">➔</span> {{ $ord->destination }}
                        </div>

                        <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span><strong>Cliente:</strong> {{ $ord->client?->name ?? 'Particular' }}</span>
                            <span><strong>Fecha:</strong> {{ $ord->service_date }}</span>
                            <span><strong>Pasajeros:</strong> {{ $ord->passenger_count ?? 1 }}</span>
                            @if($ord->initial_odometer)
                                <span class="font-mono text-slate-700"><strong>Odom. Inicial:</strong> {{ $ord->initial_odometer }} Km</span>
                            @endif
                            @if($ord->final_odometer)
                                <span class="font-mono text-emerald-700 font-bold"><strong>Odom. Final:</strong> {{ $ord->final_odometer }} Km</span>
                            @endif
                        </div>
                    </div>

                    <!-- Botones de Acción Operativa -->
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        @if($ord->status === 'pending')
                            <button @click="modalInicio = true" type="button" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">
                                ▶ Iniciar Viaje
                            </button>
                        @elseif($ord->status === 'in_progress')
                            <button @click="modalFin = true" type="button" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                                ✔ Finalizar Viaje
                            </button>
                        @endif

                        <!-- FUEC QR Link -->
                        @if($ord->fuecDocument)
                            <a href="{{ route('tenant.fuec.pdf', $ord->fuecDocument->id) }}" target="_blank" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Ver FUEC oficial">
                                📄 FUEC PDF
                            </a>
                        @endif

                        <!-- Reportar Novedad -->
                        <button @click="ordenNovedadId = {{ $ord->id }}; modalNovedad = true" type="button" class="px-2.5 py-2 text-xs font-semibold rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="Reportar Novedad en Ruta">
                            ⚠️ Novedad
                        </button>
                    </div>
                </div>

                <!-- Modal Iniciar Viaje -->
                <div x-cloak x-show="modalInicio" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl" @click.away="modalInicio = false">
                        <h3 class="text-base font-bold text-slate-900">Iniciar Servicio #{{ $ord->order_number }}</h3>
                        <form action="{{ route('portal.conductor.iniciar', $ord->id) }}" method="POST">
                            @csrf
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Odómetro Inicial (Tablero Km)</label>
                            <input type="number" name="initial_odometer" value="{{ $ord->vehicle?->current_mileage ?? 0 }}" required class="mb-4">
                            <div class="flex justify-end space-x-2">
                                <button type="button" @click="modalInicio = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Confirmar Inicio</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Finalizar Viaje -->
                <div x-cloak x-show="modalFin" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl" @click.away="modalFin = false">
                        <h3 class="text-base font-bold text-slate-900">Finalizar Servicio #{{ $ord->order_number }}</h3>
                        <form action="{{ route('portal.conductor.finalizar', $ord->id) }}" method="POST">
                            @csrf
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Odómetro Final (Tablero Km)</label>
                            <input type="number" name="final_odometer" min="{{ $ord->initial_odometer ?? 0 }}" value="{{ $ord->vehicle?->current_mileage ?? 0 }}" required class="mb-4">
                            <div class="flex justify-end space-x-2">
                                <button type="button" @click="modalFin = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl">Confirmar Fin de Viaje</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400">
                <p>No tienes órdenes de servicio asignadas en este momento.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Rápido de Recarga de Combustible -->
    <div x-cloak x-show="modalCombustible" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="modalCombustible = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    ⛽ Registrar Recarga de Combustible
                </h3>
                <button type="button" @click="modalCombustible = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('portal.conductor.combustible') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Vehículo</label>
                    <select name="vehicle_id" required>
                        @foreach($vehiculos as $v)
                            <option value="{{ $v->id }}">{{ $v->plate }} ({{ $v->brand }} {{ $v->model }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Galones / Litros</label>
                        <input type="number" step="0.01" name="liters" placeholder="Ej: 12.5" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Costo Total ($)</label>
                        <input type="number" step="100" name="cost" placeholder="Ej: 135000" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Odómetro (Km)</label>
                        <input type="number" name="odometer" placeholder="Ej: 145250" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Estación / EDS</label>
                        <input type="text" name="gas_station" placeholder="Ej: Primax / Terpel" required>
                    </div>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <button type="button" @click="modalCombustible = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl">Guardar Recarga</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reporte de Novedad -->
    <div x-cloak x-show="modalNovedad" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="modalNovedad = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center text-rose-600">
                    ⚠️ Reportar Novedad en Ruta
                </h3>
                <button type="button" @click="modalNovedad = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('portal.conductor.novedad') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="service_order_id" :value="ordenNovedadId">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipo de Incidencia</label>
                    <select name="incident_type" required>
                        <option value="mechanical">Falla Mecánica o Eléctrica</option>
                        <option value="traffic">Retraso por Tráfico / Vía Cerrada</option>
                        <option value="accident">Accidente o Colisión</option>
                        <option value="passenger">Novedad con Pasajeros</option>
                        <option value="other">Otra Contingencia</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nivel de Severidad</label>
                    <select name="severity">
                        <option value="low">Baja (Sin riesgo de cancelación)</option>
                        <option value="medium" selected>Media (Posible retraso)</option>
                        <option value="high">Alta (Requiere vehículo de apoyo o auxilio vial)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Descripción de lo Sucedido</label>
                    <textarea name="description" rows="3" placeholder="Detalla el problema y ubicación actual..." required></textarea>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <button type="button" @click="modalNovedad = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Enviar Reporte a Central</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
