<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates a UUID on creation for models that expose a stable, non-sequential
 * external identifier (DATABASE_SCHEMA.md §12 Decision #9). Generated in PHP
 * rather than via a DB-level default so behavior is identical across every
 * database driver (Postgres in production, SQLite in tests).
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
