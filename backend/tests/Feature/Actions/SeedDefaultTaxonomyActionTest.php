<?php

namespace Tests\Feature\Actions;

use App\Actions\Users\SeedDefaultTaxonomyAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises SeedDefaultTaxonomyAction directly (new SeedDefaultTaxonomyAction())
 * rather than through $this->seed()/Artisan db:seed. Artisan's SeedCommand
 * wraps every seeder run in Model::unguard(), which will silently paper over
 * a mass-assignment bug (e.g. a foreign key missing from a model's
 * #[Fillable(...)]) that would otherwise break this action when called from
 * any non-seeder context — the whole point of it being reusable per its own
 * docblock. This test is what actually catches that class of regression.
 */
class SeedDefaultTaxonomyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_seeds_taxonomy_when_called_outside_a_seeder_context(): void
    {
        $user = User::factory()->create();

        (new SeedDefaultTaxonomyAction)->execute($user);

        $this->assertSame(4, $user->tradingSessions()->count());
        $this->assertSame(8, $user->marketConditions()->count());
        $this->assertSame(8, $user->entryModels()->count());
        $this->assertSame(4, $user->setupGrades()->count());

        foreach ($user->tradingSessions()->pluck('user_id') as $userId) {
            $this->assertSame($user->id, $userId);
        }
    }

    public function test_execute_is_idempotent(): void
    {
        $user = User::factory()->create();
        $action = new SeedDefaultTaxonomyAction;

        $action->execute($user);
        $action->execute($user);

        $this->assertSame(4, $user->tradingSessions()->count());
    }
}
