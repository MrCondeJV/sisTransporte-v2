<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreoperationalChecklist extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'service_order_id',
        'date',
        'time',
        'fluid_levels',
        'lights_and_electrical',
        'tires_and_brakes',
        'safety_kit',
        'cabin_and_belts',
        'mileage',
        'is_approved',
        'observations',
        'driver_signature',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'fluid_levels' => 'array',
            'lights_and_electrical' => 'array',
            'tires_and_brakes' => 'array',
            'safety_kit' => 'array',
            'cabin_and_belts' => 'array',
            'is_approved' => 'boolean',
            'mileage' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
