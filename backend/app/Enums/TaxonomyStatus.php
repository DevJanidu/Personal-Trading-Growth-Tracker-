<?php

namespace App\Enums;

/**
 * Shared status enum for the simple, user-managed taxonomy tables
 * (trading_sessions, market_conditions, entry_models, setup_grades,
 * strategy_setups) per DATABASE_SCHEMA.md §11.
 */
enum TaxonomyStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
