<?php

namespace Tests\Feature\Strategies;

use App\Models\Strategy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategySetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_setup_to_their_strategy(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson("/api/v1/strategies/{$strategy->uuid}/setups", [
            'name' => 'Asia Low Sweep',
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Asia Low Sweep');
        $this->assertDatabaseHas('strategy_setups', ['strategy_id' => $strategy->id, 'name' => 'Asia Low Sweep']);
    }

    public function test_user_cannot_add_setup_to_another_users_strategy(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->postJson("/api/v1/strategies/{$strategy->uuid}/setups", [
            'name' => 'Should Fail',
        ]);

        $response->assertNotFound();
    }

    public function test_user_can_update_a_setup(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();
        $setup = $strategy->setups()->create(['name' => 'Original']);

        $response = $this->actingAs($user)->putJson("/api/v1/strategies/{$strategy->uuid}/setups/{$setup->id}", [
            'name' => 'Renamed',
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'Renamed');
    }

    public function test_user_can_delete_a_setup(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();
        $setup = $strategy->setups()->create(['name' => 'To Delete']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/strategies/{$strategy->uuid}/setups/{$setup->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('strategy_setups', ['id' => $setup->id]);
    }

    public function test_strategy_detail_includes_setups(): void
    {
        $user = User::factory()->create();
        $strategy = Strategy::factory()->for($user)->create();
        $strategy->setups()->create(['name' => 'Setup A']);

        $response = $this->actingAs($user)->getJson("/api/v1/strategies/{$strategy->uuid}");

        $response->assertOk()->assertJsonCount(1, 'data.setups');
    }
}
