<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['identifier', 'firstname', 'lastname', 'phone', 'credit_limit_fcfa'])]
class Farmer extends Model
{
    protected function casts(): array
    {
        return [
            'credit_limit_fcfa' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    public function outstandingDebtFcfa(): int
    {
        return (int) $this->debts()->where('status', '!=', 'paid')->sum('remaining_amount_fcfa');
    }
}
