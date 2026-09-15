<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'sisTransporte') }} - @yield('title', 'Inicio')</title>

    <!-- Google / Bunny Fonts (Instrument Sans + Figtree) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite: Tailwind CSS v4 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js & Chart.js Global -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    @php
        $tenantActual = tenancy()->initialized ? tenant() : (\App\Models\Tenant::find(session('active_tenant_id')) ?? \App\Models\Tenant::first());
        $nombreEmpresa = $tenantActual?->name ?? 'Transportes Kemuel S.A.S.';
        $nitEmpresa = $tenantActual?->nit ?? '901234567-8';
        $inicialesEmpresa = mb_strtoupper(mb_substr($nombreEmpresa, 0, 2));
        $todosTenants = \App\Models\Tenant::all();
    @endphp

    <style>
        [x-cloak] { display: none !important; }

        /* ==========================================================================
           Estilos Modernos para Dropdowns / Select Boxes (Idéntico a sys-POS)
           ========================================================================== */
        select,
        select::picker(select) {
            appearance: base-select;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.15em 1.15em !important;
            padding-right: 2.5rem !important;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            color: #1e293b;
            font-size: 0.8125rem;
            line-height: 1.25rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            cursor: pointer;
        }

        select:hover {
            border-color: #94a3b8;
        }

        select:focus {
            outline: none;
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
            background-color: #ffffff !important;
        }

        select::picker(select) {
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
            padding: 0.375rem;
            background-color: #ffffff;
            color: #1e293b;
            font-family: inherit;
        }

        select option {
            padding: 0.55rem 0.85rem;
            font-size: 0.8125rem;
            color: #334155;
            background-color: #ffffff;
            border-radius: 0.5rem;
            margin: 2px 0;
            cursor: pointer;
        }

        select option:checked,
        select option:hover {
            background-color: #eff6ff !important;
            color: #2563eb !important;
            font-weight: 600;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex overflow-hidden" x-data="{ mobileMenuOpen: false }">

    @auth
    <!-- Sidebar para Escritorio (Idéntico a sys-POS) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 text-slate-300 flex-shrink-0 border-r border-slate-800 h-full overflow-hidden">
        <!-- Brand Header de Empresa -->
        <div class="h-16 flex items-center px-5 bg-slate-950 border-b border-slate-800">
            <div class="h-9 w-9 flex-shrink-0 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-sm tracking-wider shadow-md shadow-blue-500/20 mr-3">
                {{ $inicialesEmpresa }}
            </div>
            <div class="truncate">
                <span class="font-bold text-white tracking-wide block truncate text-sm" title="{{ $nombreEmpresa }}">
                    {{ $nombreEmpresa }}
                </span>
                <span class="text-xs text-slate-400 block truncate font-mono">
                    NIT: {{ $nitEmpresa }}
                </span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto scrollbar-thin text-sm">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-3.5 py-2.5 font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <!-- Operaciones & Rutas -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Operaciones & Despachos
            </div>

            <a href="{{ route('ordenes.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('ordenes.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Órdenes de Servicio
                </span>
                <span class="text-[10px] bg-amber-500/20 text-amber-300 font-bold px-1.5 py-0.5 rounded">ODS</span>
            </a>

            <a href="{{ route('rutas.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('rutas.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Rutas Frecuentes
                </span>
            </a>

            <a href="{{ route('fuec.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('fuec.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    FUEC Digital con QR
                </span>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 font-bold px-1.5 py-0.5 rounded">Oficial</span>
            </a>

            <a href="{{ route('monitoreo.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('monitoreo.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Monitoreo GPS / Wallboard
                </span>
                <span class="text-[10px] bg-cyan-500/20 text-cyan-300 font-bold px-1.5 py-0.5 rounded flex items-center">
                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-pulse mr-1"></span> Live
                </span>
            </a>

            <!-- Parque Automotor & Flota -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Parque Automotor & Flota
            </div>

            <a href="{{ route('vehiculos.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('vehiculos.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Vehículos & Catálogo
            </a>

            <a href="{{ route('aliados.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('aliados.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Aliados & Convenios
            </a>

            <a href="{{ route('mantenimientos.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('mantenimientos.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Mantenimiento Flota
            </a>

            <a href="{{ route('combustible.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('combustible.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Combustible & Rendimiento
            </a>

            <!-- Personal & Seguridad Vial -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Personal & Seguridad Vial
            </div>

            <a href="{{ route('conductores.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('conductores.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Conductores & Licencias
            </a>

            <a href="{{ route('checklists.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('checklists.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Inspección Preoperacional
                </span>
                <span class="text-[10px] bg-teal-500/20 text-teal-300 font-bold px-1.5 py-0.5 rounded">PESV</span>
            </a>

            <!-- Auditoría & Control Gerencial (Paridad V1) -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Auditoría & Gerencia
            </div>

            <a href="{{ route('gerencial.reportes.dashboard') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('gerencial.reportes.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Panel Gerencial & KPIs
                </span>
                <span class="text-[10px] bg-indigo-500/20 text-indigo-300 font-bold px-1.5 py-0.5 rounded">V1 Plus</span>
            </a>

            <a href="{{ route('gerencial.aprobaciones.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('gerencial.aprobaciones.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Aprobaciones Gerenciales
                </span>
            </a>

            <a href="{{ route('reportes.index') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('reportes.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Liquidación Operativa
                </span>
            </a>

            <!-- Comercial & Clientes -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Comercial & Contratos
            </div>

            <a href="{{ route('clientes.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('clientes.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Clientes Corporativos
            </a>

            <a href="{{ route('contratos.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('contratos.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Contratos de Transporte
            </a>

            <!-- Seguridad & Acceso -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Seguridad & Acceso
            </div>

            <a href="{{ route('usuarios.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('usuarios.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Usuarios del Sistema
            </a>

            <a href="{{ route('roles.index') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition {{ request()->routeIs('roles.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Roles & Permisos
            </a>

            <!-- Portales Especializados (Móvil) -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Portales Especializados
            </div>

            <a href="{{ route('portal.conductor') }}" target="_blank"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition text-indigo-300 hover:text-white hover:bg-slate-800/60">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Portal Conductor (PWA)
                </span>
                <span class="text-[10px] bg-indigo-500/20 text-indigo-300 font-bold px-1.5 py-0.5 rounded">Móvil</span>
            </a>

            <a href="{{ route('portal.cliente') }}" target="_blank"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Portal Clientes
            </a>

            <a href="{{ route('portal.aliado') }}" target="_blank"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                </svg>
                Portal Aliados / Flota
            </a>
        </nav>

        <!-- User Footer (Estilo sys-POS) -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="truncate mr-2">
                <div class="text-sm font-semibold text-white truncate">{{ auth()->user()?->name ?? 'Usuario de Transporte' }}</div>
                <div class="text-xs text-blue-400 truncate">{{ auth()->user()?->roles->first()?->name ?? 'Operador' }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition rounded-lg hover:bg-slate-800" title="Cerrar sesión">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Drawer Menu (Teléfonos y Tablets) -->
    <div x-cloak x-show="mobileMenuOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"
             @click="mobileMenuOpen = false"></div>

        <div class="fixed inset-0 flex">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-250 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-250 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-slate-900 pt-5 pb-4">
                <div class="flex items-center justify-between px-6 pb-4 border-b border-slate-800">
                    <div class="flex items-center space-x-3 truncate mr-2">
                        <div class="h-9 w-9 flex-shrink-0 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xs">
                            {{ $inicialesEmpresa }}
                        </div>
                        <span class="font-bold text-white text-sm truncate">{{ $nombreEmpresa }}</span>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-white" @click="mobileMenuOpen = false">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="mt-4 px-4 space-y-1 overflow-y-auto flex-1 text-sm">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 rounded-xl text-white hover:bg-slate-800">
                        Dashboard
                    </a>
                    <a href="{{ route('ordenes.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Órdenes de Servicio
                    </a>
                    <a href="{{ route('rutas.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Rutas Frecuentes
                    </a>
                    <a href="{{ route('fuec.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        FUEC Digital con QR
                    </a>
                    <a href="{{ route('vehiculos.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Vehículos & Catálogo
                    </a>
                    <a href="{{ route('aliados.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Aliados & Convenios
                    </a>
                    <a href="{{ route('checklists.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Inspección Preoperacional
                    </a>
                    <a href="{{ route('gerencial.aprobaciones.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Aprobaciones Gerenciales
                    </a>
                    <a href="{{ route('reportes.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Reportes & Liquidación
                    </a>
                    <a href="{{ route('monitoreo.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Monitoreo GPS
                    </a>
                    <a href="{{ route('usuarios.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        👤 Usuarios del Sistema
                    </a>
                    <a href="{{ route('roles.index') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        🛡️ Roles & Permisos
                    </a>
                    <a href="{{ route('portal.conductor') }}" target="_blank" class="flex items-center px-4 py-2.5 rounded-xl text-indigo-300 hover:bg-slate-800 font-semibold">
                        📱 Portal Conductor (Móvil)
                    </a>
                </nav>

                <div class="p-4 border-t border-slate-800">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl bg-red-600/10 text-red-400 hover:bg-red-600 hover:text-white font-semibold text-sm transition">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Área de Contenido Principal -->
    <div class="flex-1 flex flex-col min-w-0 min-h-0 h-full overflow-hidden">
        @auth
        <!-- Header Superior Universal (Estilo sys-POS) -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 flex-shrink-0">
            <!-- Left: Mobile Menu Button & Brand Header Indicator -->
            <div class="flex items-center space-x-3">
                <button type="button" @click="mobileMenuOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex items-center space-x-3">
                    <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-700 font-black text-xs flex items-center justify-center border border-blue-200 shadow-xs">
                        {{ $inicialesEmpresa }}
                    </div>
                    <div class="leading-tight">
                        <span class="text-sm font-bold text-slate-900 block truncate max-w-[180px] sm:max-w-[320px]">
                            {{ $nombreEmpresa }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono hidden sm:block">
                            NIT: {{ $nitEmpresa }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Tenant Selector, Acciones Rápidas & Perfil -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Selector de Empresa / Tenant Activa (Estilo sys-POS) -->
                @if($todosTenants->count() > 1)
                <div class="relative" x-data="{ openTenant: false }">
                    <button @click="openTenant = !openTenant" type="button"
                        class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-xl bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200 transition"
                        title="Alternar empresa">
                        <svg class="h-4 w-4 mr-1.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="truncate max-w-[120px] sm:max-w-[180px]">
                            {{ $nombreEmpresa }}
                        </span>
                        <svg class="h-3.5 w-3.5 ml-1 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-cloak x-show="openTenant" @click.away="openTenant = false"
                        class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 p-2 z-50 border border-slate-100">
                        <div class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Empresas de Transporte
                        </div>
                        <div class="mt-1 space-y-1 max-h-60 overflow-y-auto">
                            @foreach($todosTenants as $tItem)
                            <form action="{{ route('tenants.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tenant_id" value="{{ $tItem->id }}">
                                <button type="submit"
                                    class="w-full text-left px-3 py-2 text-xs rounded-xl flex items-center justify-between transition {{ ($tenantActual?->id === $tItem->id) ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="truncate">{{ $tItem->name }}</span>
                                    <span class="text-[10px] text-slate-400 ml-1">{{ $tItem->nit }}</span>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- User Pill -->
                <div class="flex items-center pl-2 border-l border-slate-200 space-x-2">
                    <div class="h-8 w-8 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                        {{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'OP', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left leading-none">
                        <div class="text-xs font-bold text-slate-800">{{ auth()->user()?->name ?? 'Usuario' }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">{{ auth()->user()?->roles->first()?->name ?? 'Operaciones' }}</div>
                    </div>
                </div>
            </div>
        </header>
        @endauth

        <!-- Contenedor con Scroll Independiente -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
            @if(session('success'))
            <div class="max-w-[1680px] mx-auto mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-3">
                    <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="max-w-[1680px] mx-auto mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-3">
                    <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
