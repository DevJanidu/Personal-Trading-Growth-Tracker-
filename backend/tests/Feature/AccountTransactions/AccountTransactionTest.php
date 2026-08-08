<?php

namespace Tests\Feature\AccountTransactions;

use App\Models\TradingAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_increases_current_balance(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create([
            'initial_balance' => 5000,
            'current_balance' => 5000,
            'current_equity' => 5000,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/accounts/{$account->uuid}/transactions", [
            'type' => 'deposit',
            'amount' => 500,
            'transaction_date' => '2026-02-01',
        ]);

        $response->assertCreated();

        $this->assertSame('5500.0000', $account->refresh()->current_balance);
    }

    public function test_withdrawal_decreases_current_balance(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create([
            'initial_balance' => 5000,
            'current_balance' => 5000,
            'current_equity' => 5000,
        ]);

        $this->actingAs($user)->postJson("/api/v1/accounts/{$account->uuid}/transactions", [
            'type' => 'withdrawal',
            'amount' => 1000,
            'transaction_date' => '2026-02-01',
        ])->assertCreated();

        $this->assertSame('4000.0000', $account->refresh()->current_balance);
    }

    public function test_adjustment_can_carry_a_negative_amount(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create([
            'initial_balance' => 5000,
            'current_balance' => 5000,
            'current_equity' => 5000,
        ]);

        $this->actingAs($user)->postJson("/api/v1/accounts/{$account->uuid}/transactions", [
            'type' => 'adjustment',
            'amount' => -50,
            'transaction_date' => '2026-02-01',
        ])->assertCreated();

        $this->assertSame('4950.0000', $account->refresh()->current_balance);
    }

    public function test_non_adjustment_amount_must_be_positive(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson("/api/v1/accounts/{$account->uuid}/transactions", [
            'type' => 'deposit',
            'amount' => -50,
            'transaction_date' => '2026-02-01',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('amount');
    }

    public function test_user_cannot_create_transaction_for_another_users_account(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->postJson("/api/v1/accounts/{$account->uuid}/transactions", [
            'type' => 'deposit',
            'amount' => 100,
            'transaction_date' => '2026-02-01',
        ]);

        $response->assertNotFound();
    }

    public function test_transactions_are_listed_for_the_account(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create();
        $account->transactions()->createMany([
            ['type' => 'deposit', 'amount' => 100, 'transaction_date' => '2026-01-01'],
            ['type' => 'deposit', 'amount' => 200, 'transaction_date' => '2026-01-02'],
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/accounts/{$account->uuid}/transactions");

        $response->assertOk()->assertJsonCount(2, 'data');
    }
}
