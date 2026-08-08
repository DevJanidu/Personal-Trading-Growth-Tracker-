<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use App\Models\Concerns\ScopedToAuthenticatedUser;
use Database\Factories\SetupGradeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'description', 'score_min', 'score_max', 'sort_order', 'status'])]
class SetupGrade extends Model
{
    /** @use HasFactory<SetupGradeFactory> */
    use HasFactory, ScopedToAuthenticatedUser;

    protected function casts(): array
    {
        return [
            'status' => TaxonomyStatus::class,
            'score_min' => 'decimal:4',
            'score_max' => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
