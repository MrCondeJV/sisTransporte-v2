@extends('layouts.app')

@section('title', 'Aprobaciones Gerenciales de Órdenes')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{
 approvalModalOpen: false,
 rejectModalOpen: false,
 selectedApproval: null,
 selectedActionUrl: '',
 openApprove(item, url) {
 this.selectedApproval = item;
 this.selectedActionUrl = url;
 this.approvalModalOpen = true;
 },
 openReject(item, url) {
 this.selectedApproval = item;
 this.selectedActionUrl = url;
 this.rejectModalOpen = true;
 }
}">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Auditoría & Control Gerencial</span>
                <span>/</span>
                <span class="text-slate-800">Solicitudes de Aprobación</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Aprobaciones Gerenciales</h1>
            <p class="text-sm text-slate-500 mt-1">
                Flujo de autorización y control de auditoría para cancelaciones y modificaciones excepcionales de servicios de transporte.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('ordenes.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Ir a Órdenes de Servicio
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pendientes de Revisión</p>
                <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $pendientesCount }}</p>
                <p class="text-xs text-slate-500 mt-1">Requieren decisión gerencial</p>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aprobadas</p>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $aprobadasCount }}</p>
                <p class="text-xs text-slate-500 mt-1">Autorizadas por gerencia</p>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rechazadas</p>
                <p class="text-3xl font-extrabold text-rose-600 mt-1">{{ $rechazadasCount }}</p>
                <p class="text-xs text-slate-500 mt-1">Desestimadas con observación</p>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('gerencial.aprobaciones.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <div class="md:col-span-5">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Estado de la Solicitud:</label>
                <select name="estado" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" {{ ($estado === 'Pendiente') ? 'selected' : '' }}>Pendientes de Revisión</option>
                    <option value="Aprobado" {{ ($estado === 'Aprobado') ? 'selected' : '' }}>Aprobadas</option>
                    <option value="Rechazado" {{ ($estado === 'Rechazado') ? 'selected' : '' }}>Rechazadas</option>
                </select>
            </div>

            <div class="md:col-span-5">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Tipo de Acción:</label>
                <select name="tipo" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los Tipos</option>
                    <option value="Cancelacion" {{ ($tipo === 'Cancelacion') ? 'selected' : '' }}>Cancelación de ODS</option>
                    <option value="Modificacion" {{ ($tipo === 'Modificacion') ? 'selected' : '' }}>Modificación de ODS</option>
                </select>
            </div>

            <div class="md:col-span-2 flex items-end justify-end">
                @if($estado || $tipo)
                    <a href="{{ route('gerencial.aprobaciones.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        Limpiar Filtros
                    </a>
                @endif
            </div>
        </form>
    </div>
    <!-- Tabla de Solicitudes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Solicitud & Fecha</th>
                        <th class="px-4 py-3.5">Orden de Servicio</th>
                        <th class="px-4 py-3.5">Tipo & Motivo</th>
                        <th class="px-4 py-3.5">Solicitante</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-4 py-3.5">Revisión Gerencial</th>
                        <th class="px-5 py-3.5 text-right">Decisión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
        @forelse($solicitudes as $solicitud)
            <tr class="hover:bg-slate-50/80 transition">
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="font-extrabold text-slate-900 font-mono">#SOL-{{ str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) }}</div>
                    <div class="text-xs text-slate-400">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</div>
                </td>
                <td class="px-4 py-4">
                    @if($solicitud->serviceOrder)
                        <a href="{{ route('ordenes.show', $solicitud->service_order_id) }}" class="font-bold text-blue-600 hover:underline">
                            #{{ $solicitud->serviceOrder->order_number }}
                        </a>
                        <div class="text-xs text-slate-600 mt-0.5 truncate max-w-xs">{{ $solicitud->serviceOrder->client?->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $solicitud->serviceOrder->origin }} &rarr; {{ $solicitud->serviceOrder->destination }}</div>
                    @else
                        <span class="text-xs text-slate-400 italic">Orden eliminada</span>
                    @endif
                </td>
                <td class="px-4 py-4 max-w-xs">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $solicitud->request_type === 'Cancelacion' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                        {{ $solicitud->request_type }}
                    </span>
                    <div class="text-xs text-slate-700 mt-1 line-clamp-2" title="{{ $solicitud->reason }}">
                        {{ $solicitud->reason }}
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="font-semibold text-slate-800 text-xs">{{ $solicitud->requestedBy?->name ?? 'Usuario del sistema' }}</div>
                    <div class="text-[11px] text-slate-400">{{ $solicitud->requestedBy?->email }}</div>
                </td>
                <td class="px-4 py-4 text-center whitespace-nowrap">
                    @if($solicitud->status === 'Pendiente')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                            Pendiente
                        </span>
                    @elseif($solicitud->status === 'Aprobado')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                            Aprobado
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                            Rechazado
                        </span>
                    @endif
                </td>
                <td class="px-4 py-4 max-w-xs">
                    @if($solicitud->reviewed_at)
                        <div class="text-xs font-semibold text-slate-800">{{ $solicitud->reviewedBy?->name ?? 'Gerencia' }}</div>
                        <div class="text-[11px] text-slate-400">{{ $solicitud->reviewed_at?->format('d/m/Y H:i') }}</div>
                        @if($solicitud->manager_notes)
                            <div class="text-xs text-slate-600 mt-1 italic line-clamp-2" title="{{ $solicitud->manager_notes }}">
                                &ldquo;{{ $solicitud->manager_notes }}&rdquo;
                            </div>
                        @endif
                    @else
                        <span class="text-xs text-slate-300 italic">En espera de decisión</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    @if($solicitud->status === 'Pendiente')
                        <div class="flex items-center justify-end gap-2">
                            <button type="button"
                                @click="openApprove({{ json_encode($solicitud) }}, '{{ route('gerencial.aprobaciones.approve', $solicitud->id) }}')"
                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition">
                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Aprobar
                            </button>
                            <button type="button"
                                @click="openReject({{ json_encode($solicitud) }}, '{{ route('gerencial.aprobaciones.reject', $solicitud->id) }}')"
                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">
                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                Rechazar
                            </button>
                        </div>
                    @else
                        <span class="text-xs text-slate-400 font-medium">Procesada</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-16 text-center">
                    <div class="max-w-sm mx-auto flex flex-col items-center">
                        <div class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">No hay solicitudes registradas</h3>
                        <p class="text-xs text-slate-400 mt-1">Las solicitudes para cancelar o modificar órdenes de servicio aparecen aquí para autorización de la gerencia.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
    </table>
    </div>

    @if($solicitudes->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $solicitudes->links() }}
        </div>
    @endif
    </div>

    <!-- Modal Aprobar -->
    <div x-show="approvalModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="approvalModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-extrabold text-slate-900">Autorizar Solicitud Gerencial</h3>
                    <button type="button" @click="approvalModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form :action="selectedActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600">
                        ¿Está seguro de autorizar la solicitud de <strong x-text="selectedApproval ? selectedApproval.request_type : ''"></strong>?
                        Si es una cancelación, el estado de la orden pasará a <strong>Cancelada</strong> de inmediato.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notas de Gerencia / Observación (Opcional):</label>
                        <textarea name="manager_notes" rows="3" placeholder="Aprobado de conformidad con la política de cancelaciones..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="approvalModalOpen = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200">
                            Volver
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                            Confirmar Aprobación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Rechazar -->
    <div x-show="rejectModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="rejectModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-extrabold text-slate-900 text-rose-600">Rechazar Solicitud Gerencial</h3>
                    <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form :action="selectedActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600">
                        Indique el motivo por el cual gerencia rechaza esta solicitud. Esta justificación quedará registrada en la auditoría.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motivo del Rechazo (Obligatorio):</label>
                        <textarea name="manager_notes" rows="3" required placeholder="Se rechaza por no cumplir con la anticipación mínima requerida..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="rejectModalOpen = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200">
                            Volver
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm">
                            Confirmar Rechazo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
