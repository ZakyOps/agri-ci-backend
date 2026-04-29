<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepaymentFifoTest extends TestCase
{
    use RefreshDatabase;

    public function test_repayment_settles_oldest_debt_first_and_supports_partial_payment(): void
    {
        $this->seed();

        $operator = User::query()->where('role', User::ROLE_OPERATOR)->firstOrFail();
        $farmer = Farmer::query()->where('identifier', 'FCI-0002')->firstOrFail();
        $debts = Debt::query()->where('farmer_id', $farmer->id)->orderBy('id')->get();

        $response = $this->actingAs($operator)->postJson('/api/repayments', [
            'farmer_id' => $farmer->id,
            'commodity_kg' => 20,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.value_fcfa', 20000)
            ->assertJsonCount(2, 'data.allocations');

        $this->assertDatabaseHas('debts', [
            'id' => $debts[0]->id,
            'remaining_amount_fcfa' => 0,
            'status' => Debt::STATUS_PAID,
        ]);

        $this->assertDatabaseHas('debts', [
            'id' => $debts[1]->id,
            'paid_amount_fcfa' => 7000,
            'remaining_amount_fcfa' => 19000,
            'status' => Debt::STATUS_PARTIAL,
        ]);
    }
}
