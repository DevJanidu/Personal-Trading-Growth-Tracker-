<?php

namespace App\Http\Requests\Taxonomy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTradingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('trading_session'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'start_time_utc' => ['nullable', 'date_format:H:i'],
            'end_time_utc' => ['nullable', 'date_format:H:i'],
            'status' => ['sometimes', 'in:active,archived'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
