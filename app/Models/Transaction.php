<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'farmer_id',
    'operator_id',
    'total_fcfa',
    'payment_method',
    'interest_rate',
    'interest_amount_fcfa',
    'credited_total_fcfa',
    'status',
])]
class Transaction extends Model
{
    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_CREDIT = 'credit';

    protected function casts(): array
    {
        return [
            'total_fcfa' => 'integer',
            'interest_rate' => 'decimal:4',
            'interest_amount_fcfa' => 'integer',
            'credited_total_fcfa' => 'integer',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function debt(): HasOne
    {
        return $this->hasOne(Debt::class);
    }
}
