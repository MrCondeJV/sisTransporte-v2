@extends('layouts.app')

@section('title', 'Panel de Control Gerencial')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Auditoría & Gerencia</span>
                <span>/</span>
                <span class="text-slate-800">Panel Ejecutivo</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center">
                    <span class="p-2 bg-blue-600 text-white rounded-xl mr-3 shadow-md shadow-blue-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </span>
                    Resumen General & KPIs Estratégicos
                </h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Control integral de ingresos, flota, combustible, cartera y trazabilidad operativa de transporte.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Botón Calendario -->
            <button type="button" id="btnToggleCalendario" onclick="toggleCalendario()"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-xs transition">
                <svg class="h-4 w-4 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span id="txtBtnCalendario">Mostrar Calendario</span>
            </button>

            <!-- Exportar Excel Consolidado -->
            <button type="button" onclick="exportarTodosLosGraficosAExcel()"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Gráficos a Excel
            </button>
        </div>
    </div>

    <!-- Banner Informativo -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/70 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-2">
        <div class="flex items-center space-x-3 text-sm text-blue-900">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-600"></span>
            </span>
            <span><strong>Panel de Control Gerencial</strong> &mdash; Visualización analítica sincronizada en tiempo real.</span>
        </div>
        <span class="text-xs font-mono font-bold bg-white text-blue-700 border border-blue-200 px-3 py-1 rounded-full shadow-2xs">
            Actualizado: {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>

    <!-- 6 Tarjetas Rápidas de Resumen (KPIs Superiores con Estilo Sólido V1 + SysPOS) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Empleados (Azul) -->
        <a href="{{ route('conductores.index') }}"
           class="group bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between min-h-[125px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-100">Empleados</span>
                <span class="p-1.5 bg-white/10 rounded-lg text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </span>
            </div>
            <div class="my-1">
                <div class="text-3xl font-black tracking-tight text-white">{{ number_format($contadorEmpleados) }}</div>
            </div>
            <div class="text-[10px] font-medium text-blue-100 flex items-center justify-between pt-1 border-t border-white/15">
                <span>Conductores & Nómina</span>
                <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
            </div>
        </a>

        <!-- Vehículos Activos (Verde) -->
        <a href="{{ route('vehiculos.index') }}"
           class="group bg-gradient-to-br from-emerald-600 to-emerald-700 text-white rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between min-h-[125px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-100">Vehículos</span>
                <span class="p-1.5 bg-white/10 rounded-lg text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </span>
            </div>
            <div class="my-1">
                <div class="text-3xl font-black tracking-tight text-white">{{ number_format($contadorVehiculos) }}</div>
            </div>
            <div class="text-[10px] font-medium text-emerald-100 flex items-center justify-between pt-1 border-t border-white/15">
                <span>Flota Activa</span>
                <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
            </div>
        </a>

        <!-- Viajes Realizados (Cyan / Sky) -->
        <a href="{{ route('gerencial.reportes.ordenes-pagadas') }}"
           class="group bg-gradient-to-br from-cyan-600 to-blue-600 text-white rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between min-h-[125px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-100">Viajes Realizados</span>
                <span class="p-1.5 bg-white/10 rounded-lg text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <div class="my-1">
                <div class="text-3xl font-black tracking-tight text-white">{{ number_format($contadorCompletadas) }}</div>
            </div>
            <div class="text-[10px] font-medium text-cyan-100 flex items-center justify-between pt-1 border-t border-white/15">
                <span>Completadas</span>
                <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
            </div>
        </a>

        <!-- Pendientes (Rojo / Rose) -->
        <a href="{{ route('ordenes.index') }}"
           class="group bg-gradient-to-br from-rose-600 to-red-700 text-white rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between min-h-[125px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-100">Pendientes</span>
                <span class="p-1.5 bg-white/10 rounded-lg text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <div class="my-1">
                <div class="text-3xl font-black tracking-tight text-white">{{ number_format($contadorPendientes) }}</div>
            </div>
            <div class="text-[10px] font-medium text-rose-100 flex items-center justify-between pt-1 border-t border-white/15">
                <span>Por Despachar</span>
                <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
            </div>
        </a>

        <!-- Mantenimientos (Ámbar / Naranja) -->
        <a href="{{ route('mantenimientos.index') }}"
           class="group bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between min-h-[125px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-100">Mantenimientos</span>
                <span class="p-1.5 bg-white/10 rounded-lg text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                </span>
            </div>
            <div class="my-1">
                <div class="text-3xl font-black tracking-tight text-white">{{ number_format($contadorMantenimientos) }}</div>
            </div>
            <div class="text-[10px] font-medium text-amber-100 flex items-center justify-between pt-1 border-t border-white/15">
                <span>Taller & Preventivos</span>
                <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
            </div>
        </a>

        <!-- Clientes (Púrpura / Índigo) -->
        <a href="{{ route('clientes.index') }}"
           class="group bg-gradient-to-br from-purple-600 to-indigo-700 text-white rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between min-h-[125px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-100">Clientes</span>
                <span class="p-1.5 bg-white/10 rounded-lg text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </span>
            </div>
            <div class="my-1">
                <div class="text-3xl font-black tracking-tight text-white">{{ number_format($contadorClientes) }}</div>
            </div>
            <div class="text-[10px] font-medium text-purple-100 flex items-center justify-between pt-1 border-t border-white/15">
                <span>Empresas & Contratos</span>
                <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
            </div>
        </a>
    </div>

    <!-- Contenedor Desplegable de FullCalendar -->
    <div id="calendarioCard" style="display: none;" class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center">
                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Calendario de Despachos y Servicios
            </h2>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500 mr-1.5"></span> Completada</span>
                <span class="flex items-center"><span class="h-2.5 w-2.5 rounded-full bg-sky-500 mr-1.5"></span> En Progreso</span>
                <span class="flex items-center"><span class="h-2.5 w-2.5 rounded-full bg-amber-500 mr-1.5"></span> Pendiente</span>
                <span class="flex items-center"><span class="h-2.5 w-2.5 rounded-full bg-rose-500 mr-1.5"></span> Cancelada</span>
            </div>
        </div>
        <div id="fullCalendarContainer" style="min-height: 480px;"></div>
    </div>

    <!-- Fila 1: Valores por Servicio (Hoy, Mes, Año) + Combustible Semanal -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Tarjetas de Ingresos + Gráfica Semanal -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 mb-4 gap-2">
                    <div>
                        <h2 class="font-bold text-slate-900 text-base">Valores por Servicio Diario, Mensual y Anual</h2>
                        <p class="text-xs text-slate-500">Ingresos recaudados en órdenes de transporte completadas</p>
                    </div>

                    <!-- Selector Año Actual vs Año Pasado -->
                    <div class="flex items-center space-x-2">
                        <label for="selector_anio" class="text-xs font-semibold text-slate-500">Periodo:</label>
                        <select id="selector_anio" onchange="cambiarPeriodoIngresos(this.value)"
                            class="text-xs font-bold text-slate-800 bg-slate-100 border border-slate-300 rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="actual">📅 Año Actual ({{ $thisYear }})</option>
                            <option value="anterior">🕒 Año Pasado ({{ $lastYear }})</option>
                        </select>
                    </div>
                </div>

                <!-- 3 Tarjetas AÑO ACTUAL (Visibles por defecto) -->
                <div id="datos_actuales" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <!-- Hoy -->
                    <div class="bg-gradient-to-br from-rose-500 to-rose-700 text-white rounded-2xl p-4 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-rose-100">Hoy</div>
                        <div class="text-2xl font-black mt-1 mb-0.5 tracking-tight">${{ number_format($sumaDiaria, 0) }}</div>
                        <div class="text-xs text-rose-100 opacity-90">{{ $conteoDiario }} órdenes finalizadas</div>
                    </div>

                    <!-- Este Mes -->
                    <div class="bg-gradient-to-br from-teal-500 to-emerald-700 text-white rounded-2xl p-4 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-teal-100">Este Mes</div>
                        <div class="text-2xl font-black mt-1 mb-0.5 tracking-tight">${{ number_format($sumaMensual, 0) }}</div>
                        <div class="text-xs text-teal-100 opacity-90">{{ $conteoMensual }} órdenes finalizadas</div>
                    </div>

                    <!-- Este Año -->
                    <div class="bg-gradient-to-br from-indigo-600 to-blue-800 text-white rounded-2xl p-4 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-100">Este Año</div>
                        <div class="text-2xl font-black mt-1 mb-0.5 tracking-tight">${{ number_format($sumaAnual, 0) }}</div>
                        <div class="text-xs text-indigo-100 opacity-90">{{ $conteoAnual }} órdenes finalizadas</div>
                    </div>
                </div>

                <!-- 3 Tarjetas AÑO PASADO (Ocultas por defecto) -->
                <div id="datos_anterior" style="display: none;" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <!-- Hoy Año Pasado -->
                    <div class="bg-gradient-to-br from-slate-600 to-slate-800 text-white rounded-2xl p-4 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-200">Hoy (Año Pasado)</div>
                        <div class="text-2xl font-black mt-1 mb-0.5 tracking-tight">${{ number_format($sumaDiariaAnterior, 0) }}</div>
                        <div class="text-xs text-slate-200 opacity-90">{{ $conteoDiarioAnterior }} órdenes finalizadas</div>
                    </div>

                    <!-- Mes Año Pasado -->
                    <div class="bg-gradient-to-br from-olive-600 to-emerald-800 text-white rounded-2xl p-4 shadow-xs" style="background-color: #3b5a38;">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-100">Este Mes (Año Pasado)</div>
                        <div class="text-2xl font-black mt-1 mb-0.5 tracking-tight">${{ number_format($sumaMensualAnterior, 0) }}</div>
                        <div class="text-xs text-emerald-100 opacity-90">{{ $conteoMensualAnterior }} órdenes finalizadas</div>
                    </div>

                    <!-- Año Pasado Total -->
                    <div class="bg-gradient-to-br from-blue-900 to-slate-900 text-white rounded-2xl p-4 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Año Pasado ({{ $lastYear }})</div>
                        <div class="text-2xl font-black mt-1 mb-0.5 tracking-tight">${{ number_format($sumaAnualAnterior, 0) }}</div>
                        <div class="text-xs text-blue-200 opacity-90">{{ $conteoAnualAnterior }} órdenes finalizadas</div>
                    </div>
                </div>

                <!-- Gráfico de Barras Semanal -->
                <div class="relative w-full" style="height: 280px;">
                    <canvas id="barChartSemanal"></canvas>
                </div>
            </div>
        </div>

        <!-- Kilometraje vs Combustible Semanal -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-slate-900 text-base">Kilometraje vs Gasolina Semanal</h2>
                        <p class="text-xs text-slate-500">Consumo diario de combustible frente a distancia recorrida</p>
                    </div>
                    <span class="text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 px-2 py-1 rounded-lg">Rendimiento</span>
                </div>

                <div class="relative w-full" style="height: 340px;">
                    <canvas id="chartCombustibleSemana"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila 2: Órdenes Sin Pagar vs Órdenes Pagadas (Paridad barChart1 & barChart2) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Sin Pagar -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-rose-600 text-base">Órdenes Completadas Sin Pagar</h2>
                        <p class="text-xs text-slate-500">Cartera pendiente por recaudar de clientes</p>
                    </div>
                    <a href="{{ route('gerencial.reportes.ordenes-sin-pagar') }}"
                        class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 px-3 py-1.5 rounded-xl border border-rose-100 transition">
                        Ver Cartera Detallada &rarr;
                    </a>
                </div>
                <div class="relative w-full" style="height: 260px;">
                    <canvas id="barChartSinPagar"></canvas>
                </div>
            </div>
        </div>

        <!-- Pagadas -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-emerald-600 text-base">Órdenes Completadas Pagadas</h2>
                        <p class="text-xs text-slate-500">Ingresos liquidados y recaudados efectivamente</p>
                    </div>
                    <a href="{{ route('gerencial.reportes.ordenes-pagadas') }}"
                        class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 transition">
                        Ver Detalle de Pagos &rarr;
                    </a>
                </div>
                <div class="relative w-full" style="height: 260px;">
                    <canvas id="barChartPagadas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila 3: Facturas Emitidas vs Pendientes de Facturación -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Facturadas -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-blue-600 text-base">Órdenes Facturadas por Mes</h2>
                        <p class="text-xs text-slate-500">Servicios con número de factura fiscal registrado</p>
                    </div>
                    <a href="{{ route('gerencial.reportes.ordenes-facturadas') }}"
                        class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100 transition">
                        Ver Facturadas &rarr;
                    </a>
                </div>
                <div class="relative w-full" style="height: 260px;">
                    <canvas id="facturasMesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- No Facturadas -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-amber-600 text-base">Órdenes Pendientes de Facturar</h2>
                        <p class="text-xs text-slate-500">Servicios finalizados sin comprobante tributario</p>
                    </div>
                    <a href="{{ route('gerencial.reportes.ordenes-no-facturadas') }}"
                        class="text-xs font-bold text-amber-600 hover:text-amber-700 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-100 transition">
                        Ver No Facturadas &rarr;
                    </a>
                </div>
                <div class="relative w-full" style="height: 260px;">
                    <canvas id="noFacturadasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila 4: Órdenes por Estado + Rutas Más Usadas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Órdenes por Estado -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Órdenes por Estado en el Año</h2>
                    <p class="text-xs text-slate-500">Distribución mensual de despachos según ciclo operativo</p>
                </div>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">{{ $thisYear }}</span>
            </div>
            <div class="relative w-full" style="height: 300px;">
                <canvas id="lineChartEstados"></canvas>
            </div>
        </div>

        <!-- Rutas Más Usadas -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-slate-900 text-base">Rutas Más Usadas</h2>
                        <p class="text-xs text-slate-500">Top de itinerarios más transitados</p>
                    </div>
                </div>
                <div class="relative w-full flex items-center justify-center" style="height: 300px;">
                    <canvas id="donutChartRutas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librerías de Apoyo: FullCalendar 6 y SheetJS XLSX -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
// Variables Globales de Estado y Gráficos
let globalSemanalData = null;
let globalSemanalChart = null;
let globalCalendarInstance = null;
let isCalendarVisible = false;

// Alternar Calendario
function toggleCalendario() {
    const container = document.getElementById('calendarioCard');
    const btnText = document.getElementById('txtBtnCalendario');

    if (!container) return;

    if (container.style.display === 'none') {
        container.style.display = 'block';
        btnText.innerText = 'Ocultar Calendario';
        if (!globalCalendarInstance) {
            initFullCalendar();
        }
    } else {
        container.style.display = 'none';
        btnText.innerText = 'Mostrar Calendario';
    }
}

function initFullCalendar() {
    const el = document.getElementById('fullCalendarContainer');
    if (!el || typeof FullCalendar === 'undefined') return;

    globalCalendarInstance = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: '{{ route("gerencial.reportes.chart-calendario") }}',
        eventClick: function(info) {
            alert(info.event.title + "\n\n" + (info.event.extendedProps.description || ''));
        }
    });
    globalCalendarInstance.render();
}

