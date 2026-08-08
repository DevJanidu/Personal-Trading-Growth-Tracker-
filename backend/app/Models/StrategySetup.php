<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\StrategySetupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable(['name', 'description', 'status'])]
class StrategySetup extends Model
{
    /** @use HasFactory<StrategySetupFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TaxonomyStatus::class,
        ];
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }

    /**
     * Ownership is via the parent strategy, not a direct user_id column
     * (DATABASE_SCHEMA.md §7.2), so route-binding scoping (see
     * App\Models\Concerns\ScopedToAuthenticatedUser) is expressed as a
     * whereHas rather than a simple where().
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->whereHas('strategy', fn ($query) => $query->where('user_id', Auth::id()))
            ->first();
    }
}
