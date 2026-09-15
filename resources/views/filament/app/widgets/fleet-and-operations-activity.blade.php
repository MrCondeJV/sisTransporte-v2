@php
    $recentOrders = $this->getRecentOrders();
    $fleet = $this->getFleetSummary();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <!-- Columna Izquierda: Tabla Operativa de Últimas Órdenes (8 cols) -->
    <div class="lg:col-span-8 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-bold text-slate-900 text-lg">Órdenes de Servicio Recientes</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/50">
                            Despachos en Tiempo Real
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Monitoreo de asignaciones, rutas y cumplimiento preoperacional.
                    </p>
                </div>

                <a href="{{ url('/app/service-orders') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                    Ver todas las órdenes &rarr;
                </a>
            </div>

            @if($recentOrders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-slate-100 table-auto text-left text-sm">
                    <thead class="bg-slate-50/70 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Orden / Fecha</th>
                            <th class="px-4 py-3.5">Cliente</th>
                            <th class="px-4 py-3.5">Vehículo & Conductor</th>
                            <th class="px-4 py-3.5 text-center">Estado</th>
                            <th class="px-5 py-3.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-sm font-mono">{{ $order->order_number }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($order->scheduled_date)->format('d/m/Y') }} {{ $order->scheduled_time ? substr($order->scheduled_time, 0, 5) : '' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-slate-800">
                                {{ $order->client?->business_name ?? 'Cliente General' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600">
                                <div class="font-semibold text-slate-900 flex items-center gap-1">
                                    <span class="bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded font-mono">{{ $order->vehicle?->plate ?? 'Sin Placa' }}</span>
                                </div>
                                <div class="text-slate-400 mt-0.5 truncate max-w-[180px]">
                                    {{ $order->driver?->full_name ?? 'Sin Asignar' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @php
                                    $badgeClasses = match($order->status) {
                                        'Pendiente' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'Asignada' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'En Progreso' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'Finalizada' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'Cancelada' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ url('/app/service-orders/' . $order->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-8 text-center text-slate-400">
                <svg class="h-10 w-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-sm font-medium">No hay órdenes de servicio recientes aún.</p>
                <a href="{{ url('/app/service-orders/create') }}" class="inline-block mt-3 text-xs font-bold text-indigo-600 hover:underline">
                    Crear primera orden de servicio &rarr;
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Columna Derecha: Resumen de Flota y Mantenimientos (4 cols) -->
    <div class="lg:col-span-4 space-y-6">
        <!-- Tarjeta de Estado de Parque Automotor -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Parque Automotor</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Control de flota y disponibilidad</p>
                </div>
                <span class="p-2 rounded-xl bg-slate-50 text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </span>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Operativos en Ruta
                    </span>
                    <span class="font-bold text-slate-900 text-sm font-mono">{{ $fleet['activos'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        En Mantenimiento
                    </span>
                    <span class="font-bold text-slate-900 text-sm font-mono">{{ $fleet['mantenimiento'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                        Contratos Vigentes
                    </span>
                    <span class="font-bold text-slate-900 text-sm font-mono">{{ $fleet['contratos'] }}</span>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <a href="{{ url('/app/maintenance/maintenances') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    Ver Mantenimientos Mecánicos &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
