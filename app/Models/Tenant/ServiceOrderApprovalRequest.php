<?php

namespace App\Models\Tenant;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderApprovalRequest extends Model
{
    protected $fillable = [
        'service_order_id',
        'requested_by_user_id',
        'request_type',
        'reason',
        'proposed_changes',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'manager_notes',
    ];

    protected function casts(): array
    {
        return [
            'proposed_changes' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
