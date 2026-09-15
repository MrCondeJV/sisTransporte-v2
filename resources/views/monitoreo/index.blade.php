@extends('layouts.app')

@section('title', 'Monitoreo GPS & Despachos en Vivo')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #wallboard-map {
        height: 580px;
        border-radius: 1.5rem;
        z-index: 1;
    }
</style>
@endsection

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Operaciones & Telemetría</span>
                <span>/</span>
                <span class="text-slate-800">Centro de Control en Vivo</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Monitoreo GPS & Wallboard</h1>
            <p class="text-sm text-slate-500 mt-1">
                Supervisión satelital en tiempo real, geolocalización de vehículos, velocidad en ruta y estado de despacho.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                Transmisión Satelital Activa
            </span>
            <button onclick="location.reload()" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-xs transition" title="Refrescar">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    <!-- 5 KPIs Operativos estilo sys-POS -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Flota Activa</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2">{{ $totalVehicles }}</div>
            <span class="text-xs text-slate-500 mt-1 block">Unidades habilitadas</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-bold text-blue-600 uppercase tracking-wider">Con Señal GPS</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-blue-600 mt-2">{{ $onlineCount }}</div>
            <span class="text-xs text-slate-500 mt-1 block">Transmitiendo</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">En Movimiento</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-2">{{ $movingCount }}</div>
            <span class="text-xs text-slate-500 mt-1 block">&gt; 5 km/h</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Detenidos / Ralentí</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-2">{{ $stoppedCount }}</div>
            <span class="text-xs text-slate-500 mt-1 block">&le; 5 km/h</span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Exceso Velocidad</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-rose-600 mt-2 flex items-center gap-2">
                {{ $speedingCount }}
                @if($speedingCount > 0)
                <span class="h-2 w-2 rounded-full bg-rose-500 animate-ping"></span>
                @endif
            </div>
            <span class="text-xs text-slate-500 mt-1 block">&gt; 80 km/h</span>
        </div>
    </div>

    <!-- Tablero con Mapa y Lista de Posiciones -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Mapa Leaflet -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 p-4 shadow-sm">
            <div id="wallboard-map" class="shadow-inner"></div>
        </div>

        <!-- Panel Lateral de Unidades Reportando -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-900">Unidades en Ruta</h2>
                <span class="text-xs text-slate-400">{{ $positions->count() }} activas</span>
            </div>

            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                @forelse($positions as $pos)
                <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-slate-100/80 transition cursor-pointer"
                     onclick="focusVehicle({{ (float)$pos->latitude }}, {{ (float)$pos->longitude }}, '{{ $pos->vehicle?->plate }}')">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xs font-black">
                            {{ $pos->vehicle?->plate }}
                        </span>
                        <span class="font-mono text-xs font-bold {{ $pos->speed > 80 ? 'text-rose-600' : ($pos->speed > 5 ? 'text-emerald-600' : 'text-amber-600') }}">
                            {{ number_format($pos->speed, 0) }} km/h
                        </span>
                    </div>
                    <div class="text-xs font-semibold text-slate-700 mt-1.5 truncate">
                        {{ $pos->driver?->name ?? 'Sin conductor' }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center justify-between">
                        <span>Orden: {{ $pos->serviceOrder?->order_number ?? 'S/N' }}</span>
                        <span>{{ $pos->device_timestamp?->format('H:i:s') }}</span>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-slate-400 text-sm">
                    <svg class="h-8 w-8 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    No hay telemetría activa en este instante.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, markers = {};
    const positions = {!! $positionsJson !!};

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar mapa centrado en Colombia
        map = L.map('wallboard-map').setView([4.7110, -74.0721], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        if (positions && positions.length > 0) {
            const bounds = [];
            positions.forEach(pos => {
                if (pos.lat && pos.lng) {
                    const marker = L.marker([pos.lat, pos.lng]).addTo(map)
                        .bindPopup(`
                            <div class="p-1 font-sans text-xs">
                                <b class="text-sm font-mono">${pos.plate}</b> (${pos.brand})<br>
                                Conductor: <b>${pos.driver}</b><br>
                                Velocidad: <b class="${pos.speed > 80 ? 'text-red-600' : 'text-green-600'}">${pos.speed} km/h</b><br>
                                Orden: ${pos.order}<br>
                                <span class="text-gray-400">${pos.time}</span>
                            </div>
                        `);
                    markers[pos.plate] = marker;
                    bounds.push([pos.lat, pos.lng]);
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [40, 40] });
            }
        }
    });

    function focusVehicle(lat, lng, plate) {
        if (map && lat && lng) {
            map.setView([lat, lng], 15, { animate: true });
            if (markers[plate]) {
                markers[plate].openPopup();
            }
        }
    }
</script>
@endsection
