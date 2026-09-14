<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclePart extends Model
{
    protected $fillable = [
        'vehicle_id',
        'name',
        'replacement_mileage',
        'lifespan_km',
        'replacement_date',
        'brand',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'replacement_mileage' => 'decimal:2',
            'lifespan_km' => 'decimal:2',
            'replacement_date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
