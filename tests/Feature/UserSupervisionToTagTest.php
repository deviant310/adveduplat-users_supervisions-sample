<?php

namespace Tests\Feature;

use App\Courses;
use App\User;
use App\UserSupervision;
use App\UserSupervisionStatus;
use App\UserSupervisionTag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserSupervisionToTagTest extends TestCase
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
            UserSupervisionTag::factory()->create();
            UserSupervision::factory()->create();
        }
    }

    /**
     * @see UsersSupervisionsToTagsController::index()
     */
    public function testIndex(): void
    {
        /**
         * @var UserSupervision $model
         */

        $model = UserSupervision::take(1)->first();
        $id = $model->getKey();

        $relatedIds = UserSupervisionTag::take(self::FACTORY_COUNT)->pluck((new UserSupervisionTag)->getKeyName());

        $model->tags()->attach($relatedIds);

        $response = $this->get("/api/admin/users-supervisions/$id/tags");

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->has('items.0', function ($json){
                        $json
                            ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionTag'))
                            ->etc();
                    })
                    ->has('total');
            })
            ->assertJson([
                'items' => $model->tags()->get()->toArray(),
                'total' => $model->tags()->count()
            ]);
    }

    /**
     * @see UsersSupervisionsToTagsController::store()
     */
    public function testStore():void
    {
        /**
         * @var UserSupervision $model
         */

        $model = UserSupervision::take(1)->first();
        $id = $model->getKey();

        $relatedIds = UserSupervisionTag::take(self::FACTORY_COUNT)->pluck((new UserSupervisionTag)->getKeyName());

        $response = $this->post("/api/admin/users-supervisions/$id/tags", [
            'ids' => $relatedIds->toArray()
        ]);

        $response->assertNoContent();

        $relatedIds->each(function ($relatedId) use ($model, $id) {
            $this->assertDatabaseHas('users_supervisions_to_tags', [
                $model->tags()->getForeignPivotKeyName() => $id,
                $model->tags()->getRelatedPivotKeyName() => $relatedId
            ]);
        });

        $response = $this->post("/api/admin/users-supervisions/$id/tags", [
            'ids' => [$relatedIds->first()],
            'detach' => true
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('users_supervisions_to_tags', [
            $model->tags()->getForeignPivotKeyName() => $id,
            $model->tags()->getRelatedPivotKeyName() => $relatedIds->first()
        ]);

        $this->assertDatabaseCount('users_supervisions_to_tags', 1);
    }
}
