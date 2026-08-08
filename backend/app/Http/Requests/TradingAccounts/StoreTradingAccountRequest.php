<?php

namespace App\Http\Requests\TradingAccounts;

use App\Enums\AccountType;
use App\Enums\DrawdownCalculationType;
use App\Models\TradingAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTradingAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TradingAccount::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'broker' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
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
            'account_created_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
