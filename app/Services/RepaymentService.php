<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Repayment;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RepaymentService
{
    public function repay(Farmer $farmer, int $operatorId, float $commodityKg): Repayment
    {
        return DB::transaction(function () use ($farmer, $operatorId, $commodityKg) {
            $rate = (int) Setting::value('commodity_rate_fcfa_per_kg', 1000);
            $value = (int) round($commodityKg * $rate);

            $debts = Debt::query()
                ->where('farmer_id', $farmer->id)
                ->where('status', '!=', Debt::STATUS_PAID)
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($debts->isEmpty()) {
                throw ValidationException::withMessages([
                    'farmer_id' => 'Cet agriculteur n’a aucune dette en cours.',
                ]);
            }

            $repayment = Repayment::query()->create([
                'farmer_id' => $farmer->id,
                'operator_id' => $operatorId,
                'commodity_kg' => $commodityKg,
                'rate_fcfa_per_kg' => $rate,
                'value_fcfa' => $value,
            ]);

            $remainingCredit = $value;

            foreach ($debts as $debt) {
                if ($remainingCredit <= 0) {
                    break;
                }

                $applied = min($remainingCredit, $debt->remaining_amount_fcfa);
                $remainingCredit -= $applied;

                $debt->paid_amount_fcfa += $applied;
                $debt->remaining_amount_fcfa -= $applied;
                $debt->status = $debt->remaining_amount_fcfa === 0
                    ? Debt::STATUS_PAID
                    : Debt::STATUS_PARTIAL;
                $debt->save();

                $repayment->allocations()->create([
                    'debt_id' => $debt->id,
                    'amount_fcfa' => $applied,
                ]);
            }

            return $repayment->load(['farmer', 'operator', 'allocations.debt']);
        });
    }
}
