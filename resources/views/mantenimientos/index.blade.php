@extends('layouts.app')

@section('title', 'Mantenimiento de Flota')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{ openModal: false }">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Parque Automotor</span>
                <span>/</span>
                <span class="text-slate-800">Taller & Mantenimiento</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Mantenimiento de Flota</h1>
            <p class="text-sm text-slate-500 mt-1">
                Control de servicios preventivos, correctivos, historial de costos y kilometraje de intervención.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Inversión Total:</span>
                <span class="font-bold text-slate-900 font-mono">${{ number_format($totalCosto, 0) }} COP</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600 font-bold">{{ $totalRegistros }} Registros</span>
            </div>

            <button @click="openModal = true" type="button"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrar Mantenimiento
            </button>
        </div>
    </div>

    <!-- Tabla Mantenimientos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Vehículo</th>
                        <th class="px-4 py-3.5">Tipo & Taller</th>
                        <th class="px-4 py-3.5">Detalle / Trabajo</th>
                        <th class="px-4 py-3.5 text-center">Fecha</th>
                        <th class="px-4 py-3.5 text-right">Kilometraje</th>
                        <th class="px-5 py-3.5 text-right">Costo (COP)</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($mantenimientos as $m)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-sm font-bold">
                                {{ $m->vehicle?->plate }}
                            </span>
                            <div class="text-xs text-slate-400 mt-1">{{ $m->vehicle?->brand }} {{ $m->vehicle?->line }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold border {{ $m->maintenance_type === 'Preventivo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                {{ $m->maintenance_type }}
                            </span>
                            <div class="text-xs text-slate-600 font-semibold mt-1">{{ $m->workshop_name }}</div>
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-600 max-w-sm">
                            {{ $m->details ?? 'Sin observaciones' }}
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap text-xs text-slate-700">
                            {{ $m->maintenance_date?->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-4 text-right font-mono text-xs text-slate-700">
                            {{ number_format($m->mileage, 0) }} Km
                        </td>
                        <td class="px-5 py-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                            ${{ number_format($m->cost, 0) }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <form action="{{ route('mantenimientos.destroy', $m->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este registro de mantenimiento?');">
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
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            No hay mantenimientos registrados aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mantenimientos->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $mantenimientos->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Registrar Mantenimiento -->
    <div x-cloak x-show="openModal" class="relative z-50" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openModal = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 flex items-center justify-center">
            <div class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-5" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-lg font-bold text-slate-900">Registrar Mantenimiento</h2>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('mantenimientos.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Vehículo *</label>
                        <select name="vehicle_id" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm font-mono">
                            <option value="">-- Seleccionar Placa --</option>
                            @foreach($vehiculos as $v)
                            <option value="{{ $v->id }}">{{ $v->plate }} - {{ $v->brand }} ({{ number_format($v->current_mileage, 0) }} Km)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Tipo *</label>
                            <select name="maintenance_type" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                                <option value="Preventivo">Preventivo</option>
                                <option value="Correctivo">Correctivo</option>
                                <option value="Predictivo">Predictivo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha *</label>
                            <input type="date" name="maintenance_date" value="{{ date('Y-m-d') }}" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kilometraje (Km) *</label>
                            <input type="number" step="0.01" name="mileage" required placeholder="125000" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Costo Total ($) *</label>
                            <input type="number" step="0.01" name="cost" required placeholder="350000" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Taller / Proveedor *</label>
                        <input type="text" name="workshop_name" required placeholder="Taller Autorizado S.A.S." class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Detalles de la Intervención</label>
                        <textarea name="details" rows="2" placeholder="Cambio de aceite, pastillas de freno, filtros..." class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition">
                            Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
