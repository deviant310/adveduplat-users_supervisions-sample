<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BindRelationRequest;
use App\Http\Requests\QueryBuilderRequest;
use App\UserSupervision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UsersSupervisionsToManagersController extends Controller
{
    use BaseRestTrait;

    /**
     * Display a listing of the resource.
     *
     * @param QueryBuilderRequest $request
     * @param $id
     * @return Response
     */
    public function index(QueryBuilderRequest $request, $id): Response
    {
        /**
         * @var Builder $relatedManagers
         * @var UserSupervision $supervision
         */

        $supervisionInstance = new UserSupervision;

        try {
            $supervision = $supervisionInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $supervisionInstance->getKeyName()
            ]));
        }

        /**
         * @noinspection PhpUndefinedMethodInspection
         * @see QueryBuilderFromRequest::scopeBuildQueryFromRequest()
         */
        $relatedManagers = $supervision
            ->managers()
            ->buildQueryFromRequest($request);

        return $this->response($relatedManagers->get(), $relatedManagers->count());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BindRelationRequest $request
     * @param $id
     * @return Response
     * @throws Throwable
     */
    public function store(BindRelationRequest $request, $id): Response
    {
        $attributes = $request->all();

        $supervisionInstance = new UserSupervision;

        try {
            $supervision = $supervisionInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $supervisionInstance->getKeyName()
            ]));
        }

        $relation = $supervision->managers();

        DB::beginTransaction();

        try {
            $relation->sync($attributes['ids'], $attributes['detach'] ?? false);
        } catch (Throwable $e) {
            DB::rollBack();

            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => 'ids'
            ]));
        }

        DB::commit();

        return response('', 204);
    }
}
