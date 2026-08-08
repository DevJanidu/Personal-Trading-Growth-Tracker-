<?php

namespace App\Http\Requests\StrategySetups;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStrategySetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('setup'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,archived'],
        ];
    }
}
