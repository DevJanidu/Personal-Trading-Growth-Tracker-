<?php

namespace App\Http\Requests\Taxonomy;

use App\Models\SetupGrade;
use Illuminate\Foundation\Http\FormRequest;

class StoreSetupGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SetupGrade::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'score_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'score_max' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:score_min'],
            'sort_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', 'in:active,archived'],
        ];
    }
}
