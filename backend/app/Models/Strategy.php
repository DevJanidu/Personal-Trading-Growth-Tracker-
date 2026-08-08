<?php

namespace App\Models;

use App\Enums\StrategyStatus;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopedToAuthenticatedUser;
use Database\Factories\StrategyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'description', 'status', 'preferred_markets', 'preferred_pairs',
    'preferred_sessions', 'preferred_timeframes', 'minimum_rr', 'maximum_risk_percent',
    'required_confirmations', 'invalidation_conditions', 'entry_model_notes',
    'trade_management_rules', 'notes',
])]
class Strategy extends Model
{
    /** @use HasFactory<StrategyFactory> */
    use HasFactory, HasUuid, ScopedToAuthenticatedUser, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => StrategyStatus::class,
            'preferred_markets' => 'array',
            'preferred_pairs' => 'array',
            'preferred_sessions' => 'array',
            'preferred_timeframes' => 'array',
            'minimum_rr' => 'decimal:4',
            'maximum_risk_percent' => 'decimal:4',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $strategy): void {
            if (empty($strategy->slug)) {
                $strategy->slug = Str::slug($strategy->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setups(): HasMany
    {
        return $this->hasMany(StrategySetup::class);
    }
}
