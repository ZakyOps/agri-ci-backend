<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['farmer_id', 'operator_id', 'commodity_kg', 'rate_fcfa_per_kg', 'value_fcfa'])]
class Repayment extends Model
{
    protected function casts(): array
    {
        return [
            'commodity_kg' => 'decimal:2',
            'rate_fcfa_per_kg' => 'integer',
            'value_fcfa' => 'integer',
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

    public function allocations(): HasMany
    {
        return $this->hasMany(RepaymentAllocation::class);
    }
}
