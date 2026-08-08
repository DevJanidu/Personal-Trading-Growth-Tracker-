<?php

namespace App\Http\Resources;

use App\Models\Strategy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Strategy */
class StrategyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status?->value,
            'preferred_markets' => $this->preferred_markets,
            'preferred_pairs' => $this->preferred_pairs,
            'preferred_sessions' => $this->preferred_sessions,
            'preferred_timeframes' => $this->preferred_timeframes,
            'minimum_rr' => $this->minimum_rr,
            'maximum_risk_percent' => $this->maximum_risk_percent,
            'required_confirmations' => $this->required_confirmations,
            'invalidation_conditions' => $this->invalidation_conditions,
            'entry_model_notes' => $this->entry_model_notes,
            'trade_management_rules' => $this->trade_management_rules,
            'notes' => $this->notes,
            'setups' => StrategySetupResource::collection($this->whenLoaded('setups')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
