<?php

namespace App\Policies;

use App\Models\StrategySetup;
use App\Models\User;

/**
 * Ownership chain: strategy_setups -> strategy_id -> strategies.user_id
 * (DATABASE_SCHEMA.md §7.2).
 */
class StrategySetupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StrategySetup $strategySetup): bool
    {
        return $strategySetup->strategy->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, StrategySetup $strategySetup): bool
    {
        return $strategySetup->strategy->user_id === $user->id;
    }

    public function delete(User $user, StrategySetup $strategySetup): bool
    {
        return $strategySetup->strategy->user_id === $user->id;
    }
}
