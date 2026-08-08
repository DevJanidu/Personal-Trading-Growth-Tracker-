<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Shared ownership policy for models with a direct user_id column
 * (ARCHITECTURE.md §4: every taxonomy/account/strategy table's ownership
 * path terminates at users.id).
 */
trait OwnedDirectlyByUser
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $model): bool
    {
        return $model->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, $model): bool
    {
        return $model->user_id === $user->id;
    }

    public function delete(User $user, $model): bool
    {
        return $model->user_id === $user->id;
    }

    public function restore(User $user, $model): bool
    {
        return $model->user_id === $user->id;
    }

    public function forceDelete(User $user, $model): bool
    {
        return $model->user_id === $user->id;
    }
}
