<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QueryBuilderRequest;
use App\Http\Requests\UserSupervisionTagRequest;
use App\UserSupervisionTag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UsersSupervisionsTagsController extends Controller
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
        $tags =  (new UserSupervisionTag)
            ->buildQueryFromRequest($request);

        return $this->response($tags->get(), $tags->count());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserSupervisionTagRequest $request
     * @return Response
     */
    public function store(UserSupervisionTagRequest $request): Response
    {
        /**
         * @var UserSupervisionTag $tag
         */

        $attributes = $request->validated();

        $tag = UserSupervisionTag::create($attributes);

        return response($tag, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param QueryBuilderRequest $request
     * @param int $id
     * @return Response
     */
    public function show(QueryBuilderRequest $request, int $id): Response
    {
        /**
         * @var UserSupervisionTag $tag
         */

        $tagInstance = new UserSupervisionTag;

        try {
            /**
             * @see QueryBuilderFromRequest::scopeBuildQueryFromRequest()
             */
            $tag = $tagInstance
                ->buildQueryFromRequest($request)
                ->where($tagInstance->getKeyName(), $id)
                ->firstOrFail();
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $tagInstance->getKeyName()
            ]));
        }

        return response($tag);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserSupervisionTagRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UserSupervisionTagRequest $request, int $id): Response
    {
        /**
         * @var UserSupervisionTag $tag
         */

        $attributes = $request->validated();

        $tagInstance = new UserSupervisionTag;

        try {
            $tag = $tagInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $tagInstance->getKeyName()
            ]));
        }

        $tag->update($attributes);

        return response($tag);
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
         * @var UserSupervisionTag $tag
         */

        $tagInstance = new UserSupervisionTag;

        try {
            $tag = $tagInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.exists', [
                'attribute' => $tagInstance->getKeyName()
            ]));
        }

        $tag->delete();

        return response('', 204);
    }
}
