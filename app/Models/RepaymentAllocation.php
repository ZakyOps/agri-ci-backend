<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['repayment_id', 'debt_id', 'amount_fcfa'])]
class RepaymentAllocation extends Model
{
    protected function casts(): array
    {
        return [
            'amount_fcfa' => 'integer',
        ];
    }

    public function repayment(): BelongsTo
    {
        return $this->belongsTo(Repayment::class);
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }
}
