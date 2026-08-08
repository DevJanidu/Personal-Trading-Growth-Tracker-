<?php

namespace App\Http\Controllers\Api\V1\Taxonomy;

use App\Http\Requests\Taxonomy\StoreEntryModelRequest;
use App\Http\Requests\Taxonomy\UpdateEntryModelRequest;
use App\Http\Resources\EntryModelResource;
use App\Models\EntryModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EntryModelController extends BaseTaxonomyController
{
    protected function resourceClass(): string
    {
        return EntryModelResource::class;
    }

    protected function userRelation(Request $request): HasMany
    {
        return $request->user()->entryModels();
    }

    public function store(StoreEntryModelRequest $request): EntryModelResource
    {
        return $this->createFor($request, $request->validated());
    }

    public function update(UpdateEntryModelRequest $request, EntryModel $entryModel): EntryModelResource
    {
        return $this->updateWith($entryModel, $request->validated());
    }

    public function destroy(Request $request, EntryModel $entryModel): Response
    {
        return $this->archiveOrDelete($entryModel, $request);
    }
}
