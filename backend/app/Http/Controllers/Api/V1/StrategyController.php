<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Strategies\StoreStrategyRequest;
use App\Http\Requests\Strategies\UpdateStrategyRequest;
use App\Http\Resources\StrategyResource;
use App\Models\Strategy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StrategyController extends Controller
{
    public function index(Request $request)
    {
        $strategies = $request->user()->strategies()
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')),
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'),
            )
            ->orderBy('name')
            ->get();

        return StrategyResource::collection($strategies);
    }

    public function store(StoreStrategyRequest $request): StrategyResource
    {
        $strategy = $request->user()->strategies()->create($request->validated());

        return new StrategyResource($strategy);
    }

    public function show(Request $request, Strategy $strategy): StrategyResource
    {
        $this->authorize('view', $strategy);

        return new StrategyResource($strategy->load('setups'));
    }

    public function update(UpdateStrategyRequest $request, Strategy $strategy): StrategyResource
    {
        $strategy->update($request->validated());

        return new StrategyResource($strategy);
    }

    public function destroy(Request $request, Strategy $strategy): Response
    {
        $this->authorize('delete', $strategy);

        if ($request->boolean('force')) {
            if ($strategy->setups()->exists()) {
                abort(409, 'Strategy has setups and cannot be permanently deleted. Remove them first or archive the strategy instead.');
            }

            $strategy->forceDelete();

            return response()->noContent();
        }

        $strategy->update(['status' => 'archived']);
        $strategy->delete();

        return response()->noContent();
    }
}
