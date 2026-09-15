@extends('layouts.app')

@section('title', 'Hoja de Vida Vehículo ' . $vehiculo->plate)

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <!-- Header / Banner de la Unidad -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="h-16 w-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-md shadow-blue-500/20 flex-shrink-0">
                {{ $vehiculo->internal_number ? '#' . $vehiculo->internal_number : 'VEH' }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xl font-black">
                        {{ $vehiculo->plate }}
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold border {{ $vehiculo->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                        {{ $vehiculo->status }}
                    </span>
                </div>
                <div class="text-sm font-semibold text-slate-700 mt-1">
                    {{ $vehiculo->brand }} {{ $vehiculo->line }} • Modelo {{ $vehiculo->model_year }} • {{ $vehiculo->vehicle_type }} ({{ $vehiculo->passenger_capacity }} Pax)
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('vehiculos.edit', $vehiculo->id) }}"
                class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                Editar Datos
            </a>
            <a href="{{ route('vehiculos.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Matriz de Documentos Oficiales y Cumplimiento Normativo -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
            <span class="flex items-center">
                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2"></span>
                Vigencia Legal & Documental (Ministerio de Transporte)
            </span>
            <span class="text-xs text-slate-400 font-normal">Actualizado al día de hoy</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- SOAT -->
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase">SOAT</span>
                <div class="text-sm font-bold text-slate-900">{{ $vehiculo->soat_number ?? 'Sin registrar' }}</div>
                <div class="text-xs flex items-center justify-between pt-2 border-t border-slate-200">
                    <span class="text-slate-500">Vence:</span>
                    <span class="font-bold {{ $vehiculo->soat_expiration?->lt(now()) ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ $vehiculo->soat_expiration?->format('d/m/Y') ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <!-- Tecnomecánica -->
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase">RTM</span>
                <div class="text-sm font-bold text-slate-900">{{ $vehiculo->technomechanical_number ?? 'Sin registrar' }}</div>
                <div class="text-xs flex items-center justify-between pt-2 border-t border-slate-200">
                    <span class="text-slate-500">Vence:</span>
                    <span class="font-bold {{ $vehiculo->technomechanical_expiration?->lt(now()) ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ $vehiculo->technomechanical_expiration?->format('d/m/Y') ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <!-- Tarjeta de Operación -->
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase">Tarjeta Operación</span>
                <div class="text-sm font-bold text-slate-900">{{ $vehiculo->operation_card_number ?? 'Sin registrar' }}</div>
                <div class="text-xs flex items-center justify-between pt-2 border-t border-slate-200">
                    <span class="text-slate-500">Vence:</span>
                    <span class="font-bold {{ $vehiculo->operation_card_expiration?->lt(now()) ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ $vehiculo->operation_card_expiration?->format('d/m/Y') ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <!-- Kilometraje y Propietario -->
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase">Odómetro Actual</span>
                <div class="text-lg font-black text-blue-600">{{ number_format($vehiculo->current_mileage, 0) }} Km</div>
                <div class="text-xs text-slate-500 pt-2 border-t border-slate-200 truncate">
                    Prop: {{ $vehiculo->partner?->name ?? 'Empresa' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Servicios y Despachos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">Historial de Órdenes de Servicio</h3>
            <span class="text-xs text-slate-400">{{ $vehiculo->serviceOrders->count() }} servicios registrados</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($vehiculo->serviceOrders->take(5) as $so)
            <div class="p-4 flex items-center justify-between text-sm">
                <div>
                    <span class="font-bold text-slate-900">#{{ $so->order_number }}</span>
                    <span class="text-xs text-slate-400 ml-2">{{ $so->scheduled_start_time?->format('d/m/Y H:i') }}</span>
                    <div class="text-xs text-slate-600 mt-0.5">{{ $so->origin }} &rarr; {{ $so->destination }}</div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-slate-50 text-slate-700 border-slate-200">
                    {{ $so->status }}
                </span>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                No hay órdenes de servicio asociadas a este vehículo.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
