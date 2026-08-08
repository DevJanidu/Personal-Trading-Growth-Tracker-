<?php

namespace App\Http\Requests\Taxonomy;

use App\Models\EntryModel;
use Illuminate\Foundation\Http\FormRequest;

class StoreEntryModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EntryModel::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,archived'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
