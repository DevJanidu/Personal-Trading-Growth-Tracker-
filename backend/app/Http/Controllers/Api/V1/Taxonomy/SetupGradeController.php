<?php

namespace App\Http\Controllers\Api\V1\Taxonomy;

use App\Http\Requests\Taxonomy\StoreSetupGradeRequest;
use App\Http\Requests\Taxonomy\UpdateSetupGradeRequest;
use App\Http\Resources\SetupGradeResource;
use App\Models\SetupGrade;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SetupGradeController extends BaseTaxonomyController
{
    protected function resourceClass(): string
    {
        return SetupGradeResource::class;
    }

    protected function userRelation(Request $request): HasMany
    {
        return $request->user()->setupGrades();
    }

    public function store(StoreSetupGradeRequest $request): SetupGradeResource
    {
        return $this->createFor($request, $request->validated());
    }

    public function update(UpdateSetupGradeRequest $request, SetupGrade $setupGrade): SetupGradeResource
    {
        return $this->updateWith($setupGrade, $request->validated());
    }

    public function destroy(Request $request, SetupGrade $setupGrade): Response
    {
        return $this->archiveOrDelete($setupGrade, $request);
    }
}
