<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function checkout(Farmer $farmer, int $operatorId, array $items, string $paymentMethod): Transaction
    {
        return DB::transaction(function () use ($farmer, $operatorId, $items, $paymentMethod) {
            $products = Product::query()
                ->whereIn('id', collect($items)->pluck('product_id'))
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Le produit {$item['product_id']} n'est pas disponible.",
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $lineTotal = $product->price_fcfa * $quantity;
                $total += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price_fcfa' => $product->price_fcfa,
                    'line_total_fcfa' => $lineTotal,
                ];
            }

            $interestRate = 0.0;
            $interestAmount = 0;
            $creditedTotal = $total;
            $status = 'closed';

            if ($paymentMethod === Transaction::PAYMENT_CREDIT) {
                $interestRate = (float) Setting::value('credit_interest_rate', 0.30);
                $creditedTotal = (int) round($total * (1 + $interestRate));
                $interestAmount = $creditedTotal - $total;
                $status = 'open';

                $outstanding = (int) Debt::query()
                    ->where('farmer_id', $farmer->id)
                    ->where('status', '!=', Debt::STATUS_PAID)
                    ->lockForUpdate()
                    ->sum('remaining_amount_fcfa');

                if (($outstanding + $creditedTotal) > $farmer->credit_limit_fcfa) {
                    throw ValidationException::withMessages([
                        'credit_limit' => 'La limite de crédit de cet agriculteur serait dépassée.',
                    ]);
                }
            }

            $transaction = Transaction::query()->create([
                'farmer_id' => $farmer->id,
                'operator_id' => $operatorId,
                'total_fcfa' => $total,
                'payment_method' => $paymentMethod,
                'interest_rate' => $interestRate,
                'interest_amount_fcfa' => $interestAmount,
                'credited_total_fcfa' => $creditedTotal,
                'status' => $status,
            ]);

            $transaction->items()->createMany($lines);

            if ($paymentMethod === Transaction::PAYMENT_CREDIT) {
                Debt::query()->create([
                    'transaction_id' => $transaction->id,
                    'farmer_id' => $farmer->id,
                    'original_amount_fcfa' => $creditedTotal,
                    'paid_amount_fcfa' => 0,
                    'remaining_amount_fcfa' => $creditedTotal,
                    'status' => Debt::STATUS_OPEN,
                ]);
            }

            return $transaction->load(['farmer', 'operator', 'items.product', 'debt']);
        });
    }
}
