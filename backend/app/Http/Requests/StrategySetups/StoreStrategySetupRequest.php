<?php

namespace App\Http\Requests\StrategySetups;

use App\Models\StrategySetup;
use Illuminate\Foundation\Http\FormRequest;

class StoreStrategySetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', StrategySetup::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,archived'],
        ];
    }
}
