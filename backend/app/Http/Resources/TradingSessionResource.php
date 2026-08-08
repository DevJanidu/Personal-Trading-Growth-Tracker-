<?php

namespace App\Http\Resources;

use App\Models\TradingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TradingSession */
class TradingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_time_utc' => $this->start_time_utc,
            'end_time_utc' => $this->end_time_utc,
            'status' => $this->status?->value,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
