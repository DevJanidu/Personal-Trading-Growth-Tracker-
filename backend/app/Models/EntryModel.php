<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use App\Models\Concerns\ScopedToAuthenticatedUser;
use Database\Factories\EntryModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'description', 'status', 'sort_order'])]
class EntryModel extends Model
{
    /** @use HasFactory<EntryModelFactory> */
    use HasFactory, ScopedToAuthenticatedUser;

    protected function casts(): array
    {
        return [
            'status' => TaxonomyStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
