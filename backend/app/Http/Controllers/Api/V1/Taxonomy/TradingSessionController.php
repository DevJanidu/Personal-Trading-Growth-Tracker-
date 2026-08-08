<?php

namespace App\Http\Controllers\Api\V1\Taxonomy;

use App\Http\Requests\Taxonomy\StoreTradingSessionRequest;
use App\Http\Requests\Taxonomy\UpdateTradingSessionRequest;
use App\Http\Resources\TradingSessionResource;
use App\Models\TradingSession;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TradingSessionController extends BaseTaxonomyController
{
    protected function resourceClass(): string
    {
        return TradingSessionResource::class;
    }

    protected function userRelation(Request $request): HasMany
    {
        return $request->user()->tradingSessions();
    }

    public function store(StoreTradingSessionRequest $request): TradingSessionResource
    {
        return $this->createFor($request, $request->validated());
    }

    public function update(UpdateTradingSessionRequest $request, TradingSession $tradingSession): TradingSessionResource
    {
        return $this->updateWith($tradingSession, $request->validated());
    }

    public function destroy(Request $request, TradingSession $tradingSession): Response
    {
        return $this->archiveOrDelete($tradingSession, $request);
    }
}
