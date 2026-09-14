@extends('layouts.transport')

@section('title', 'Portal de Aliados y Propietarios')

@section('content')
<div class="space-y-6">
    <!-- Header del Portal Aliado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <span class="p-2 bg-sky-50 text-sky-600 rounded-xl mr-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                    </svg>
                </span>
                Portal de Aliados y Propietarios Vinculados
            </h1>
            <p class="text-sm text-slate-500 mt-1">Supervisión de flota propia o afiliada, convenios de colaboración y liquidación de servicios prestados.</p>
        </div>
    </div>

    <!-- Lista de Vehículos Vinculados -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Vehículos Vinculados a su Cuenta</h2>
            <span class="text-xs text-slate-400">Total: {{ $vehiculosAliados->count() }} vehículos</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
            @forelse($vehiculosAliados as $veh)
            <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 space-y-3 transition">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-sm font-mono font-bold bg-white text-slate-900 border border-slate-200 shadow-xs">
                        🚗 {{ $veh->plate }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $veh->status === 'Activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $veh->status === 'Activo' ? 'Operativo' : 'Inhabilitado' }}
                    </span>
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div><strong>Marca/Modelo:</strong> {{ $veh->brand }} {{ $veh->model }} ({{ $veh->year ?? 2024 }})</div>
                    <div><strong>Capacidad:</strong> {{ $veh->passenger_capacity ?? 19 }} Pasajeros</div>
                    <div><strong>Odómetro:</strong> <span class="font-mono font-bold text-slate-800">{{ number_format($veh->current_mileage, 0, ',', '.') }} Km</span></div>
                </div>

                <!-- Semáforo de Documentación Rápido -->
                <div class="pt-2 border-t border-slate-200/80 text-[11px] grid grid-cols-2 gap-1 text-slate-500">
                    <div>SOAT: <span class="font-semibold text-emerald-700">Vigente</span></div>
                    <div>RTM: <span class="font-semibold text-emerald-700">Vigente</span></div>
                    <div>Póliza RCC: <span class="font-semibold text-emerald-700">Vigente</span></div>
                    <div>Póliza RCE: <span class="font-semibold text-emerald-700">Vigente</span></div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-slate-400">
                No tiene vehículos vinculados actualmente.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Viajes Realizados y Liquidación -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Servicios Realizados y Liquidación</h2>
            <span class="text-xs text-slate-400">Historial Operativo</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Orden #</th>
                        <th class="px-5 py-3">Vehículo</th>
                        <th class="px-5 py-3">Ruta</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Km Recorridos</th>
                        <th class="px-5 py-3 text-right">Estado Liquidación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ordenesAliadas as $ord)
                    @php
                        $kmRecorridos = ($ord->final_odometer && $ord->initial_odometer) ? ($ord->final_odometer - $ord->initial_odometer) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-mono font-bold text-slate-900">#{{ $ord->order_number }}</td>
                        <td class="px-5 py-4 font-bold text-slate-800">{{ $ord->vehicle?->plate ?? 'N/A' }}</td>
                        <td class="px-5 py-4">{{ $ord->origin }} ➔ {{ $ord->destination }}</td>
                        <td class="px-5 py-4 text-xs text-slate-500">{{ $ord->service_date }}</td>
                        <td class="px-5 py-4 font-mono text-xs">{{ $kmRecorridos > 0 ? number_format($kmRecorridos, 0) . ' Km' : 'Calculando' }}</td>
                        <td class="px-5 py-4 text-right">
                            @if($ord->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Liquidada</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                            No se registran viajes recientes para la flota vinculada.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
