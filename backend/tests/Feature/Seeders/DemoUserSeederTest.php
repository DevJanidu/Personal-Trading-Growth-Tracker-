<?php

namespace Tests\Feature\Seeders;

use App\Models\Strategy;
use App\Models\TradingAccount;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_user_with_taxonomy_accounts_and_strategies(): void
    {
        $this->seed(DemoUserSeeder::class);

        $user = User::query()->where('email', 'demo@tradegrowth.test')->firstOrFail();

        $this->assertSame(4, $user->tradingSessions()->count());
        $this->assertSame(8, $user->marketConditions()->count());
        $this->assertSame(8, $user->entryModels()->count());
        $this->assertSame(4, $user->setupGrades()->count());

        $this->assertSame(2, TradingAccount::query()->where('user_id', $user->id)->count());
        $this->assertSame(2, Strategy::query()->where('user_id', $user->id)->count());

        $liquiditySweep = Strategy::query()->where('user_id', $user->id)->where('slug', 'liquidity-sweep-mss')->firstOrFail();
        $this->assertSame(5, $liquiditySweep->setups()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(DemoUserSeeder::class);
        $this->seed(DemoUserSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'demo@tradegrowth.test')->count());
        $user = User::query()->where('email', 'demo@tradegrowth.test')->firstOrFail();
        $this->assertSame(4, $user->tradingSessions()->count());
    }
}
