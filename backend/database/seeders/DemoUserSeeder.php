<?php

namespace Database\Seeders;

use App\Actions\Users\SeedDefaultTaxonomyAction;
use App\Models\Strategy;
use App\Models\TradingAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the personal-use demo trader account (SRS §111 "Seed Data": demo
 * user, demo account, default strategies, default taxonomy values) so the
 * dashboard/sidebar/account-selector can be exercised without manual entry.
 */
class DemoUserSeeder extends Seeder
{
    public function run(SeedDefaultTaxonomyAction $seedDefaultTaxonomy): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@tradegrowth.test'],
            [
                'name' => 'Demo Trader',
                'password' => 'password',
                'email_verified_at' => now(),
                'timezone' => 'America/New_York',
                'default_currency' => 'USD',
                'theme' => 'system',
            ]
        );

        $seedDefaultTaxonomy->execute($user);

        TradingAccount::query()->firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Personal Live'],
            [
                'account_type' => 'personal_live',
                'currency' => 'USD',
                'initial_balance' => 5000,
                'current_balance' => 5000,
                'current_equity' => 5000,
                'status' => 'active',
                'account_created_date' => now()->subMonths(3)->toDateString(),
            ]
        );

        TradingAccount::query()->firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Funded Evaluation #01'],
            [
                'account_type' => 'prop_evaluation',
                'currency' => 'USD',
                'initial_balance' => 100000,
                'current_balance' => 100000,
                'current_equity' => 100000,
                'status' => 'active',
                'max_overall_drawdown_percent' => 10,
                'max_daily_drawdown_percent' => 5,
                'profit_target_percent' => 8,
                'account_created_date' => now()->subMonth()->toDateString(),
            ]
        );

        $this->seedExampleStrategies($user);
    }

    private function seedExampleStrategies(User $user): void
    {
        $liquiditySweep = Strategy::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'liquidity-sweep-mss'],
            [
                'name' => 'Liquidity Sweep + MSS',
                'description' => 'Sweep a liquidity pool, confirm a market structure shift, enter on the retest.',
                'status' => 'active',
                'minimum_rr' => 3,
                'maximum_risk_percent' => 0.5,
            ]
        );

        foreach ([
            'Asia Low Sweep', 'Asia High Sweep', 'Previous Day High Sweep',
            'Previous Day Low Sweep', 'NY Continuation',
        ] as $name) {
            $liquiditySweep->setups()->firstOrCreate(['name' => $name]);
        }

        $orderBlock = Strategy::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'order-block'],
            [
                'name' => 'Order Block',
                'description' => 'Enter on a retest of a validated order block aligned with HTF bias.',
                'status' => 'active',
                'minimum_rr' => 2,
                'maximum_risk_percent' => 0.5,
            ]
        );

        foreach (['Bullish Order Block', 'Bearish Order Block'] as $name) {
            $orderBlock->setups()->firstOrCreate(['name' => $name]);
        }
    }
}
