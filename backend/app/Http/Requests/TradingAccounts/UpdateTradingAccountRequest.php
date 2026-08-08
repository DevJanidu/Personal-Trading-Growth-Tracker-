<?php

namespace App\Http\Requests\TradingAccounts;

use App\Enums\AccountType;
use App\Enums\DrawdownCalculationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTradingAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('account'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'account_type' => ['sometimes', Rule::enum(AccountType::class)],
            'broker' => ['nullable', 'string', 'max:255'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'status' => ['sometimes', 'in:active,archived,closed'],
            'max_overall_drawdown_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_daily_drawdown_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'profit_target_percent' => ['nullable', 'numeric', 'min:0'],
            'minimum_trading_days' => ['nullable', 'integer', 'min:0'],
            'maximum_trading_days' => ['nullable', 'integer', 'min:0'],
            'payout_target' => ['nullable', 'numeric', 'min:0'],
            'consistency_rule_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'drawdown_calculation_type' => ['nullable', Rule::enum(DrawdownCalculationType::class)],
            'daily_reset_time' => ['nullable', 'date_format:H:i'],
            'daily_reset_timezone' => ['nullable', 'timezone'],
            'challenge_phase' => ['nullable', 'string', 'max:64'],
            'account_created_date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
