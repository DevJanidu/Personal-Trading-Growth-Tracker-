<?php

namespace App\Actions\Users;

use App\Models\User;

/**
 * Seeds a user's per-user taxonomy tables with the SRS §112 default values.
 * Idempotent (firstOrCreate keyed on the unique (user_id, name) constraint),
 * so it is safe to call from both a database seeder and, later, a real
 * registration flow (IMPLEMENTATION_PLAN.md Phase 1c).
 *
 * Creation goes through each $user->hasMany(...) relation (not
 * TradingSession::query() etc.) so `user_id` is set via the relationship's
 * own foreign-key assignment rather than mass assignment — none of the
 * taxonomy models list `user_id` in their #[Fillable(...)] (it must never be
 * settable from a request payload), and going through the relation is what
 * lets this run correctly outside of Artisan's `db:seed` command, which is
 * the only context that mass-assigns fillable-guarded attributes for free
 * (SeedCommand wraps every seeder in Model::unguard()).
 */
class SeedDefaultTaxonomyAction
{
    public function execute(User $user): void
    {
        $this->seedTradingSessions($user);
        $this->seedMarketConditions($user);
        $this->seedEntryModels($user);
        $this->seedSetupGrades($user);
    }

    private function seedTradingSessions(User $user): void
    {
        $sessions = [
            ['name' => 'Asia', 'start_time_utc' => '00:00', 'end_time_utc' => '09:00'],
            ['name' => 'London', 'start_time_utc' => '07:00', 'end_time_utc' => '16:00'],
            ['name' => 'New York', 'start_time_utc' => '12:00', 'end_time_utc' => '21:00'],
            ['name' => 'London/New York Overlap', 'start_time_utc' => '12:00', 'end_time_utc' => '16:00'],
        ];

        foreach ($sessions as $index => $session) {
            $user->tradingSessions()->firstOrCreate(
                ['name' => $session['name']],
                [
                    'start_time_utc' => $session['start_time_utc'],
                    'end_time_utc' => $session['end_time_utc'],
                    'status' => 'active',
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function seedMarketConditions(User $user): void
    {
        $conditions = [
            'Trending', 'Ranging', 'Reversal', 'Consolidation',
            'High Volatility', 'Low Volatility', 'News-driven', 'Custom',
        ];

        foreach ($conditions as $index => $name) {
            $user->marketConditions()->firstOrCreate(
                ['name' => $name],
                ['status' => 'active', 'sort_order' => $index]
            );
        }
    }

    private function seedEntryModels(User $user): void
    {
        $models = [
            'FVG', 'Order Block', 'Breaker', 'Retest',
            'Fibonacci', 'Market Structure Shift', 'Support/Resistance', 'Custom',
        ];

        foreach ($models as $index => $name) {
            $user->entryModels()->firstOrCreate(
                ['name' => $name],
                ['status' => 'active', 'sort_order' => $index]
            );
        }
    }

    private function seedSetupGrades(User $user): void
    {
        $grades = [
            ['name' => 'A+', 'score_min' => 90, 'score_max' => 100],
            ['name' => 'A', 'score_min' => 75, 'score_max' => 89.9999],
            ['name' => 'B', 'score_min' => 50, 'score_max' => 74.9999],
            ['name' => 'C', 'score_min' => 0, 'score_max' => 49.9999],
        ];

        foreach ($grades as $index => $grade) {
            $user->setupGrades()->firstOrCreate(
                ['name' => $grade['name']],
                [
                    'score_min' => $grade['score_min'],
                    'score_max' => $grade['score_max'],
                    'sort_order' => $index,
                    'status' => 'active',
                ]
            );
        }
    }
}
