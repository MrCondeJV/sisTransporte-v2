<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'maintenance_date',
        'mileage',
        'cost',
        'workshop_name',
        'details',
        'receipt_file',
        'replaced_parts',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'mileage' => 'decimal:2',
            'cost' => 'decimal:2',
            'replaced_parts' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
