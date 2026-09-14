<?php

namespace App\Events\Tenant;

use App\Models\Tenant\GpsLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GpsLocationReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tenantId;

    public array $payload;

    public function __construct(GpsLocation $location, ?string $tenantId = null)
    {
        $this->tenantId = $tenantId ?? (tenant('id') ?? 'default');

        $location->loadMissing(['vehicle', 'driver', 'serviceOrder']);

        $speed = (float) $location->speed;
        $isSpeeding = $speed > 80.0; // Límite legal estándar en carretera en Colombia

        $this->payload = [
            'id' => $location->id,
            'vehicle_id' => $location->vehicle_id,
            'plate' => $location->vehicle?->plate ?? 'N/A',
            'driver_name' => $location->driver?->name ?? 'Sin Conductor',
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'speed' => $speed,
            'heading' => (float) $location->heading,
            'accuracy' => (float) $location->accuracy,
            'is_speeding' => $isSpeeding,
            'service_order_number' => $location->serviceOrder?->order_number,
            'timestamp' => $location->device_timestamp ? $location->device_timestamp->format('Y-m-d H:i:s') : now()->toDateTimeString(),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("tenant.{$this->tenantId}.fleet"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'GpsLocationReceived';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
