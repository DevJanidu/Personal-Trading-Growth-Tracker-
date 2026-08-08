<?php

namespace App\Policies;

use App\Models\AccountTransaction;
use App\Models\User;

/**
 * Ownership chain: account_transactions -> trading_account_id -> trading_accounts.user_id
 * (DATABASE_SCHEMA.md §3.2 "Ownership path").
 */
class AccountTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AccountTransaction $accountTransaction): bool
    {
        return $accountTransaction->tradingAccount->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AccountTransaction $accountTransaction): bool
    {
        return $accountTransaction->tradingAccount->user_id === $user->id;
    }

    public function delete(User $user, AccountTransaction $accountTransaction): bool
    {
        return $accountTransaction->tradingAccount->user_id === $user->id;
    }
}
