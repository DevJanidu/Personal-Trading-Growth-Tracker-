<?php

namespace App\Http\Requests\Taxonomy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSetupGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('setup_grade'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'score_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'score_max' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:score_min'],
            'sort_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', 'in:active,archived'],
        ];
    }
}
