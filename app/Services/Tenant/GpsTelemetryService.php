<?php

namespace App\Services\Tenant;

use App\Events\Tenant\GpsLocationReceived;
use App\Models\Tenant\GpsLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GpsTelemetryService
{
    /**
     * Registra una posición GPS individual y emite el evento de WebSocket.
     */
    public function recordLocation(array $data): GpsLocation
    {
        $data['device_timestamp'] = $data['device_timestamp'] ?? now();
        $data['created_at'] = now();

        $location = GpsLocation::create($data);

        // Disparar evento de transmisión en tiempo real
        event(new GpsLocationReceived($location));

        return $location;
    }

    /**
     * Ingesta masiva por lotes (batch) optimizada para alta concurrencia desde dispositivos o apps.
     */
    public function recordBatch(array $points): int
    {
        if (empty($points)) {
            return 0;
        }

        $now = now();
        $rows = [];
        $latestPointsByVehicle = [];

        foreach ($points as $point) {
            $rows[] = [
                'vehicle_id' => $point['vehicle_id'],
                'driver_id' => $point['driver_id'] ?? null,
                'service_order_id' => $point['service_order_id'] ?? null,
                'latitude' => $point['latitude'],
                'longitude' => $point['longitude'],
                'speed' => $point['speed'] ?? 0,
                'heading' => $point['heading'] ?? 0,
                'accuracy' => $point['accuracy'] ?? null,
                'device_timestamp' => $point['device_timestamp'] ?? $now,
                'created_at' => $now,
            ];

            // Guardar el último punto para emitir el evento en vivo
            $latestPointsByVehicle[$point['vehicle_id']] = $point;
        }

        // Inserción masiva en chunks para eficiencia SQL
        foreach (array_chunk($rows, 200) as $chunk) {
            GpsLocation::insert($chunk);
        }

        // Disparar evento del último punto por vehículo
        foreach ($latestPointsByVehicle as $vehicleId => $point) {
            $lastLocation = GpsLocation::where('vehicle_id', $vehicleId)
                ->latest('id')
                ->first();

            if ($lastLocation) {
                event(new GpsLocationReceived($lastLocation));
            }
        }

        return count($rows);
    }

    /**
     * Obtiene la última posición registrada de cada vehículo activo de la flota.
     */
    public function getFleetLastPositions(): Collection
    {
        // Subconsulta para obtener el ID de la última ubicación por vehículo
        $latestIds = GpsLocation::select(DB::raw('MAX(id) as id'))
            ->groupBy('vehicle_id')
            ->pluck('id');

        return GpsLocation::with(['vehicle', 'driver', 'serviceOrder.client'])
            ->whereIn('id', $latestIds)
            ->get();
    }

    /**
     * Obtiene el recorrido histórico ordenado cronológicamente para trazar polilíneas en el mapa.
     */
    public function getVehicleRouteHistory(int $vehicleId, ?string $from = null, ?string $to = null, int $limit = 500): Collection
    {
        $query = GpsLocation::where('vehicle_id', $vehicleId)
            ->orderBy('device_timestamp', 'asc');

        if ($from) {
            $query->where('device_timestamp', '>=', $from);
        }
        if ($to) {
            $query->where('device_timestamp', '<=', $to);
        }

        return $query->limit($limit)->get([
            'id',
            'latitude',
            'longitude',
            'speed',
            'heading',
            'device_timestamp',
        ]);
    }
}
