@extends('layouts.app')

@section('title', 'Orden de Servicio ' . $ordene->order_number)

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('ordenes.index') }}" class="hover:underline">Órdenes de Servicio</a>
                <span>/</span>
                <span class="text-slate-800">Detalle Operativo</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Orden #{{ $ordene->order_number }}</h1>
                @php
                    $statusClasses = [
                        'En Progreso' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Programada' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Finalizada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Cancelada' => 'bg-rose-50 text-rose-700 border-rose-200',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClasses[$ordene->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                    {{ $ordene->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Programada para el {{ $ordene->scheduled_start_time?->format('d/m/Y \a \l\a\s H:i A') }}.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if($ordene->fuecDocument)
                <a href="{{ route('tenant.fuec.pdf', $ordene->fuecDocument->id) }}" target="_blank"
                    class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Descargar FUEC PDF
                </a>
            @else
                <form action="{{ route('ordenes.emitir-fuec', $ordene->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition flex items-center">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Emitir FUEC Oficial
                    </button>
                </form>
            @endif
            <a href="{{ route('ordenes.edit', $ordene->id) }}"
                class="px-4 py-2.5 rounded-xl border border-amber-300 text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition flex items-center">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Editar
            </a>
            <form action="{{ route('ordenes.duplicar', $ordene->id) }}" method="POST" onsubmit="return confirm('¿Desea duplicar/clonar esta orden de servicio para programar un nuevo viaje?')">
                @csrf
                <button type="submit"
                    class="px-4 py-2.5 rounded-xl border border-indigo-200 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition flex items-center" title="Clonar orden para nuevo viaje">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    Duplicar
                </button>
            </form>
            <a href="{{ route('ordenes.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Barra de Control Operacional y Ciclo de Vida -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm" x-data="{ modalInicio: false, modalFin: false, modalCancelar: false, modalSolicitudGerencial: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-2xl flex items-center justify-center {{ $hasChecklistToday ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    @if($hasChecklistToday)
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    @else
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    @endif
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inspección Preoperacional Hoy</div>
                    <div class="text-sm font-extrabold text-slate-800">
                        {{ $hasChecklistToday ? 'Checklist Aprobado' : 'Pendiente de Inspección Técnica Diaria' }}
                    </div>
                </div>
            </div>

            <!-- Botones de Acción de Estado -->
            <div class="flex flex-wrap items-center gap-2">
                @if(in_array($ordene->status, ['Pendiente', 'Asignada']))
                    <button @click="modalInicio = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition flex items-center">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Iniciar Servicio
                    </button>
                    <button @click="modalCancelar = true"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                        Cancelar
                    </button>
                @elseif($ordene->status === 'En Progreso')
                    <button @click="modalFin = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Finalizar Servicio
                    </button>
                @elseif($ordene->status === 'Finalizada')
                    <div class="flex items-center space-x-3 text-xs bg-slate-50 px-3.5 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-500">Km Inicio: <strong class="text-slate-800">{{ number_format($ordene->start_mileage ?? 0) }}</strong></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-500">Km Fin: <strong class="text-slate-800">{{ number_format($ordene->end_mileage ?? 0) }}</strong></span>
                        @if($ordene->end_mileage && $ordene->start_mileage)
                        <span class="text-slate-300">|</span>
                        <span class="text-emerald-600 font-bold">Recorrido: {{ number_format($ordene->end_mileage - $ordene->start_mileage) }} km</span>
                        @endif
                    </div>
                @endif

                <button @click="modalSolicitudGerencial = true"
                    class="px-3 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition flex items-center">
                    <svg class="h-3.5 w-3.5 mr-1 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    Solicitud Gerencial
                </button>
            </div>
        </div>

        <!-- Modal Iniciar Servicio -->
        <div x-show="modalInicio" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="modalInicio = false">
                <h3 class="text-lg font-bold text-slate-900">Iniciar Servicio de Transporte</h3>
                <p class="text-xs text-slate-500">Se registrará la hora de inicio oficial y se actualizará el odómetro del vehículo.</p>

                @if(!$hasChecklistToday)
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 font-medium">
                        ⚠️ Advertencia: Recuerda que para iniciar el servicio en ruta el vehículo debe tener la inspección preoperacional aprobada por el conductor.
                    </div>
                @endif

                <form action="{{ route('ordenes.status', $ordene->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="En Progreso">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Kilometraje Inicial (Odómetro):</label>
                        <input type="number" step="0.1" name="mileage" value="{{ $ordene->vehicle?->current_mileage ?? '' }}" required
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" @click="modalInicio = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700">Confirmar Inicio</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Finalizar Servicio -->
        <div x-show="modalFin" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="modalFin = false">
                <h3 class="text-lg font-bold text-slate-900">Finalizar Servicio</h3>
                <p class="text-xs text-slate-500">Se registrará la hora de culminación del viaje y el kilometraje final para auditoría operativa.</p>
                <form action="{{ route('ordenes.status', $ordene->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="Finalizada">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Kilometraje Final (Odómetro):</label>
                        <input type="number" step="0.1" name="mileage" value="{{ ($ordene->start_mileage ?? $ordene->vehicle?->current_mileage ?? 0) + 15 }}" required
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Novedades o Notas de Cierre (Opcional):</label>
                        <textarea name="service_notes" rows="2" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Servicio concluido sin novedades..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" @click="modalFin = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Volver</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700">Completar Viaje</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Cancelar Orden -->
        <div x-show="modalCancelar" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="modalCancelar = false">
                <h3 class="text-lg font-bold text-rose-700">Cancelar Orden de Servicio</h3>
                <p class="text-xs text-slate-500">Indica el motivo de cancelación del servicio asignado.</p>
                <form action="{{ route('ordenes.status', $ordene->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="Cancelada">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Motivo de Cancelación:</label>
                        <textarea name="service_notes" rows="3" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-500" placeholder="Cancelado por solicitud del cliente..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" @click="modalCancelar = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Regresar</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700">Confirmar Cancelación</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Solicitud Gerencial -->
        <div x-show="modalSolicitudGerencial" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="modalSolicitudGerencial = false">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">Solicitud Gerencial</h3>
                    <button @click="modalSolicitudGerencial = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <p class="text-xs text-slate-500">Envía una solicitud auditada a la gerencia de operaciones para autorizar la cancelación o modificación de este servicio.</p>
                <form action="{{ route('gerencial.aprobaciones.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="service_order_id" value="{{ $ordene->id }}">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Tipo de Solicitud:</label>
                        <select name="request_type" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="Cancelacion">Cancelación de Orden de Servicio</option>
                            <option value="Modificacion">Modificación Operacional</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Motivo / Justificación:</label>
                        <textarea name="reason" rows="3" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="Indique las razones para la consideración gerencial..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" @click="modalSolicitudGerencial = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700">Enviar a Gerencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Grid de Información -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarjeta 1: Ruta y Cliente -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                Itinerario & Pasajeros
            </h2>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Cliente:</span>
                    <span class="font-bold text-slate-900">{{ $ordene->client?->business_name }}</span>
                    <span class="text-xs text-slate-500 block">NIT: {{ $ordene->client?->document_number }} • Tel: {{ $ordene->client?->phone }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <span class="font-semibold text-slate-800">Origen:</span>
                        <span class="text-slate-600">{{ $ordene->origin }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-800">Destino:</span>
                        <span class="text-slate-600">{{ $ordene->destination }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="text-slate-500 font-medium">Pasajeros: <strong class="text-slate-900">{{ $ordene->passengers_count }}</strong></span>
                    <span class="text-slate-500">Contacto: <strong class="text-slate-900">{{ $ordene->passenger_contact_name ?? 'N/A' }}</strong></span>
                </div>

                @if($ordene->service_notes)
                <div class="pt-2 border-t border-slate-100 text-xs text-slate-500">
                    <span class="font-bold block text-slate-700 mb-0.5">Notas del servicio:</span>
                    {{ $ordene->service_notes }}
                </div>
                @endif
            </div>
        </div>

        <!-- Tarjeta 2: Asignación de Flota y Tripulación -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                Vehículo & Conductor Asignado
            </h2>

            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="flex items-center space-x-3">
                        <span class="px-2.5 py-1 rounded-md bg-amber-50 text-slate-900 border border-amber-300 font-mono text-base font-extrabold">
                            {{ $ordene->vehicle?->plate ?? 'S/A' }}
                        </span>
                        <div>
                            <div class="font-bold text-slate-800 text-xs">{{ $ordene->vehicle?->brand }} {{ $ordene->vehicle?->line }}</div>
                            <div class="text-[11px] text-slate-400">{{ $ordene->vehicle?->vehicle_type }} ({{ $ordene->vehicle?->passenger_capacity }} Pax)</div>
                        </div>
                    </div>
                    @if($ordene->vehicle_id)
                    <a href="{{ route('vehiculos.show', $ordene->vehicle_id) }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Ver Ficha
                    </a>
                    @endif
                </div>

                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="text-xs text-slate-400">Conductor Titular:</div>
                    <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $ordene->driver?->first_name }} {{ $ordene->driver?->last_name }}</div>
                    <div class="text-xs text-slate-500 mt-1 font-mono">
                        C.C. {{ $ordene->driver?->document_number }} • Licencia: {{ $ordene->driver?->license_number }}
                    </div>
                </div>

                <!-- Estado del FUEC -->
                <div class="p-4 rounded-2xl border {{ $ordene->fuecDocument ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="h-2 w-2 rounded-full {{ $ordene->fuecDocument ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span class="text-xs font-bold uppercase">Estado FUEC:</span>
                        </div>
                        <span class="text-xs font-bold">{{ $ordene->fuecDocument ? 'Emitido Reglamentario' : 'Pendiente de Emisión' }}</span>
                    </div>
                    @if($ordene->fuecDocument)
                    <div class="mt-2 text-xs font-mono font-bold">
                        N°: {{ $ordene->fuecDocument->fuec_number }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($ordene->approvalRequests && $ordene->approvalRequests->isNotEmpty())
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </span>
                Historial de Solicitudes Gerenciales ({{ $ordene->approvalRequests->count() }})
            </h2>
            <a href="{{ route('gerencial.aprobaciones.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                Ir a Centro de Aprobaciones &rarr;
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($ordene->approvalRequests as $req)
            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900 font-mono">#SOL-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="px-2 py-0.5 rounded font-bold {{ $req->request_type === 'Cancelacion' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ $req->request_type }}
                        </span>
                        <span class="text-slate-400 font-mono">{{ $req->created_at?->format('d/m/Y H:i') }}</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-500">Por: <strong>{{ $req->requestedBy?->name ?? 'Usuario' }}</strong></span>
                    </div>
                    <p class="text-slate-700">&ldquo;{{ $req->reason }}&rdquo;</p>
                    @if($req->manager_notes)
                        <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 text-slate-600 italic">
                            <strong>Respuesta Gerencial:</strong> {{ $req->manager_notes }}
                            @if($req->reviewedBy)
                                <span class="text-slate-400 not-italic">({{ $req->reviewedBy->name }} el {{ $req->reviewed_at?->format('d/m/Y H:i') }})</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex items-center">
                    @if($req->status === 'Pendiente')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Pendiente de Revisión
                        </span>
                    @elseif($req->status === 'Aprobado')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Aprobado por Gerencia
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Rechazado
                        </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
