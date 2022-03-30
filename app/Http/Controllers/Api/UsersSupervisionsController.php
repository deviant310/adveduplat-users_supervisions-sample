<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QueryBuilderRequest;
use App\Http\Requests\UserSupervisionRequest;
use App\User;
use App\UserSupervision;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UsersSupervisionsController extends Controller
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
        $supervisions = (new UserSupervision)
            ->buildQueryFromRequest($request);

        return $this->response(
            $supervisions
                ->get()
                ->append(collect($request['append'])->filter()->all()),
            $supervisions->count()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserSupervisionRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(UserSupervisionRequest $request): Response
    {
        /**
         * @var User $currentUser
         */

        $attributes = $request->all();
        $currentUser = auth()->user();

        DB::beginTransaction();

        try {
            $supervision = UserSupervision::create($attributes);

            if($currentUser)
                $supervision->curators()->attach([$currentUser->id]);
        } catch (Throwable $e) {
            DB::rollBack();

            throw new HttpException(500, $e->getMessage());
        }

        DB::commit();

        return response($supervision, 201);
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
         * @var UserSupervision $supervision
         */

        $supervisionInstance = new UserSupervision;

        /**
         * @see QueryBuilderFromRequest::scopeBuildQueryFromRequest()
         */
        $supervisions = $supervisionInstance
            ->buildQueryFromRequest($request)
            ->where($supervisionInstance->getKeyName(), $id);

        try {
            $supervision = $supervisions
                ->firstOrFail()
                ->append(collect($request['append'])->filter()->all());
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.db_record_exists', [
                'key_name' => $supervisionInstance->getKeyName()
            ]));
        }


        return response($supervision);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserSupervisionRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UserSupervisionRequest $request, int $id): Response
    {
        /**
         * @var UserSupervision $supervision
         */

        $attributes = $request->all();

        $supervisionInstance = new UserSupervision;

        try {
            $supervision = $supervisionInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.db_record_exists', [
                'key_name' => $supervisionInstance->getKeyName()
            ]));
        }

        $supervision->update($attributes);

        return response($supervision);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function destroy(int $id): Response
    {
        /**
         * @var UserSupervision $supervision
         */

        DB::beginTransaction();

        $supervisionInstance = new UserSupervision;

        try {
            $supervision = $supervisionInstance->findOrFail($id);
        } catch (Throwable $e) {
            throw new HttpException(404, Lang::get('validation.db_record_exists', [
                'key_name' => $supervisionInstance->getKeyName()
            ]));
        }

        try {
            $supervision->curators()->detach();
            $supervision->managers()->detach();
            $supervision->tags()->detach();
            $supervision->delete();
        } catch (Throwable $e) {
            DB::rollBack();

            throw new HttpException(500, $e->getMessage());
        }

        DB::commit();

        return response('', 204);
    }
}
