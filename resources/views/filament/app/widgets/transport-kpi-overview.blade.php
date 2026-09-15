@php
    $kpi = $this->getKpiData();
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <!-- KPI 1: Órdenes Activas Hoy -->
    <a href="{{ url('/app/service-orders') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition group block">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Órdenes en Servicio</span>
            <span class="p-2 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
            {{ $kpi['ordenesActivas'] }}
        </div>
        <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
            <span>{{ $kpi['ordenesHoy'] }} programadas hoy</span>
            <span class="text-indigo-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
        </div>
    </a>

    <!-- KPI 2: FUEC Oficiales Emitidos -->
    <a href="{{ url('/app/fuec/fuec-documents') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-400 hover:shadow-md transition group block">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">FUEC Digital (Mes)</span>
            <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-3">
            {{ $kpi['fuecsMes'] }}
        </div>
        <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
            <span>{{ $kpi['fuecsTotal'] }} extractos históricos</span>
            <span class="text-emerald-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
        </div>
    </a>

    <!-- KPI 3: Flota Operativa -->
    <a href="{{ url('/app/vehicles') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition group block">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Flota Disponible</span>
            <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
            {{ $kpi['vehiculosActivos'] }}
        </div>
        <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
            <span>{{ $kpi['vehiculosTaller'] }} en mantenimiento</span>
            <span class="text-indigo-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
        </div>
    </a>

    <!-- KPI 4: Alertas Preventivas de Vencimiento -->
    <a href="{{ url('/app/vehicles') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-amber-400 hover:shadow-md transition group block">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alertas Preventivas</span>
            <span class="p-2 rounded-xl {{ $kpi['alertasVencimiento'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold {{ $kpi['alertasVencimiento'] > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-3 flex items-center gap-2">
            {{ $kpi['alertasVencimiento'] }}
            @if($kpi['alertasVencimiento'] > 0)
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
            @endif
        </div>
        <div class="text-xs {{ $kpi['alertasVencimiento'] > 0 ? 'text-amber-600 font-medium' : 'text-emerald-600 font-medium' }} mt-1 flex items-center justify-between">
            <span>{{ $kpi['alertasVencimiento'] > 0 ? 'SOAT / Tecno por renovar' : 'Documentación al día' }}</span>
            <span class="group-hover:translate-x-1 transition">&rarr;</span>
        </div>
    </a>
</div>
