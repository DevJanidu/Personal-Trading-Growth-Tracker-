<?php

namespace App\Http\Requests\AccountTransactions;

use App\Enums\AccountTransactionType;
use App\Models\AccountTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AccountTransaction::class);
    }

    public function rules(): array
    {
        // Every type except `adjustment` moves the balance in a fixed direction
        // (App\Enums\AccountTransactionType::defaultSign), so its stored amount
        // is always positive; only `adjustment` carries its own sign.
        $isAdjustment = $this->input('type') === AccountTransactionType::Adjustment->value;

        return [
            'type' => ['required', Rule::enum(AccountTransactionType::class)],
            'amount' => $isAdjustment
                ? ['required', 'numeric', 'not_in:0']
                : ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
