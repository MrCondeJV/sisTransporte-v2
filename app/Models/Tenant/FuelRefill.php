<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRefill extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'service_order_id',
        'refill_date',
        'gallons',
        'total_cost',
        'price_per_gallon',
        'odometer_mileage',
        'distance_since_last_refill',
        'calculated_performance',
        'gas_station_name',
        'odometer_photo',
        'receipt_photo',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'refill_date' => 'date',
            'gallons' => 'decimal:3',
            'total_cost' => 'decimal:2',
            'price_per_gallon' => 'decimal:2',
            'odometer_mileage' => 'decimal:2',
            'distance_since_last_refill' => 'decimal:2',
            'calculated_performance' => 'decimal:2',
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
