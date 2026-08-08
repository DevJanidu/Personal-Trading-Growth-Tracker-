<?php

namespace Tests\Feature\Strategies;

use App\Models\Strategy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_strategy_with_auto_generated_slug(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/strategies', [
            'name' => 'Liquidity Sweep + MSS',
            'minimum_rr' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Liquidity Sweep + MSS')
            ->assertJsonPath('data.slug', 'liquidity-sweep-mss');
    }

    public function test_user_only_sees_their_own_strategies(): void
    {
        $user = User::factory()->create();
        Strategy::factory()->for($user)->create(['name' => 'Mine']);
        Strategy::factory()->for(User::factory())->create(['name' => 'Not Mine']);

        $response = $this->actingAs($user)->getJson('/api/v1/strategies');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_user_can_update_their_strategy(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();

        $response = $this->actingAs($user)->putJson("/api/v1/strategies/{$strategy->uuid}", [
            'description' => 'Updated description',
        ]);

        $response->assertOk()->assertJsonPath('data.description', 'Updated description');
    }

    public function test_user_cannot_access_another_users_strategy(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for(User::factory())->create();

        $this->actingAs($user)->getJson("/api/v1/strategies/{$strategy->uuid}")->assertNotFound();
        $this->actingAs($user)->putJson("/api/v1/strategies/{$strategy->uuid}", ['name' => 'x'])->assertNotFound();
    }

    public function test_delete_without_force_archives_and_soft_deletes(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create(['status' => 'active']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/strategies/{$strategy->uuid}");

        $response->assertNoContent();
        $this->assertSoftDeleted('strategies', ['id' => $strategy->id]);
    }

    public function test_force_delete_is_blocked_when_setups_exist(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();
        $strategy->setups()->create(['name' => 'Asia Low Sweep']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/strategies/{$strategy->uuid}?force=true");

        $response->assertStatus(409);
        $this->assertDatabaseHas('strategies', ['id' => $strategy->id]);
    }

    public function test_force_delete_succeeds_when_no_setups_exist(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/strategies/{$strategy->uuid}?force=true");

        $response->assertNoContent();
        $this->assertDatabaseMissing('strategies', ['id' => $strategy->id]);
    }
}
