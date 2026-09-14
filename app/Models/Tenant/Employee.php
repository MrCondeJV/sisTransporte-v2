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

    public function getLicenseStatusAttribute(): string
    {
        if (! $this->driver_license_expiration) {
            return 'Sin Licencia';
        }

        $today = now()->startOfDay();
        if ($this->driver_license_expiration->lt($today)) {
            return 'Vencida';
        }

        if ($this->driver_license_expiration->diffInDays($today, false) <= 0 && $this->driver_license_expiration->diffInDays($today, false) >= -30) {
            return 'Por Vencer';
        }

        return 'Al Día';
    }

    public function getLicenseStatusColorAttribute(): string
    {
        return match ($this->license_status) {
            'Vencida', 'Sin Licencia' => 'danger',
            'Por Vencer' => 'warning',
            default => 'success',
        };
    }

    public function isEligibleToDrive(): bool
    {
        return empty($this->getEligibilityErrors());
    }

    public function getEligibilityErrors(): array
    {
        $errors = [];
        $today = now()->startOfDay();

        if ($this->status !== 'Activo') {
            $errors[] = "El conductor se encuentra en estado '{$this->status}'.";
        }

        if ($this->employee_type !== 'Conductor') {
            $errors[] = "El empleado tiene rol '{$this->employee_type}', no 'Conductor'.";
        }

        if (! $this->driver_license_expiration) {
            $errors[] = 'El conductor no tiene fecha de vigencia de licencia de conducción registrada.';
        } elseif ($this->driver_license_expiration->lt($today)) {
            $errors[] = "La licencia de conducción está vencida desde {$this->driver_license_expiration->format('Y-m-d')}.";
        }

        return $errors;
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
