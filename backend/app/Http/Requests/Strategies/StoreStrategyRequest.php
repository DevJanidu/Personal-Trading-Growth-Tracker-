<?php

namespace App\Http\Requests\Strategies;

use App\Models\Strategy;
use Illuminate\Foundation\Http\FormRequest;

class StoreStrategyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Strategy::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,archived'],
            'preferred_markets' => ['nullable', 'array'],
            'preferred_markets.*' => ['string'],
            'preferred_pairs' => ['nullable', 'array'],
            'preferred_pairs.*' => ['string'],
            'preferred_sessions' => ['nullable', 'array'],
            'preferred_sessions.*' => ['integer'],
            'preferred_timeframes' => ['nullable', 'array'],
            'preferred_timeframes.*' => ['string'],
            'minimum_rr' => ['nullable', 'numeric', 'min:0'],
            'maximum_risk_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'required_confirmations' => ['nullable', 'string'],
            'invalidation_conditions' => ['nullable', 'string'],
            'entry_model_notes' => ['nullable', 'string'],
            'trade_management_rules' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
