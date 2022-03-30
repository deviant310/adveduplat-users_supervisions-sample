<?php

namespace Tests\Feature;

use App\Courses;
use App\User;
use App\UserSupervision;
use App\UserSupervisionEvent;
use App\UserSupervisionStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserSupervisionToEventTest extends TestCase
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
     * @see UsersSupervisionsToEventsController::index()
     */
    public function testIndex(): void
    {
        /**
         * @var UserSupervision $model
         */

        $model = UserSupervision::take(1)->first();
        $id = $model->getKey();

        $response = $this->get("/api/admin/users-supervisions/$id/events");

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->has('items.0', function ($json){
                        $json
                            ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionEvent'))
                            ->etc();
                    })
                    ->has('total');
            })
            ->assertJson([
                'items' => $model->events()->get()->toArray(),
                'total' => $model->events()->count()
            ]);
    }
}
