<?php

namespace Tests\Feature;

use App\Courses;
use App\User;
use App\UserSupervision;
use App\Http\Controllers\Api\UsersSupervisionsController;
use App\UserSupervisionStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserSupervisionTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        for ($i = 1; $i < 3; $i++) {
            User::factory()->create();
            Courses::factory()->create();
            UserSupervisionStatus::factory()->create();
        }
    }

    /**
     * @see UsersSupervisionsController::index()
     */
    public function testIndex(): void
    {
        for ($i = 1; $i < 3; $i++)
            UserSupervision::factory()->create();

        $response = $this->get('/api/admin/users-supervisions');

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->has('items.0', function ($json){
                        $json
                            ->whereAllType($this->getSchemaAttributeTypes('UserSupervision'))
                            ->etc();
                    })
                    ->has('total');
            })
            ->assertJson([
                'items' => UserSupervision::orderBy('id')->get()->toArray(),
                'total' => UserSupervision::count()
            ]);
    }

    /**
     * @see UsersSupervisionsController::show()
     */
    public function testShow(): void
    {
        $model = UserSupervision::factory()->create()->fresh();
        $id = $model->getKey();

        $response = $this->get("/api/admin/users-supervisions/$id");

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervision'))
                    ->etc();
            })
            ->assertJson($model->toArray());
    }

    /**
     * @see UsersSupervisionsController::store()
     */
    public function testStore():void
    {
        $attributes = UserSupervision::factory()->make()->toArray();

        $response = $this->post('/api/admin/users-supervisions', $attributes);

        $response
            ->assertStatus(201)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervision'))
                    ->etc();
            })
            ->assertJson($attributes);

        $this->assertDatabaseHas(UserSupervision::class, $attributes);
    }

    /**
     * @see UsersSupervisionsController::update()
     */
    public function testUpdate():void
    {
        $model = UserSupervision::factory()->create()->fresh();
        $id = $model->getKey();
        $attributes = UserSupervision::factory()->make()->toArray();

        $response = $this->put("/api/admin/users-supervisions/$id", $attributes);

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervision'))
                    ->etc();
            })
            ->assertJson($attributes);

        $this->assertDatabaseHas(
            UserSupervision::class,
            collect($attributes)->merge([
                $model->getKeyName() => $id
            ])->all()
        );
    }

    /**
     * @see UsersSupervisionsController::destroy()
     */
    public function testDestroy():void
    {
        $model = UserSupervision::factory()->create()->fresh();
        $id = $model->getKey();

        $response = $this->delete("/api/admin/users-supervisions/$id");

        $response->assertNoContent();

        $this->assertDeleted($model);
    }
}
