<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StrategySetups\StoreStrategySetupRequest;
use App\Http\Requests\StrategySetups\UpdateStrategySetupRequest;
use App\Http\Resources\StrategySetupResource;
use App\Models\Strategy;
use App\Models\StrategySetup;
use Illuminate\Http\Response;

class StrategySetupController extends Controller
{
    public function index(Strategy $strategy)
    {
        $this->authorize('view', $strategy);

        return StrategySetupResource::collection($strategy->setups()->orderBy('name')->get());
    }

    public function store(StoreStrategySetupRequest $request, Strategy $strategy): StrategySetupResource
    {
        $this->authorize('view', $strategy);

        $setup = $strategy->setups()->create($request->validated());

        return new StrategySetupResource($setup);
    }

    public function update(UpdateStrategySetupRequest $request, Strategy $strategy, StrategySetup $setup): StrategySetupResource
    {
        $setup->update($request->validated());

        return new StrategySetupResource($setup);
    }

    public function destroy(Strategy $strategy, StrategySetup $setup): Response
    {
        $this->authorize('delete', $setup);

        $setup->delete();

        return response()->noContent();
    }
}
