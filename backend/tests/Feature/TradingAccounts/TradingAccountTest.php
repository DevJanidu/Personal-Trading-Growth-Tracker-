<?php

namespace Tests\Feature\TradingAccounts;

use App\Models\TradingAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradingAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_trading_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', [
            'name' => 'Personal Live',
            'account_type' => 'personal_live',
            'currency' => 'USD',
            'initial_balance' => 5000,
            'account_created_date' => '2026-01-01',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Personal Live')
            ->assertJsonPath('data.current_balance', '5000.0000')
            ->assertJsonPath('data.current_equity', '5000.0000');

        $this->assertDatabaseHas('trading_accounts', [
            'user_id' => $user->id,
            'name' => 'Personal Live',
        ]);
    }

    public function test_creating_account_requires_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'account_type', 'currency', 'initial_balance', 'account_created_date']);
    }

    public function test_user_only_sees_their_own_accounts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TradingAccount::factory()->for($user)->create(['name' => 'Mine']);
        TradingAccount::factory()->for($otherUser)->create(['name' => 'Not Mine']);

        $response = $this->actingAs($user)->getJson('/api/v1/accounts');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Mine', $response->json('data.0.name'));
    }

    public function test_user_cannot_view_another_users_account(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->getJson("/api/v1/accounts/{$account->uuid}");

        $response->assertNotFound();
    }

    public function test_user_can_update_their_account(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->putJson("/api/v1/accounts/{$account->uuid}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'New Name');
    }

    public function test_user_cannot_update_another_users_account(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->putJson("/api/v1/accounts/{$account->uuid}", [
            'name' => 'Hacked',
        ]);

        $response->assertNotFound();
    }

    public function test_user_can_soft_delete_their_account(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/accounts/{$account->uuid}");

        $response->assertNoContent();
        $this->assertSoftDeleted('trading_accounts', ['id' => $account->id]);
    }

    public function test_force_delete_is_blocked_when_transactions_exist(): void
    {
        $user = User::factory()->create();
        $account = TradingAccount::factory()->for($user)->create();
        $account->transactions()->create([
            'type' => 'deposit',
            'amount' => 100,
            'transaction_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/accounts/{$account->uuid}?force=true");

        $response->assertStatus(409);
        $this->assertDatabaseHas('trading_accounts', ['id' => $account->id]);
    }
}
