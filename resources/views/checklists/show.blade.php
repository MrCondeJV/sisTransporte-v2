@extends('layouts.app')

@section('title', 'Detalle de Inspección Preoperacional - ' . ($checklist->vehicle?->plate ?? ''))

@section('content')
<div class="w-full max-w-[1400px] mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('checklists.index') }}" class="hover:text-blue-600">Inspecciones Preoperacionales</a>
                <span>/</span>
                <span class="text-slate-800">Acta #{{ $checklist->id }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Inspección Vehículo {{ $checklist->vehicle?->plate ?? 'S/P' }}
                </h1>
                @if($checklist->is_approved)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        Aprobado (Apto para operar)
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                        Rechazado (No Apto)
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('checklists.index') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                &larr; Volver
            </a>
            <a href="{{ route('checklists.pdf', $checklist->id) }}" target="_blank"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Descargar Acta Oficial PDF
            </a>
        </div>
    </div>

    <!-- Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fecha & Hora</p>
            <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $checklist->date?->format('d/m/Y') }}</p>
            <p class="text-xs text-slate-500 font-mono">{{ $checklist->time ?? 'Hora no registrada' }}</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vehículo & Odómetro</p>
            <p class="text-lg font-extrabold text-slate-900 mt-1">
                <span class="px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-sm">
                    {{ $checklist->vehicle?->plate ?? 'N/A' }}
                </span>
            </p>
            <p class="text-xs text-slate-500 font-mono mt-1">{{ number_format($checklist->mileage, 0) }} Km</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Conductor Responsable</p>
            <p class="text-base font-bold text-slate-900 mt-1 truncate">{{ $checklist->driver?->name ?? 'No asignado' }}</p>
            <p class="text-xs text-slate-500 font-mono">Doc: {{ $checklist->driver?->document_number ?? $checklist->driver?->identification_number ?? 'S/N' }}</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Orden Vinculada</p>
            @if($checklist->serviceOrder)
                <a href="{{ route('ordenes.show', $checklist->service_order_id) }}" class="text-base font-bold text-blue-600 hover:underline block mt-1">
                    ODS #{{ $checklist->serviceOrder->order_number }}
                </a>
                <p class="text-xs text-slate-500 truncate">{{ $checklist->serviceOrder->client?->business_name ?? 'Cliente directo' }}</p>
            @else
                <p class="text-base font-bold text-slate-500 mt-1">N/A</p>
                <p class="text-xs text-slate-400">Inspección periódica diaria</p>
            @endif
        </div>
    </div>

    <!-- Items Audit Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Niveles de Fluidos -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </span>
                    1. Niveles de Fluidos
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                @php
                    $fluidos = $checklist->fluid_levels ?? [];
                @endphp
                @forelse($fluidos as $key => $val)
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ in_array(strtoupper((string)$val), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ is_bool($val) ? ($val ? 'OK' : 'Falla') : $val }}
                        </span>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400 italic">No se especificaron ítems de fluidos</div>
                @endforelse
            </div>
        </div>

        <!-- Luces y Sistema Eléctrico -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </span>
                    2. Luces y Sistema Eléctrico
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                @php
                    $luces = $checklist->lights_and_electrical ?? [];
                @endphp
                @forelse($luces as $key => $val)
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ in_array(strtoupper((string)$val), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ is_bool($val) ? ($val ? 'OK' : 'Falla') : $val }}
                        </span>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400 italic">No se especificaron ítems de luces</div>
                @endforelse
            </div>
        </div>

        <!-- Llantas y Frenos -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    3. Neumáticos y Sistema de Frenos
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                @php
                    $frenos = $checklist->tires_and_brakes ?? [];
                @endphp
                @forelse($frenos as $key => $val)
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ in_array(strtoupper((string)$val), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ is_bool($val) ? ($val ? 'OK' : 'Falla') : $val }}
                        </span>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400 italic">No se especificaron ítems de neumáticos y frenos</div>
                @endforelse
            </div>
        </div>

        <!-- Equipo de Carretera y Seguridad -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </span>
                    4. Equipo de Prevención y Seguridad
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                @php
                    $equipo = $checklist->safety_kit ?? [];
                @endphp
                @forelse($equipo as $key => $val)
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ in_array(strtoupper((string)$val), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ is_bool($val) ? ($val ? 'OK' : 'Falla') : $val }}
                        </span>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400 italic">No se especificaron ítems de seguridad</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Cabina, Observaciones y Firma Digital -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cabina y Cinturones -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <h3 class="font-extrabold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="p-2 rounded-xl bg-teal-50 text-teal-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </span>
                5. Cabina y Cinturones
            </h3>
            <div class="divide-y divide-slate-100 text-sm">
                @php
                    $cabina = $checklist->cabin_and_belts ?? [];
                @endphp
                @forelse($cabina as $key => $val)
                    <div class="py-2 flex items-center justify-between">
                        <span class="text-slate-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ in_array(strtoupper((string)$val), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ is_bool($val) ? ($val ? 'OK' : 'Falla') : $val }}
                        </span>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400 italic">No se especificaron ítems</div>
                @endforelse
            </div>
        </div>

        <!-- Observaciones & Novedades -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-3">
            <h3 class="font-extrabold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="p-2 rounded-xl bg-slate-100 text-slate-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                </span>
                Observaciones y Novedades
            </h3>
            @if($checklist->observations)
                <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-200 text-amber-900 text-sm leading-relaxed">
                    {{ $checklist->observations }}
                </div>
            @else
                <p class="text-slate-400 text-sm italic py-4">No se reportaron observaciones ni novedades durante la inspección preoperacional.</p>
            @endif
        </div>

        <!-- Firma Digital del Conductor -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-3 flex flex-col justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </span>
                    Firma de Conformidad
                </h3>
                <p class="text-xs text-slate-500 mt-2">
                    Firma manuscrita digitalizada capturada en el dispositivo móvil del conductor.
                </p>
                <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center min-h-[140px]">
                    @if($checklist->driver_signature)
                        @if(str_starts_with($checklist->driver_signature, 'data:image') || str_starts_with($checklist->driver_signature, 'http'))
                            <img src="{{ $checklist->driver_signature }}" alt="Firma del conductor" class="max-h-28 object-contain">
                        @else
                            <img src="{{ asset('storage/' . $checklist->driver_signature) }}" alt="Firma del conductor" class="max-h-28 object-contain">
                        @endif
                    @else
                        <span class="text-xs text-slate-400 italic">Sin firma digital registrada</span>
                    @endif
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 text-center">
                <p class="text-xs font-bold text-slate-700">{{ $checklist->driver?->name }}</p>
                <p class="text-[11px] text-slate-400 font-mono">C.C. {{ $checklist->driver?->document_number ?? $checklist->driver?->identification_number ?? 'S/N' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

