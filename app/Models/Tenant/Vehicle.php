<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plate',
        'internal_number',
        'brand',
        'line',
        'model_year',
        'color',
        'engine_number',
        'chassis_number',
        'vehicle_type',
        'passenger_capacity',
        'current_mileage',
        'soat_number',
        'soat_expiration',
        'technomechanical_number',
        'technomechanical_expiration',
        'contractual_policy_number',
        'contractual_policy_expiration',
        'extra_contractual_policy_number',
        'extra_contractual_policy_expiration',
        'operation_card_number',
        'operation_card_expiration',
        'partner_id',
        'default_driver_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'soat_expiration' => 'date',
            'technomechanical_expiration' => 'date',
            'contractual_policy_expiration' => 'date',
            'extra_contractual_policy_expiration' => 'date',
            'operation_card_expiration' => 'date',
            'current_mileage' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function defaultDriver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'default_driver_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(VehiclePart::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function fuelRefills(): HasMany
    {
        return $this->hasMany(FuelRefill::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(PreoperationalChecklist::class);
    }

    public function gpsLocations(): HasMany
    {
        return $this->hasMany(GpsLocation::class);
    }
}
