<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceIncident extends Model
{
    protected $fillable = [
        'service_order_id',
        'driver_id',
        'vehicle_id',
        'incident_type',
        'description',
        'photos',
        'reported_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'reported_at' => 'datetime',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
