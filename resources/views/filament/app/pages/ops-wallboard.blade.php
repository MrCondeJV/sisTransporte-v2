<x-filament-panels::page>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- 1. Tarjetas de Métricas de Operación (KPIs) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Flota Total -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Flota Total Activa</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalVehicles }}</div>
            <span class="text-xs text-gray-400">Unidades registradas</span>
        </div>

        <!-- En Línea (GPS) -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Con GPS Activo</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $onlineCount }}</div>
            <span class="text-xs text-gray-400">Reportando señal</span>
        </div>

        <!-- En Movimiento -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">En Movimiento</div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $movingCount }}</div>
            <span class="text-xs text-gray-400">Velocidad > 5 km/h</span>
        </div>

        <!-- Detenidos -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Detenidos / Ralentí</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stoppedCount }}</div>
            <span class="text-xs text-gray-400">Velocidad &le; 5 km/h</span>
        </div>

        <!-- Alertas de Velocidad -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
            <div class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Exceso Velocidad</div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 flex items-center gap-2">
                {{ $speedingCount }}
                @if($speedingCount > 0)
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                @endif
            </div>
            <span class="text-xs text-gray-400">&gt; 80 km/h en ruta</span>
        </div>
    </div>

    <!-- 2. Mapa Interactivo y Panel Lateral -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start mt-2">
        <!-- Contenedor del Mapa Leaflet -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Mapa Satelital de Operaciones en Vivo</h3>
                </div>
                <span class="text-xs text-gray-400">Actualización en tiempo real</span>
            </div>

            <div id="fleet-map" style="height: 520px; width: 100%; z-index: 1;" wire:ignore></div>
        </div>

        <!-- Panel Lateral: Lista de Unidades en Ruta -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden flex flex-col" style="max-height: 575px;">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Unidades Reportando</h3>
                <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 font-bold px-2 py-0.5 rounded-full">
                    {{ $onlineCount }}
                </span>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800 overflow-y-auto flex-1 p-2 space-y-1">
                @forelse($positions as $pos)
                    @php
                        $isSpeeding = $pos->speed > 80;
                        $isMoving = $pos->speed > 5;
                    @endphp
                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-xl transition cursor-pointer"
                         onclick="centerVehicle({{ (float)$pos->latitude }}, {{ (float)$pos->longitude }}, '{{ $pos->vehicle?->plate }}')">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-sm text-gray-900 dark:text-white px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">
                                    {{ $pos->vehicle?->plate ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $pos->vehicle?->brand }}</span>
                            </div>

                            @if($isSpeeding)
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300 animate-pulse">
                                    {{ round($pos->speed) }} km/h ⚠️
                                </span>
                            @elseif($isMoving)
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                                    {{ round($pos->speed) }} km/h
                                </span>
                            @else
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Detenido
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            👤 Conductor: <span class="font-medium text-gray-900 dark:text-gray-200">{{ $pos->driver?->name ?? 'Sin Asignar' }}</span>
                        </div>

                        <div class="flex justify-between items-center mt-1 text-[11px] text-gray-400">
                            <span>📋 {{ $pos->serviceOrder?->order_number ?? 'Sin orden' }}</span>
                            <span>🕒 {{ $pos->device_timestamp ? $pos->device_timestamp->format('H:i:s') : '' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 text-sm">
                        No hay unidades transmitiendo coordenadas en este momento.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Script de Inicialización de Leaflet y Dibujo de Marcadores -->
    <script>
        let map;
        let markers = {};

        function initFleetMap() {
            const rawData = {!! $positionsJson !!};

            // Centro por defecto: Bogotá, Colombia
            let defaultLat = 4.6097;
            let defaultLng = -74.0817;
            let defaultZoom = 11;

            if (rawData.length > 0) {
                defaultLat = rawData[0].lat;
                defaultLng = rawData[0].lng;
            }

            map = L.map('fleet-map').setView([defaultLat, defaultLng], defaultZoom);

            // Capa de mapa OpenStreetMap limpia y rápida
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors | sisTransporte GPS',
                maxZoom: 19
            }).addTo(map);

            // Dibujar marcadores para cada vehículo
            rawData.forEach(item => {
                const markerColor = item.is_speeding ? '#e11d48' : (item.speed > 5 ? '#059669' : '#3b82f6');

                const customIcon = L.divIcon({
                    className: 'custom-vehicle-marker',
                    html: `<div style="background-color: ${markerColor}; color: white; padding: 3px 6px; border-radius: 6px; font-weight: bold; font-size: 11px; font-family: monospace; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); text-align: center; white-space: nowrap;">
                               🚗 ${item.plate} (${Math.round(item.speed)} km/h)
                           </div>`,
                    iconSize: [80, 26],
                    iconAnchor: [40, 13]
                });

                const marker = L.marker([item.lat, item.lng], { icon: customIcon }).addTo(map);

                const popupContent = `
                    <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4;">
                        <strong style="font-size: 13px; color: #0f172a;">${item.plate} - ${item.brand}</strong><br>
                        <strong>Conductor:</strong> ${item.driver}<br>
                        <strong>Orden:</strong> ${item.order}<br>
                        <strong>Velocidad:</strong> <span style="font-weight: bold; color: ${item.is_speeding ? 'red' : 'green'}">${item.speed} km/h</span><br>
                        <strong>Último reporte:</strong> ${item.time}
                    </div>
                `;

                marker.bindPopup(popupContent);
                markers[item.plate] = marker;
            });

            if (rawData.length > 1) {
                const group = new L.featureGroup(Object.values(markers));
                map.fitBounds(group.getBounds().pad(0.15));
            }
        }

        function centerVehicle(lat, lng, plate) {
            if (map) {
                map.flyTo([lat, lng], 15, { duration: 1.2 });
                if (markers[plate]) {
                    markers[plate].openPopup();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initFleetMap();
        });

        // Soporte de Livewire navigation / refresh
        document.addEventListener('livewire:navigated', () => {
            if (document.getElementById('fleet-map') && !map) {
                initFleetMap();
            }
        });
    </script>
</x-filament-panels::page>
