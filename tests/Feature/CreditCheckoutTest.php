<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_checkout_applies_interest_and_creates_debt(): void
    {
        $this->seed();

        $operator = User::query()->where('role', User::ROLE_OPERATOR)->firstOrFail();
        $farmer = Farmer::query()->where('identifier', 'FCI-0001')->firstOrFail();
        $product = Product::query()->where('price_fcfa', 12000)->firstOrFail();

        Setting::query()->where('key', 'credit_interest_rate')->update(['value' => '0.30']);

        $response = $this->actingAs($operator)->postJson('/api/transactions', [
            'farmer_id' => $farmer->id,
            'payment_method' => 'credit',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_fcfa', 12000)
            ->assertJsonPath('data.interest_amount_fcfa', 3600)
            ->assertJsonPath('data.credited_total_fcfa', 15600);

        $this->assertDatabaseHas('debts', [
            'farmer_id' => $farmer->id,
            'original_amount_fcfa' => 15600,
            'remaining_amount_fcfa' => 15600,
            'status' => Debt::STATUS_OPEN,
        ]);
    }

    public function test_credit_checkout_is_blocked_when_limit_would_be_exceeded(): void
    {
        $this->seed();

        $operator = User::query()->where('role', User::ROLE_OPERATOR)->firstOrFail();
        $farmer = Farmer::query()->where('identifier', 'FCI-0003')->firstOrFail();
        $product = Product::query()->where('price_fcfa', 24000)->firstOrFail();

        $response = $this->actingAs($operator)->postJson('/api/transactions', [
            'farmer_id' => $farmer->id,
            'payment_method' => 'credit',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('credit_limit');
    }
}
