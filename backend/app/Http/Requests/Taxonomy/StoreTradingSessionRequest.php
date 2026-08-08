<?php

namespace App\Http\Requests\Taxonomy;

use App\Models\TradingSession;
use Illuminate\Foundation\Http\FormRequest;

class StoreTradingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TradingSession::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'start_time_utc' => ['nullable', 'date_format:H:i'],
            'end_time_utc' => ['nullable', 'date_format:H:i'],
            'status' => ['sometimes', 'in:active,archived'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
