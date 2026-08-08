<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Scopes implicit route-model binding to the authenticated user's own rows,
 * so a record that exists but belongs to someone else resolves as "not
 * found" rather than "found, then forbidden" — API_CONTRACTS.md §1: "a
 * resource that exists but isn't yours returns 404, not 403 (never leak
 * existence of another user's record)". The Policy layer (ARCHITECTURE.md
 * §4) still runs on top of this as defense in depth.
 */
trait ScopedToAuthenticatedUser
{
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('user_id', Auth::id())
            ->first();
    }
}
