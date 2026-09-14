<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuecDocument extends Model
{
    protected $fillable = [
        'service_order_id',
        'fuec_number',
        'resolution_number',
        'issue_date',
        'expiration_date',
        'qr_code_content',
        'pdf_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
