<?php

namespace App\Models\Tenant;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'partner_id',
        'name',
        'document_number',
        'phone',
        'email',
        'address',
        'employee_type',
        'contract_number',
        'contract_type',
        'contract_start_date',
        'contract_end_date',
        'driver_license_number',
        'driver_license_category',
        'driver_license_expiration',
        'photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'driver_license_expiration' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'driver_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(PreoperationalChecklist::class, 'driver_id');
    }

    public function fuelRefills(): HasMany
    {
        return $this->hasMany(FuelRefill::class, 'driver_id');
    }
}
