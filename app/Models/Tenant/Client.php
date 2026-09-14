<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'business_name',
        'document_number',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->type === 'Empresa'
            ? ($this->business_name ?? $this->document_number)
            : trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
