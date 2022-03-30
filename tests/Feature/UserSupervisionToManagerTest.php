<?php

namespace Tests\Feature;

use App\Courses;
use App\User;
use App\UserSupervision;
use App\UserSupervisionStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserSupervisionToManagerTest extends TestCase
{
    use DatabaseTransactions;

    private const FACTORY_COUNT = 3;

    public function setUp(): void
    {
        parent::setUp();

        for ($i = 1; $i < self::FACTORY_COUNT; $i++) {
            User::factory()->create();
            Courses::factory()->create();
            UserSupervisionStatus::factory()->create();
            UserSupervision::factory()->create();
        }
    }

    /**
     * @see UsersSupervisionsToManagersController::index()
     */
    public function testIndex(): void
    {
        /**
         * @var UserSupervision $model
         */

        $model = UserSupervision::take(1)->first();
        $id = $model->getKey();

        $relatedIds = User::take(self::FACTORY_COUNT)->pluck((new User)->getKeyName());

        $model->managers()->attach($relatedIds);

        $response = $this->get("/api/admin/users-supervisions/$id/managers");

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->has('items.0', function ($json){
                        $json
                            ->whereAllType($this->getSchemaAttributeTypes('Users'))
                            ->etc();
                    })
                    ->has('total');
            })
            ->assertJson([
                'items' => $model->managers()->get()->toArray(),
                'total' => $model->managers()->count()
            ]);
    }

    /**
     * @see UsersSupervisionsToManagersController::store()
     */
    public function testStore():void
    {
        /**
         * @var UserSupervision $model
         */

        $model = UserSupervision::take(1)->first();
        $id = $model->getKey();

        $relatedIds = User::take(self::FACTORY_COUNT)->pluck((new User)->getKeyName());

        $response = $this->post("/api/admin/users-supervisions/$id/managers", [
            'ids' => $relatedIds->toArray()
        ]);

        $response->assertNoContent();

        $relatedIds->each(function ($relatedId) use ($model, $id) {
            $this->assertDatabaseHas('users_supervisions_to_managers', [
                $model->managers()->getForeignPivotKeyName() => $id,
                $model->managers()->getRelatedPivotKeyName() => $relatedId
            ]);
        });

        $response = $this->post("/api/admin/users-supervisions/$id/managers", [
            'ids' => [$relatedIds->first()],
            'detach' => true
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('users_supervisions_to_managers', [
            $model->managers()->getForeignPivotKeyName() => $id,
            $model->managers()->getRelatedPivotKeyName() => $relatedIds->first()
        ]);

        $this->assertDatabaseCount('users_supervisions_to_managers', 1);
    }
}
