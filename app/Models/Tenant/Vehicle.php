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

    public function getDocumentStatusAttribute(): string
    {
        $today = now()->startOfDay();
        $fields = [
            'SOAT' => $this->soat_expiration,
            'Tecnomecánica' => $this->technomechanical_expiration,
            'Póliza Contractual' => $this->contractual_policy_expiration,
            'Póliza Extracontractual' => $this->extra_contractual_policy_expiration,
            'Tarjeta de Operación' => $this->operation_card_expiration,
        ];

        foreach ($fields as $label => $date) {
            if ($date && $date->lt($today)) {
                return 'Vencido';
            }
        }

        foreach ($fields as $label => $date) {
            if ($date && $date->diffInDays($today, false) <= 0 && $date->diffInDays($today, false) >= -30) {
                return 'Por Vencer';
            }
        }

        return 'Al Día';
    }

    public function getDocumentStatusColorAttribute(): string
    {
        return match ($this->document_status) {
            'Vencido' => 'danger',
            'Por Vencer' => 'warning',
            default => 'success',
        };
    }

    public function isEligibleForService(): bool
    {
        return empty($this->getEligibilityErrors());
    }

    public function getEligibilityErrors(): array
    {
        $errors = [];
        $today = now()->startOfDay();

        if ($this->status !== 'Activo') {
            $errors[] = "El vehículo se encuentra en estado '{$this->status}'.";
        }

        $documents = [
            'SOAT' => $this->soat_expiration,
            'Revisión Tecnomecánica' => $this->technomechanical_expiration,
            'Póliza Contractual' => $this->contractual_policy_expiration,
            'Póliza Extracontractual' => $this->extra_contractual_policy_expiration,
            'Tarjeta de Operación' => $this->operation_card_expiration,
        ];

        foreach ($documents as $name => $date) {
            if (! $date) {
                $errors[] = "El documento {$name} no tiene fecha de vigencia registrada.";
            } elseif ($date->lt($today)) {
                $errors[] = "El documento {$name} está vencido desde {$date->format('Y-m-d')}.";
            }
        }

        return $errors;
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
