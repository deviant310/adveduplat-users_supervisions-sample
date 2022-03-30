<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\UsersSupervisionsStatusesController;
use App\UserSupervisionStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserSupervisionStatusTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @see UsersSupervisionsStatusesController::index()
     */
    public function testIndex(): void
    {
        for ($i = 1; $i < 3; $i++)
            UserSupervisionStatus::factory()->create();

        $response = $this->get('/api/admin/users-supervisions-statuses');

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->has('items.0', function ($json){
                        $json
                            ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionStatus'))
                            ->etc();
                    })
                    ->has('total');
            })
            ->assertJson([
                'items' => UserSupervisionStatus::orderBy('id')->get()->toArray(),
                'total' => UserSupervisionStatus::count()
            ]);
    }

    /**
     * @see UsersSupervisionsStatusesController::show()
     */
    public function testShow(): void
    {
        $model = UserSupervisionStatus::factory()->create()->fresh();
        $id = $model->getKey();

        $response = $this->get("/api/admin/users-supervisions-statuses/$id");

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionStatus'))
                    ->etc();
            })
            ->assertJson($model->toArray());
    }

    /**
     * @see UsersSupervisionsStatusesController::store()
     */
    public function testStore():void
    {
        $attributes = UserSupervisionStatus::factory()->make()->toArray();

        $response = $this->post('/api/admin/users-supervisions-statuses', $attributes);

        $response
            ->assertStatus(201)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionStatus'))
                    ->etc();
            })
            ->assertJson($attributes);

        $this->assertDatabaseHas(UserSupervisionStatus::class, $attributes);
    }

    /**
     * @see UsersSupervisionsStatusesController::update()
     */
    public function testUpdate():void
    {
        $model = UserSupervisionStatus::factory()->create()->fresh();
        $id = $model->getKey();
        $attributes = UserSupervisionStatus::factory()->make()->toArray();

        $response = $this->put("/api/admin/users-supervisions-statuses/$id", $attributes);

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionStatus'))
                    ->etc();
            })
            ->assertJson($attributes);

        $this->assertDatabaseHas(
            UserSupervisionStatus::class,
            collect($attributes)->merge([
                $model->getKeyName() => $id
            ])->all()
        );
    }

    /**
     * @see UsersSupervisionsStatusesController::destroy()
     */
    public function testDestroy():void
    {
        $model = UserSupervisionStatus::factory()->create()->fresh();
        $id = $model->getKey();

        $response = $this->delete("/api/admin/users-supervisions-statuses/$id");

        $response->assertNoContent();

        $this->assertDeleted($model);
    }
}
