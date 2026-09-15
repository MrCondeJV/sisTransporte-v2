@extends('layouts.app')

@section('title', 'Dashboard Operativo')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-8">
    <!-- Banner de Bienvenida y Estado Operativo (Estilo sys-POS) -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30 mb-3">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    Centro de Operaciones Activo
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    {{ tenancy()->initialized ? tenant('name') : 'Transportes Kemuel S.A.S.' }}
                </h1>
                <p class="mt-2 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Bienvenido, <span class="font-bold text-white">{{ auth()->user()?->name ?? 'Operador' }}</span>. Gestiona el parque automotor, emisión de FUECs digitales, despachos y control de seguridad vial en tiempo real.
                </p>
            </div>

            <!-- Botones de Acción Directa -->
            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                <a href="{{ route('ordenes.create') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-900 bg-white hover:bg-slate-100 shadow-md transition">
                    <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Orden
                </a>
                <a href="{{ route('vehiculos.index') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                    <svg class="h-4 w-4 mr-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Catálogo de Flota
                </a>
                <a href="{{ route('monitoreo.index') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-cyan-300 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 transition">
                    <span class="h-2 w-2 rounded-full bg-cyan-400 mr-2 animate-pulse"></span>
                    Wallboard GPS
                </a>
            </div>
        </div>

        <div class="absolute right-0 bottom-0 opacity-10 hidden lg:block pointer-events-none">
            <svg class="h-72 w-72 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </div>
    </div>

    <!-- Indicadores Operativos Clave (KPIs de Alto Impacto estilo sys-POS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- KPI 1: Vehículos Activos -->
        <a href="{{ route('vehiculos.index') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-blue-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Flota Operativa</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $vehiculosActivos }} / {{ $totalVehiculos }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>{{ $vehiculosEnMantenimiento }} en mantenimiento</span>
                <span class="text-blue-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI 2: Órdenes de Servicio Hoy -->
        <a href="{{ route('ordenes.index') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-amber-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Órdenes del Día</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-3">
                {{ $ordenesHoy }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>{{ $ordenesEnCurso }} en ruta activa</span>
                <span class="text-amber-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI 3: FUECs Vigentes -->
        <a href="{{ route('fuec.index') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">FUEC Digital</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-3">
                {{ $fuecsActivos }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>{{ $totalFuecs }} planillas históricas</span>
                <span class="text-emerald-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI 4: Semáforo Documental -->
        <a href="{{ route('vehiculos.index') }}?alerta=1" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-rose-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vencimientos SOAT/RTM</span>
                <span class="p-2 rounded-xl {{ $documentosVencidos > 0 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600' }} transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold {{ $documentosVencidos > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-3">
                {{ $documentosPorVencer + $documentosVencidos }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>{{ $documentosVencidos }} vencidos, {{ $documentosPorVencer }} por vencer</span>
                <span class="text-rose-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>
    </div>

    <!-- Tablero Dual: Despachos en Curso y Semáforo de Vehículos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda (2 cols): Despachos y Órdenes Recientes -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Despachos y Órdenes Recientes</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Seguimiento en tiempo real de servicios programados y en ruta</p>
                </div>
                <a href="{{ route('ordenes.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                    Ver todas &rarr;
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full divide-y divide-slate-100 text-sm text-left">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Orden #</th>
                            <th class="px-4 py-3">Cliente / Ruta</th>
                            <th class="px-4 py-3">Vehículo & Conductor</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">FUEC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($ordenesRecientes as $orden)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">
                                #{{ $orden->order_number }}
                                <div class="text-[11px] font-normal text-slate-400">{{ $orden->scheduled_start_time?->format('d/m H:i') }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $orden->client?->business_name ?? 'Particular' }}</div>
                                <div class="text-xs text-slate-400 truncate max-w-xs">{{ $orden->origin }} &rarr; {{ $orden->destination }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-mono text-xs font-bold border border-slate-200">
                                    {{ $orden->vehicle?->plate ?? 'Sin asignar' }}
                                </span>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $orden->driver?->first_name }} {{ $orden->driver?->last_name }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $statusClasses = [
                                        'En Progreso' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Programada' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Finalizada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Cancelada' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClasses[$orden->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                    {{ $orden->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                @if($orden->fuecDocument)
                                    <a href="{{ route('tenant.fuec.pdf', $orden->fuecDocument->id) }}" target="_blank"
                                        class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200 transition">
                                        PDF QR
                                    </a>
                                @else
                                    <span class="text-xs text-slate-300">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">
                                No hay órdenes de servicio registradas recientemente.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Columna Derecha (1 col): Alertas Documentales de Flota -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Alertas de Flota</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Vencimientos próximos</p>
                </div>
                <span class="h-2 w-2 rounded-full {{ $documentosVencidos > 0 ? 'bg-rose-500 animate-ping' : 'bg-amber-400' }}"></span>
            </div>

            <div class="divide-y divide-slate-100 mt-2 flex-1">
                @forelse($vehiculosAlerta as $v)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-mono font-bold text-sm text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                {{ $v->plate }}
                            </span>
                            <span class="text-xs text-slate-500">Int: {{ $v->internal_number ?? 'S/N' }}</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">
                            SOAT: {{ $v->soat_expiration?->format('d/m/Y') ?? 'N/A' }} | RTM: {{ $v->technomechanical_expiration?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                    </div>
                    <a href="{{ route('vehiculos.edit', $v->id) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        Renovar
                    </a>
                </div>
                @empty
                <div class="py-10 text-center text-slate-400 text-sm">
                    <svg class="h-8 w-8 text-emerald-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Todos los documentos de la flota están al día.
                </div>
                @endforelse
            </div>

            <div class="pt-4 border-t border-slate-100 mt-auto">
                <a href="{{ route('vehiculos.index') }}" class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold transition">
                    Ver Todo el Parque Automotor
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
