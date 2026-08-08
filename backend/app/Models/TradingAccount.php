<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\DrawdownCalculationType;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopedToAuthenticatedUser;
use Database\Factories\TradingAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'account_type', 'broker', 'currency', 'initial_balance', 'current_balance',
    'current_equity', 'status', 'max_overall_drawdown_percent', 'max_daily_drawdown_percent',
    'profit_target_percent', 'minimum_trading_days', 'maximum_trading_days', 'payout_target',
    'consistency_rule_percent', 'drawdown_calculation_type', 'daily_reset_time',
    'daily_reset_timezone', 'challenge_phase', 'account_created_date', 'notes',
])]
class TradingAccount extends Model
{
    /** @use HasFactory<TradingAccountFactory> */
    use HasFactory, HasUuid, ScopedToAuthenticatedUser, SoftDeletes;

    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'status' => AccountStatus::class,
            'drawdown_calculation_type' => DrawdownCalculationType::class,
            'initial_balance' => 'decimal:4',
            'current_balance' => 'decimal:4',
            'current_equity' => 'decimal:4',
            'max_overall_drawdown_percent' => 'decimal:4',
            'max_daily_drawdown_percent' => 'decimal:4',
            'profit_target_percent' => 'decimal:4',
            'payout_target' => 'decimal:4',
            'consistency_rule_percent' => 'decimal:4',
            'account_created_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }
}
