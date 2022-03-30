<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QueryBuilderRequest;
use App\UserSupervision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UsersSupervisionsToEventsController extends Controller
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
         * @var Builder $relatedEvents
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
        $relatedEvents = $supervision
            ->events()
            ->buildQueryFromRequest($request);

        return $this->response(
            $relatedEvents
                ->get()
                ->append(collect($request['append'])->filter()->all()),
            $relatedEvents->count()
        );
    }
}
