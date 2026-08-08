<?php

namespace Tests\Feature\Taxonomy;

use App\Models\EntryModel;
use App\Models\MarketCondition;
use App\Models\SetupGrade;
use App\Models\TradingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaxonomyControllersTest extends TestCase
{
    use RefreshDatabase;

    public static function taxonomyEndpoints(): array
    {
        return [
            'trading sessions' => ['trading-sessions', TradingSession::class, 'trading_sessions'],
            'market conditions' => ['market-conditions', MarketCondition::class, 'market_conditions'],
            'entry models' => ['entry-models', EntryModel::class, 'entry_models'],
            'setup grades' => ['setup-grades', SetupGrade::class, 'setup_grades'],
        ];
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_user_can_create_an_item(string $uri, string $modelClass, string $table): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/{$uri}", [
            'name' => 'Custom Value',
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Custom Value');
        $this->assertDatabaseHas($table, ['user_id' => $user->id, 'name' => 'Custom Value']);
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_index_only_returns_the_users_own_active_items(string $uri, string $modelClass): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $modelClass::factory()->for($user)->create(['name' => 'Mine Active', 'status' => 'active']);
        $modelClass::factory()->for($user)->create(['name' => 'Mine Archived', 'status' => 'archived']);
        $modelClass::factory()->for($otherUser)->create(['name' => 'Not Mine', 'status' => 'active']);

        $response = $this->actingAs($user)->getJson("/api/v1/{$uri}");

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Mine Active', $response->json('data.0.name'));
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_index_can_include_archived_items(string $uri, string $modelClass): void
    {
        $user = User::factory()->create();
        $modelClass::factory()->for($user)->create(['status' => 'active']);
        $modelClass::factory()->for($user)->create(['status' => 'archived']);

        $response = $this->actingAs($user)->getJson("/api/v1/{$uri}?include_archived=true");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_user_can_update_their_item(string $uri, string $modelClass): void
    {
        $user = User::factory()->create();
        $item = $modelClass::factory()->for($user)->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->putJson("/api/v1/{$uri}/{$item->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'New Name');
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_user_cannot_update_another_users_item(string $uri, string $modelClass): void
    {
        $user = User::factory()->create();
        $item = $modelClass::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->putJson("/api/v1/{$uri}/{$item->id}", ['name' => 'Hacked']);

        $response->assertNotFound();
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_delete_without_force_archives_instead_of_deleting(string $uri, string $modelClass, string $table): void
    {
        $user = User::factory()->create();
        $item = $modelClass::factory()->for($user)->create(['status' => 'active']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/{$uri}/{$item->id}");

        $response->assertNoContent();
        $this->assertDatabaseHas($table, ['id' => $item->id, 'status' => 'archived']);
    }

    #[DataProvider('taxonomyEndpoints')]
    public function test_delete_with_force_permanently_removes_the_item(string $uri, string $modelClass, string $table): void
    {
        $user = User::factory()->create();
        $item = $modelClass::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/{$uri}/{$item->id}?force=true");

        $response->assertNoContent();
        $this->assertDatabaseMissing($table, ['id' => $item->id]);
    }
}
