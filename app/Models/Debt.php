<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'transaction_id',
    'farmer_id',
    'original_amount_fcfa',
    'paid_amount_fcfa',
    'remaining_amount_fcfa',
    'status',
])]
class Debt extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    protected function casts(): array
    {
        return [
            'original_amount_fcfa' => 'integer',
            'paid_amount_fcfa' => 'integer',
            'remaining_amount_fcfa' => 'integer',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(RepaymentAllocation::class);
    }
}
