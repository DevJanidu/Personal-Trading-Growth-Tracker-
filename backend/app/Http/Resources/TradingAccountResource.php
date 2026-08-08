<?php

namespace App\Http\Resources;

use App\Models\TradingAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TradingAccount */
class TradingAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'account_type' => $this->account_type?->value,
            'broker' => $this->broker,
            'currency' => $this->currency,
            'initial_balance' => $this->initial_balance,
            'current_balance' => $this->current_balance,
            'current_equity' => $this->current_equity,
            // growth_percent / current_*_drawdown_percent are computed from trade history
            // (ANALYTICS_FORMULAS.md §9-10) — not available until the Phase 2 analytics
            // engine lands (IMPLEMENTATION_PLAN.md Phase 2), intentionally omitted here.
            'status' => $this->status?->value,
            'max_overall_drawdown_percent' => $this->max_overall_drawdown_percent,
            'max_daily_drawdown_percent' => $this->max_daily_drawdown_percent,
            'profit_target_percent' => $this->profit_target_percent,
            'minimum_trading_days' => $this->minimum_trading_days,
            'maximum_trading_days' => $this->maximum_trading_days,
            'payout_target' => $this->payout_target,
            'consistency_rule_percent' => $this->consistency_rule_percent,
            'drawdown_calculation_type' => $this->drawdown_calculation_type?->value,
            'daily_reset_time' => $this->daily_reset_time,
            'daily_reset_timezone' => $this->daily_reset_timezone,
            'challenge_phase' => $this->challenge_phase,
            'account_created_date' => $this->account_created_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
