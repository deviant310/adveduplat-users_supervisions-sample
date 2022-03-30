<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QueryBuilderRequest;
use App\Http\Requests\UserSupervisionStatusRequest;
use App\UserSupervisionStatus;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UsersSupervisionsStatusesController extends Controller
{
    use BaseRestTrait;

    /**
     * Display a listing of the resource.
     *
     * @param QueryBuilderRequest $request
     * @return Response
     */
    public function index(QueryBuilderRequest $request): Response
    {
        /**
         * @see QueryBuilderFromRequest::scopeBuildQueryFromRequest()
         */
        $statuses =  (new UserSupervisionStatus)
            ->buildQueryFromRequest($request);

        return $this->response($statuses->get(), $statuses->count());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserSupervisionStatusRequest $request
     * @return Response
     */
    public function store(UserSupervisionStatusRequest $request): Response
    {
        /**
         * @var UserSupervisionStatus $status
         */

        $attributes = $request->validated();

        $status = UserSupervisionStatus::create($attributes);

        return response($status, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\QueryBuilderRequest $request
     * @param int $id
     * @return Response
     */
    public function show(QueryBuilderRequest $request, int $id): Response
    {
        /**
         * @var UserSupervisionStatus $status
         */

        $statusInstance = new UserSupervisionStatus;

        try {
            /**
             * @see QueryBuilderFromRequest::scopeBuildQueryFromRequest()
             */
            $status = $statusInstance
                ->buildQueryFromRequest($request)
                ->where($statusInstance->getKeyName(), $id)
                ->firstOrFail();
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $statusInstance->getKeyName()
            ]));
        }

        return response($status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserSupervisionStatusRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UserSupervisionStatusRequest $request, int $id): Response
    {
        /**
         * @var UserSupervisionStatus $status
         */

        $attributes = $request->validated();

        $statusInstance = new UserSupervisionStatus;

        try {
            $status = $statusInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $statusInstance->getKeyName()
            ]));
        }

        $status->update($attributes);

        return response($status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        /**
         * @var UserSupervisionStatus $status
         */

        $statusInstance = new UserSupervisionStatus;

        try {
            $status = $statusInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $statusInstance->getKeyName()
            ]));
        }

        $status->delete();

        return response('', 204);
    }
}
