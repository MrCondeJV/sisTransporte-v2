<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'client_id',
        'contract_id',
        'vehicle_id',
        'driver_id',
        'support_driver_id',
        'partner_id',
        'origin',
        'destination',
        'route_name',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'passenger_contact_name',
        'passenger_contact_phone',
        'passengers_count',
        'service_notes',
        'start_mileage',
        'end_mileage',
        'invoice_number',
        'invoice_file',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start_time' => 'datetime',
            'scheduled_end_time' => 'datetime',
            'actual_start_time' => 'datetime',
            'actual_end_time' => 'datetime',
            'start_mileage' => 'decimal:2',
            'end_mileage' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function supportDriver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'support_driver_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function fuec(): HasOne
    {
        return $this->hasOne(FuecDocument::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(PreoperationalChecklist::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(ServiceIncident::class);
    }

    public function gpsLocations(): HasMany
    {
        return $this->hasMany(GpsLocation::class);
    }
}
