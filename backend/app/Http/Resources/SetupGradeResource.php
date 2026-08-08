<?php

namespace App\Http\Resources;

use App\Models\SetupGrade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SetupGrade */
class SetupGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'score_min' => $this->score_min,
            'score_max' => $this->score_max,
            'sort_order' => $this->sort_order,
            'status' => $this->status?->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
