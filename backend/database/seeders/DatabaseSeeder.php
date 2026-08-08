<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Note: deliberately does NOT use WithoutModelEvents — TradingAccount/Strategy
     * rely on their `creating` model event to generate a uuid (App\Models\Concerns\HasUuid)
     * and Strategy additionally auto-slugs from its name in that same event.
     */
    public function run(): void
    {
        $this->call(DemoUserSeeder::class);
    }
}
