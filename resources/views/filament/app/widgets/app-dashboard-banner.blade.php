@php
    $tenant = $this->getTenant();
    $nombreEmpresa = $tenant?->name ?? 'Transportes Especiales S.A.S.';
    $nitEmpresa = $tenant?->nit ?? '901.234.567-8';
    $user = auth()->user();
    $rol = $user?->roles?->first()?->name ?? 'Administrador';
@endphp

<div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="max-w-2xl">
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-3">
                <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                Base de Datos Aislada: <span class="font-mono ml-1 font-bold">{{ $tenant?->id ?? 'empresa1' }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                {{ $nombreEmpresa }}
            </h1>
            <p class="mt-2 text-slate-300 text-sm sm:text-base leading-relaxed">
                Bienvenido, <span class="font-bold text-white">{{ $user?->name ?? 'Administrador' }}</span>. Has iniciado sesión con rol <span class="text-indigo-300 font-semibold">{{ $rol }}</span>. Control operacional, flota y emisión FUEC al día.
            </p>
        </div>

        <!-- Botones de Acción Rápida (Idéntico a sys-POS) -->
        <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
            <a href="{{ url('/app/service-orders/create') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-900 bg-white hover:bg-slate-100 shadow-md transition">
                <svg class="h-4 w-4 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Orden (ODS)
            </a>

            <a href="{{ url('/app/fuec/fuec-documents') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                <svg class="h-4 w-4 mr-2 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                FUEC Oficial
            </a>

            <a href="{{ url('/app/ops-wallboard') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                <svg class="h-4 w-4 mr-2 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                GPS Wallboard
            </a>
        </div>
    </div>

    <!-- Marca de agua vectorial de transporte -->
    <div class="absolute right-0 bottom-0 opacity-10 hidden lg:block pointer-events-none transform translate-x-8 translate-y-8">
        <svg class="h-64 w-64 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
    </div>
</div>
