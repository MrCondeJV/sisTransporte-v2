<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'origin',
        'destination',
        'description',
        'route_type',
        'estimated_distance_km',
        'estimated_duration_minutes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_distance_km' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
        ];
    }
}
