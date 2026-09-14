<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'sisTransporte v2') }} - @yield('title', 'Panel de Transporte')</title>

    <!-- Google / Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Alpine.js & Tailwind CSS v4 via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $tenantActual = tenancy()->initialized ? tenant() : \App\Models\Tenant::first();
        $nombreEmpresa = $tenantActual?->name ?? 'Transportes Kemuel S.A.S.';
        $nitEmpresa = $tenantActual?->nit ?? '901234567-8';
        $inicialesEmpresa = mb_strtoupper(mb_substr($nombreEmpresa, 0, 2));
    @endphp

    <style>
        :root {
            --theme-primary: #2563eb;
            --theme-primary-hover: #1d4ed8;
            --theme-primary-focus: rgba(37, 99, 235, 0.25);
        }
        [x-cloak] { display: none !important; }
        html, body {
            height: 100%;
            overflow: hidden !important;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex overflow-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Sidebar para Escritorio (Pantallas medianas y grandes, idéntico a sys-POS) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 text-slate-300 flex-shrink-0 border-r border-slate-800 h-full overflow-hidden">
        <!-- Brand Header de Empresa -->
        <div class="h-16 flex items-center px-5 bg-slate-950 border-b border-slate-800">
            <div class="h-10 w-10 flex-shrink-0 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-sm tracking-wider shadow-md shadow-slate-950/40 mr-3">
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
        <nav class="flex-1 min-h-0 px-4 py-4 space-y-1 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent text-sm">
            <!-- Sección: Dashboard -->
            <a href="{{ url('/app') }}"
                class="flex items-center px-3.5 py-2.5 font-medium rounded-xl transition {{ request()->is('app') || request()->is('portal') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard Operativo
            </a>

            <!-- Sección: Operaciones & Rutas -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Operaciones & Despachos
            </div>

            <a href="{{ url('/app/service-orders') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Órdenes de Servicio
                </span>
                <span class="text-[10px] bg-amber-500/20 text-amber-300 font-bold px-1.5 py-0.5 rounded">ODS</span>
            </a>

            <a href="{{ url('/app/fuec-documents') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    FUEC Digital con QR
                </span>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 font-bold px-1.5 py-0.5 rounded">Oficial</span>
            </a>

            <a href="{{ url('/app/gps-tracking') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Monitoreo GPS en Vivo
                </span>
                <span class="text-[10px] bg-cyan-500/20 text-cyan-300 font-bold px-1.5 py-0.5 rounded flex items-center">
                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-pulse mr-1"></span> Live
                </span>
            </a>

            <!-- Sección: Flota & Mantenimiento -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Flota & Mantenimiento
            </div>

            <a href="{{ url('/app/vehicles') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Vehículos & Vencimientos
            </a>

            <a href="{{ url('/app/maintenances') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Mantenimiento Flota
            </a>

            <a href="{{ url('/app/fuel-refills') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Combustible & Rendimiento
            </a>

            <!-- Sección: Personal & Seguridad Vial -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Conductores & Seguridad
            </div>

            <a href="{{ url('/app/employees') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Conductores & Licencias
            </a>

            <a href="{{ url('/app/preoperational-checklists') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Checklist Preoperacional
            </a>

            <a href="{{ url('/app/service-incidents') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Novedades en Ruta
            </a>

            <!-- Sección: Portales Especiales (Diseño sys-POS) -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider px-3">
                Portales Especializados
            </div>

            <a href="{{ route('portal.conductor') }}"
                class="flex items-center justify-between px-3.5 py-2 font-medium rounded-xl transition text-indigo-300 hover:text-white hover:bg-slate-800/60">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Portal Conductor (PWA)
                </span>
                <span class="text-[10px] bg-indigo-500/20 text-indigo-300 font-bold px-1.5 py-0.5 rounded">Móvil</span>
            </a>

            <a href="{{ route('portal.cliente') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Portal de Clientes
            </a>

            <a href="{{ route('portal.aliado') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                <svg class="h-5 w-5 mr-3 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                </svg>
                Portal Aliados / Flota
            </a>

            <!-- Sección: Central / SuperAdmin -->
            <div class="pt-3 pb-1 text-[11px] font-semibold text-purple-400 uppercase tracking-wider px-3 flex items-center justify-between">
                <span>Plataforma SaaS</span>
                <span class="text-[9px] bg-purple-950 text-purple-300 px-1 py-0.2 rounded border border-purple-800">SuperAdmin</span>
            </div>

            <a href="{{ url('/admin/tenants') }}"
                class="flex items-center px-3.5 py-2 font-medium rounded-xl transition text-purple-300 hover:text-white hover:bg-purple-900/30">
                <svg class="h-5 w-5 mr-3 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Empresas Clientes (Tenants)
            </a>
        </nav>

        <!-- User Footer (Estilo sys-POS) -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="truncate mr-2">
                <div class="text-sm font-semibold text-white truncate">{{ auth()->user()?->name ?? 'Usuario de Transporte' }}</div>
                <div class="text-xs text-blue-400 truncate">{{ auth()->user()?->roles->first()?->name ?? 'Operador' }}</div>
            </div>
            <form action="{{ url('/app/logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition rounded-lg hover:bg-slate-800" title="Cerrar sesión">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Drawer Menu (Teléfonos y Tablets pequeñas) -->
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
                    <a href="{{ url('/app') }}" class="flex items-center px-4 py-2.5 rounded-xl text-white hover:bg-slate-800">
                        Dashboard Operativo
                    </a>
                    <a href="{{ url('/app/service-orders') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Órdenes de Servicio (ODS)
                    </a>
                    <a href="{{ url('/app/fuec-documents') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        FUEC Digital con QR
                    </a>
                    <a href="{{ url('/app/vehicles') }}" class="flex items-center px-4 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800">
                        Vehículos & Documentos
                    </a>
                    <a href="{{ route('portal.conductor') }}" class="flex items-center px-4 py-2.5 rounded-xl text-indigo-300 hover:bg-slate-800 font-semibold">
                        📱 Portal Conductor (Móvil)
                    </a>
                    <a href="{{ route('portal.cliente') }}" class="flex items-center px-4 py-2.5 rounded-xl text-emerald-300 hover:bg-slate-800">
                        👥 Portal Clientes
                    </a>
                </nav>

                <div class="p-4 border-t border-slate-800">
                    <form action="{{ url('/app/logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl bg-red-600/10 text-red-400 hover:bg-red-600 hover:text-white font-semibold text-sm transition">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Área de Contenido Principal -->
    <div class="flex-1 flex flex-col min-w-0 min-h-0 h-full overflow-hidden">
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
                        <span class="text-sm font-bold text-slate-900 block truncate max-w-[200px] sm:max-w-[320px]">
                            {{ $nombreEmpresa }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono hidden sm:block">
                            NIT: {{ $nitEmpresa }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Tenant Selector (SuperAdmin), Acciones Rápidas & Perfil -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Enlace rápido a Filament App -->
                <a href="{{ url('/app') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition" title="Ir al panel Filament">
                    <svg class="h-4 w-4 mr-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Panel Filament
                </a>

                <!-- Campana de Notificaciones con Ping Animado -->
                <div class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer" title="Novedades y Vencimientos">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                    </span>
                </div>

                <!-- User Pill -->
                <div class="flex items-center pl-2 border-l border-slate-200 space-x-2">
                    <div class="h-8 w-8 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                        {{ substr(auth()->user()?->name ?? 'AD', 0, 2) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()?->name ?? 'Administrador' }}</div>
                        <div class="text-[10px] text-slate-500 leading-tight">{{ auth()->user()?->roles->first()?->name ?? 'SuperAdmin' }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenedor de Contenido Principal -->
        <main class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-[1680px] mx-auto space-y-6">
                <!-- Alertas Flash con auto-cierre en 5 segundos y animación fade (Estilo sys-POS) -->
                @if(session('success'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 6000)"
                     x-show="show"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center justify-between p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')
</body>
</html>
