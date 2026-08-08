<?php

namespace Tests\Unit\Services;

use App\Models\TradingAccount;
use App\Models\User;
use App\Services\BalanceReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconcile_applies_mixed_transaction_types(): void
    {
        $account = TradingAccount::factory()->for(User::factory())->create([
            'initial_balance' => 1000,
            'current_balance' => 1000,
            'current_equity' => 1000,
        ]);

        $account->transactions()->createMany([
            ['type' => 'deposit', 'amount' => 500, 'transaction_date' => '2026-01-01'],
            ['type' => 'withdrawal', 'amount' => 200, 'transaction_date' => '2026-01-02'],
            ['type' => 'fee', 'amount' => 10, 'transaction_date' => '2026-01-03'],
            ['type' => 'refund', 'amount' => 10, 'transaction_date' => '2026-01-04'],
            ['type' => 'adjustment', 'amount' => -25, 'transaction_date' => '2026-01-05'],
        ]);

        $reconciled = (new BalanceReconciliationService)->reconcile($account);

        // 1000 + 500 - 200 - 10 + 10 - 25 = 1275
        $this->assertSame('1275.0000', $reconciled->current_balance);
        $this->assertSame('1275.0000', $reconciled->current_equity);
    }

    public function test_reconcile_with_no_transactions_leaves_initial_balance(): void
    {
        $account = TradingAccount::factory()->for(User::factory())->create([
            'initial_balance' => 2500,
            'current_balance' => 2500,
            'current_equity' => 2500,
        ]);

        $reconciled = (new BalanceReconciliationService)->reconcile($account);

        $this->assertSame('2500.0000', $reconciled->current_balance);
    }
}
