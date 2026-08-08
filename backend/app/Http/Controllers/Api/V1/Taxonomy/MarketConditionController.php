<?php

namespace App\Http\Controllers\Api\V1\Taxonomy;

use App\Http\Requests\Taxonomy\StoreMarketConditionRequest;
use App\Http\Requests\Taxonomy\UpdateMarketConditionRequest;
use App\Http\Resources\MarketConditionResource;
use App\Models\MarketCondition;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MarketConditionController extends BaseTaxonomyController
{
    protected function resourceClass(): string
    {
        return MarketConditionResource::class;
    }

    protected function userRelation(Request $request): HasMany
    {
        return $request->user()->marketConditions();
    }

    public function store(StoreMarketConditionRequest $request): MarketConditionResource
    {
        return $this->createFor($request, $request->validated());
    }

    public function update(UpdateMarketConditionRequest $request, MarketCondition $marketCondition): MarketConditionResource
    {
        return $this->updateWith($marketCondition, $request->validated());
    }

    public function destroy(Request $request, MarketCondition $marketCondition): Response
    {
        return $this->archiveOrDelete($marketCondition, $request);
    }
}
