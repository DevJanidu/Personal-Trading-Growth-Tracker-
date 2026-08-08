<?php

namespace App\Services;

use App\Enums\AccountTransactionType;
use App\Models\TradingAccount;

/**
 * Recomputes a trading account's current_balance/current_equity from its
 * initial_balance plus the signed effect of its account_transactions
 * (ARCHITECTURE.md §2.1 — a cross-cutting Service, not an Action, since it is
 * invoked from multiple write paths: transaction create/update/delete now,
 * and trade close in Phase 1d/2 per IMPLEMENTATION_PLAN.md).
 *
 * Trades are not implemented yet (Phase 1 scope), so for now the balance is
 * purely deposits/withdrawals/fees/refunds/profit-splits/adjustments applied
 * on top of the account's initial_balance — trading P&L will extend this once
 * the trades table exists.
 */
class BalanceReconciliationService
{
    public function reconcile(TradingAccount $account): TradingAccount
    {
        $netTransactionEffect = $account->transactions()
            ->get()
            ->sum(fn ($transaction) => $this->signedEffect($transaction->type, (float) $transaction->amount));

        $balance = round((float) $account->initial_balance + $netTransactionEffect, 4);

        $account->forceFill([
            'current_balance' => $balance,
            'current_equity' => $balance,
        ])->save();

        return $account->refresh();
    }

    private function signedEffect(AccountTransactionType $type, float $amount): float
    {
        $sign = $type->defaultSign();

        // `adjustment` carries its own sign on the stored amount (positive or negative).
        return $sign === null ? $amount : $sign * abs($amount);
    }
}
