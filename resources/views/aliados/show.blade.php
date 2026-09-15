@extends('layouts.app')

@section('title', 'Aliado ' . $aliado->name)

@section('content')
<div class="w-full max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('aliados.index') }}" class="hover:underline">Aliados y Convenios</a>
                <span>/</span>
                <span class="text-slate-800">Ficha del Aliado</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $aliado->name }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $aliado->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-700 border-slate-200' }}">
                    {{ $aliado->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                NIT: <strong class="font-mono text-slate-700">{{ $aliado->nit }}</strong> • Contacto: {{ $aliado->contact_person ?? 'N/D' }} • Tel: {{ $aliado->phone ?? 'N/D' }}
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('aliados.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Grid de Flota y Conductores Vinculados -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Flota Vehicular Vinculada -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Vehículos del Aliado ({{ $aliado->vehicles->count() }})</h2>
                <a href="{{ route('vehiculos.create') }}" class="text-xs font-bold text-blue-600 hover:underline">+ Asignar Vehículo</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($aliado->vehicles as $v)
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="px-2.5 py-1 rounded-md bg-amber-50 text-slate-900 border border-amber-300 font-mono text-sm font-extrabold">
                            {{ $v->plate }}
                        </span>
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ $v->brand }} {{ $v->line }} ({{ $v->model_year }})</div>
                            <div class="text-[11px] text-slate-400">{{ $v->vehicle_type }} • {{ $v->passenger_capacity }} Pasajeros</div>
                        </div>
                    </div>
                    <a href="{{ route('vehiculos.show', $v->id) }}" class="text-xs font-bold text-blue-600 hover:underline">Ver Ficha</a>
                </div>
                @empty
                <div class="py-6 text-center text-xs text-slate-400">
                    No hay vehículos vinculados a este aliado todavía.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Personal / Conductores Vinculados -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Conductores del Aliado ({{ $aliado->employees->count() }})</h2>
                <a href="{{ route('conductores.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Ver Directorio</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($aliado->employees as $emp)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-800">{{ $emp->name }}</div>
                        <div class="text-[11px] text-slate-400">C.C. {{ $emp->document_number }} • Tel: {{ $emp->phone }}</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $emp->status === 'Activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $emp->status }}
                    </span>
                </div>
                @empty
                <div class="py-6 text-center text-xs text-slate-400">
                    No hay conductores registrados bajo este aliado.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
