@extends('layouts.app')

@section('title', 'Combustible & Rendimiento')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{ openModal: false }">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Parque Automotor</span>
                <span>/</span>
                <span class="text-slate-800">Combustible & Consumo</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Combustible y Rendimiento</h1>
            <p class="text-sm text-slate-500 mt-1">
                Control de abastecimiento, galones suministrados, gasto acumulado y eficiencia por kilómetro.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Gasto Total:</span>
                <span class="font-bold text-slate-900 font-mono">${{ number_format($totalGasto, 0) }} COP</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600 font-bold font-mono">{{ number_format($totalGalones, 1) }} Galones</span>
            </div>

            <button @click="openModal = true" type="button"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrar Tanqueo
            </button>
        </div>
    </div>

    <!-- Tabla Tanqueos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Vehículo</th>
                        <th class="px-4 py-3.5">Conductor</th>
                        <th class="px-4 py-3.5">Estación de Servicio</th>
                        <th class="px-4 py-3.5 text-center">Fecha</th>
                        <th class="px-4 py-3.5 text-right">Galones</th>
                        <th class="px-4 py-3.5 text-right">Odómetro (Km)</th>
                        <th class="px-5 py-3.5 text-right">Total ($)</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($tanqueos as $t)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-sm font-bold">
                                {{ $t->vehicle?->plate }}
                            </span>
                            <div class="text-xs text-slate-400 mt-1">{{ $t->vehicle?->brand }} {{ $t->vehicle?->line }}</div>
                        </td>
                        <td class="px-4 py-4 text-xs font-medium text-slate-700">
                            {{ $t->driver?->name ?? 'No especificado' }}
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-700 font-semibold">
                            {{ $t->gas_station_name }}
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap text-xs text-slate-600">
                            {{ $t->refill_date?->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-4 text-right font-mono text-xs font-bold text-slate-800">
                            {{ number_format($t->gallons, 2) }} Gal
                        </td>
                        <td class="px-4 py-4 text-right font-mono text-xs text-slate-600">
                            {{ number_format($t->odometer_mileage, 0) }} Km
                        </td>
                        <td class="px-5 py-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                            ${{ number_format($t->total_cost, 0) }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <form action="{{ route('combustible.destroy', $t->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este registro de combustible?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Eliminar">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            No hay registros de combustible guardados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tanqueos->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $tanqueos->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Registrar Tanqueo -->
    <div x-cloak x-show="openModal" class="relative z-50" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openModal = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 flex items-center justify-center">
            <div class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-5" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-lg font-bold text-slate-900">Registrar Abastecimiento de Combustible</h2>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('combustible.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Vehículo *</label>
                        <select name="vehicle_id" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm font-mono">
                            <option value="">-- Seleccionar Placa --</option>
                            @foreach($vehiculos as $v)
                            <option value="{{ $v->id }}">{{ $v->plate }} - {{ $v->brand }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Conductor</label>
                            <select name="driver_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                                <option value="">-- Sin conductor --</option>
                                @foreach($conductores as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha *</label>
                            <input type="date" name="refill_date" value="{{ date('Y-m-d') }}" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Galones *</label>
                            <input type="number" step="0.001" name="gallons" required placeholder="12.5" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Total ($) *</label>
                            <input type="number" step="0.01" name="total_cost" required placeholder="180000" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Odómetro *</label>
                            <input type="number" step="0.01" name="odometer_mileage" required placeholder="125400" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Estación de Servicio / Gasolinera *</label>
                        <input type="text" name="gas_station_name" required placeholder="Terpel / Primax Calle 80" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition">
                            Guardar Tanqueo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
