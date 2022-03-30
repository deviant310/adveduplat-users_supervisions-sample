<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\UsersSupervisionsStatusesController;
use App\UserSupervisionStatus;
use App\UserSupervisionTag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserSupervisionTagTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @see UsersSupervisionsTagsController::index()
     */
    public function testIndex(): void
    {
        for ($i = 1; $i < 3; $i++)
            UserSupervisionTag::factory()->create();

        $response = $this->get('/api/admin/users-supervisions-tags');

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
                'items' => UserSupervisionTag::orderBy('id')->get()->toArray(),
                'total' => UserSupervisionTag::count()
            ]);
    }

    /**
     * @see UsersSupervisionsTagsController::show()
     */
    public function testShow(): void
    {
        $model = UserSupervisionTag::factory()->create()->fresh();
        $id = $model->getKey();

        $response = $this->get("/api/admin/users-supervisions-tags/$id");

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionTag'))
                    ->etc();
            })
            ->assertJson($model->toArray());
    }

    /**
     * @see UsersSupervisionsTagsController::store()
     */
    public function testStore():void
    {
        $attributes = UserSupervisionTag::factory()->make()->toArray();

        $response = $this->post('/api/admin/users-supervisions-tags', $attributes);

        $response
            ->assertStatus(201)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionTag'))
                    ->etc();
            })
            ->assertJson($attributes);

        $this->assertDatabaseHas(UserSupervisionTag::class, $attributes);
    }

    /**
     * @see UsersSupervisionsTagsController::update()
     */
    public function testUpdate():void
    {
        $model = UserSupervisionTag::factory()->create()->fresh();
        $id = $model->getKey();
        $attributes = UserSupervisionTag::factory()->make()->toArray();

        $response = $this->put("/api/admin/users-supervisions-tags/$id", $attributes);

        $response
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json){
                $json
                    ->whereAllType($this->getSchemaAttributeTypes('UserSupervisionTag'))
                    ->etc();
            })
            ->assertJson($attributes);

        $this->assertDatabaseHas(
            UserSupervisionTag::class,
            collect($attributes)->merge([
                $model->getKeyName() => $id
            ])->all()
        );
    }

    /**
     * @see UsersSupervisionsTagsController::destroy()
     */
    public function testDestroy():void
    {
        $model = UserSupervisionTag::factory()->create()->fresh();
        $id = $model->getKey();

        $response = $this->delete("/api/admin/users-supervisions-tags/$id");

        $response->assertNoContent();

        $this->assertDeleted($model);
    }
}
