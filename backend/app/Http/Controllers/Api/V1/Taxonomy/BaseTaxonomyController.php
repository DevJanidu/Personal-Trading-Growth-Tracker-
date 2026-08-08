<?php

namespace App\Http\Controllers\Api\V1\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * Shared CRUD shape for the simple, per-user taxonomy tables (trading
 * sessions, market conditions, entry models, setup grades) per
 * ARCHITECTURE.md §2.1 / DATABASE_SCHEMA.md §12 Decision #2. Concrete
 * controllers supply the resource class + user relation, and implement their
 * own store()/update()/destroy() (Laravel resolves Form Requests and route
 * model bindings by concrete parameter type, so those can't be made generic),
 * delegating to the helpers below to stay thin.
 */
abstract class BaseTaxonomyController extends Controller
{
    /** @return class-string<JsonResource> */
    abstract protected function resourceClass(): string;

    /** The authenticated user's HasMany relation for this taxonomy type. */
    abstract protected function userRelation(Request $request): HasMany;

    public function index(Request $request)
    {
        $items = $this->userRelation($request)
            ->when(
                ! $request->boolean('include_archived'),
                fn ($query) => $query->where('status', 'active'),
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $resourceClass = $this->resourceClass();

        return $resourceClass::collection($items);
    }

    protected function createFor(Request $request, array $validated): JsonResource
    {
        $model = $this->userRelation($request)->create($validated);

        $resourceClass = $this->resourceClass();

        return new $resourceClass($model);
    }

    protected function updateWith(Model $model, array $validated): JsonResource
    {
        $model->update($validated);

        $resourceClass = $this->resourceClass();

        return new $resourceClass($model);
    }

    protected function archiveOrDelete(Model $model, Request $request): Response
    {
        $this->authorize('delete', $model);

        if ($request->boolean('force')) {
            $model->delete();

            return response()->noContent();
        }

        $model->update(['status' => 'archived']);

        return response()->noContent();
    }
}