// Selector Dinámico Año Actual vs Año Pasado
function cambiarPeriodoIngresos(modo) {
    const divActual = document.getElementById('datos_actuales');
    const divAnterior = document.getElementById('datos_anterior');

    if (modo === 'actual') {
        if (divActual) divActual.style.display = 'grid';
        if (divAnterior) divAnterior.style.display = 'none';
    } else {
        if (divActual) divActual.style.display = 'none';
        if (divAnterior) divAnterior.style.display = 'grid';
    }

    if (globalSemanalData && globalSemanalChart) {
        const dataset = modo === 'actual' ? globalSemanalData.actual : globalSemanalData.anterior;
        globalSemanalChart.data.datasets[0].data = dataset.valores;
        globalSemanalChart.options.plugins.title.text = dataset.semana + ' (' + dataset.rango + ')';
        globalSemanalChart.update();
    }
}

// Inicializador Maestro de Gráficos
function inicializarGraficosGerenciales() {
    if (typeof Chart === 'undefined') {
        setTimeout(inicializarGraficosGerenciales, 200);
        return;
    }

    // 1. Gráfico Semanal (Ingresos)
    fetch('{{ route("gerencial.reportes.chart-semanal") }}')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            globalSemanalData = res;
            const ctx = document.getElementById('barChartSemanal')?.getContext('2d');
            if (!ctx) return;

            const cur = res.actual;
            globalSemanalChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: res.dias,
                    datasets: [{
                        label: 'Ingresos Diarios ($)',
                        data: cur.valores,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(14, 165, 233, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ],
                        borderRadius: 8,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: cur.semana + ' (' + cur.rango + ')',
                            font: { weight: 'bold', size: 13 }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(ctx) {
                                    const modo = document.getElementById('selector_anio')?.value || 'actual';
                                    const item = (modo === 'actual' ? res.actual : res.anterior).detalles[ctx.dataIndex];
                                    return 'Servicios ejecutados: ' + (item?.cantidad || 0);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => '$' + Number(v).toLocaleString()
                            }
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Error cargando gráfico semanal:', err));

    // 2. Kilometraje vs Combustible
    fetch('{{ route("gerencial.reportes.chart-combustible") }}')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const ctx = document.getElementById('chartCombustibleSemana')?.getContext('2d');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: res.dias,
                    datasets: [
                        {
                            label: 'Km Recorridos',
                            data: res.km,
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderRadius: 6,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Galones Combustible',
                            data: res.combustible,
                            backgroundColor: 'rgba(239, 68, 68, 0.85)',
                            borderRadius: 6,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Semana Actual (' + res.rango + ')',
                            font: { weight: 'bold', size: 13 }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Kilómetros' },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Galones' },
                            grid: { drawOnChartArea: false },
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Error cargando combustible:', err));

    // 3. Pagos (Sin Pagar vs Pagadas)
    fetch('{{ route("gerencial.reportes.chart-pagos") }}')
        .then(r => r.json())
        .then(res => {
            // Sin Pagar
            const ctx1 = document.getElementById('barChartSinPagar')?.getContext('2d');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Cartera Sin Pagar ($)',
                            data: res.no_pagadas,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => '$' + Number(v).toLocaleString() }
                            }
                        }
                    }
                });
            }

            // Pagadas
            const ctx2 = document.getElementById('barChartPagadas')?.getContext('2d');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Órdenes Pagadas ($)',
                            data: res.pagadas,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => '$' + Number(v).toLocaleString() }
                            }
                        }
                    }
                });
            }
        })
        .catch(err => console.error('Error cargando pagos:', err));

    // 4. Facturación
    fetch('{{ route("gerencial.reportes.chart-facturas") }}')
        .then(r => r.json())
        .then(res => {
            // Facturadas
            const ctxF = document.getElementById('facturasMesChart')?.getContext('2d');
            if (ctxF) {
                new Chart(ctxF, {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Órdenes Facturadas',
                            data: res.facturadas,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            // No Facturadas
            const ctxNF = document.getElementById('noFacturadasChart')?.getContext('2d');
            if (ctxNF) {
                new Chart(ctxNF, {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Pendientes por Facturar',
                            data: res.no_facturadas,
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        })
        .catch(err => console.error('Error cargando facturas:', err));

    // 5. Estados por Mes
    fetch('{{ route("gerencial.reportes.chart-estados") }}')
        .then(r => r.json())
        .then(res => {
            const ctx = document.getElementById('lineChartEstados')?.getContext('2d');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: res.labels,
                    datasets: [
                        { label: 'Pendiente', data: res.pendiente, backgroundColor: 'rgba(245, 158, 11, 0.85)', borderRadius: 4 },
                        { label: 'En Progreso', data: res.en_progreso, backgroundColor: 'rgba(14, 165, 233, 0.85)', borderRadius: 4 },
                        { label: 'Completado', data: res.completado, backgroundColor: 'rgba(16, 185, 129, 0.85)', borderRadius: 4 },
                        { label: 'Cancelado', data: res.cancelado, backgroundColor: 'rgba(239, 68, 68, 0.85)', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    }
                }
            });
        })
        .catch(err => console.error('Error cargando estados:', err));

    // 6. Rutas Más Usadas
    fetch('{{ route("gerencial.reportes.chart-rutas") }}')
        .then(r => r.json())
        .then(res => {
            const ctx = document.getElementById('donutChartRutas')?.getContext('2d');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: res.labels,
                    datasets: [{
                        data: res.data,
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        })
        .catch(err => console.error('Error cargando rutas:', err));
}

// Exportar Todo a Excel
function exportarTodosLosGraficosAExcel() {
    if (typeof XLSX === 'undefined') {
        alert('La librería SheetJS está terminando de cargar. Por favor intente en unos instantes.');
        return;
    }

    const wb = XLSX.utils.book_new();
    const charts = Chart.instances;

    const nombresHojas = {
        barChartSemanal: "Ingresos Semanales",
        chartCombustibleSemana: "Combustible y Km",
        barChartSinPagar: "Cartera Sin Pagar",
        barChartPagadas: "Órdenes Pagadas",
        facturasMesChart: "Órdenes Facturadas",
        noFacturadasChart: "No Facturadas",
        lineChartEstados: "Órdenes por Estado",
        donutChartRutas: "Rutas Más Usadas"
    };

    Object.values(charts).forEach(chart => {
        const id = chart.canvas.id;
        const sheetName = nombresHojas[id] || id;
        const labels = chart.data.labels;
        const datasets = chart.data.datasets;

        const header = ["Categoría", ...datasets.map(ds => ds.label)];
        const rows = [header];

        labels.forEach((label, i) => {
            const row = [label];
            datasets.forEach(ds => {
                row.push(ds.data[i] ?? "");
            });
            rows.push(row);
        });

        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, sheetName.substring(0, 31));
    });

    XLSX.writeFile(wb, "ReporteGerencial_Consolidado_" + new Date().toISOString().slice(0,10) + ".xlsx");
}

// Ejecución al cargar el DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarGraficosGerenciales);
} else {
    inicializarGraficosGerenciales();
}
</script>
@endsection
