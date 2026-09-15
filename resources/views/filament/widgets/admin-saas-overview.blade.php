@php
    $metrics = $this->getSaaSMetrics();
    $user = auth()->user();
@endphp

<div class="space-y-6">
    <!-- Banner Central SaaS (Estilo sys-POS SuperAdmin) -->
    <div class="bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30 mb-3">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    SuperAdmin SaaS · Multi-Database per Tenant
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Plataforma Central sisTransporte
                </h1>
                <p class="mt-2 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Bienvenido, <span class="font-bold text-white">{{ $user?->name ?? 'SuperAdmin' }}</span>. Supervisión global de empresas afiliadas, dominios, infraestructura aislada y administración SaaS.
                </p>
            </div>

            <!-- Botones de Acción Directa -->
            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                <a href="{{ url('/admin/tenants/create') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-900 bg-white hover:bg-slate-100 shadow-md transition">
                    <svg class="h-4 w-4 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Empresa
                </a>

                <a href="{{ url('/admin/tenants') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                    <svg class="h-4 w-4 mr-2 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Ver Empresas
                </a>
            </div>
        </div>

        <div class="absolute right-0 bottom-0 opacity-10 hidden lg:block pointer-events-none transform translate-x-8 translate-y-8">
            <svg class="h-64 w-64 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
    </div>

    <!-- Indicadores SaaS Centrales (Fila de 3 KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <!-- KPI 1: Empresas Activas -->
        <a href="{{ url('/admin/tenants') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-purple-400 hover:shadow-md transition group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Empresas Clientes</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $metrics['totalTenants'] }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>Bases de datos independientes</span>
                <span class="text-purple-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI 2: Subdominios Mapeados -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dominios Vinculados</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $metrics['totalDomains'] }}
            </div>
            <div class="text-xs text-slate-500 mt-1">
                Subdominios activos en la red
            </div>
        </div>

        <!-- KPI 3: Usuarios Registrados -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuarios Centrales</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $metrics['totalUsers'] }}
            </div>
            <div class="text-xs text-slate-500 mt-1">
                Operadores y administradores
            </div>
        </div>
    </div>
</div>
