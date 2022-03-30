<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api,web', 'user.request'])->group(function () {
    /**
     * Users Supervisions routes
     */
    Route::apiResource('users-supervisions', 'Api\UsersSupervisionsController')
        ->parameters(['users-supervisions' => 'id']);
    Route::apiResource('users-supervisions-tags', 'Api\UsersSupervisionsTagsController')
        ->parameters(['users-supervisions-tags' => 'id']);
    Route::apiResource('users-supervisions-statuses', 'Api\UsersSupervisionsStatusesController')
        ->parameters(['users-supervisions-statuses' => 'id']);
    Route::apiResource('users-supervisions.tags', 'Api\UsersSupervisionsToTagsController')
        ->parameters(['users-supervisions' => 'related_id'])
        ->only(['index', 'store']);
    Route::apiResource('users-supervisions.curators', 'Api\UsersSupervisionsToCuratorsController')
        ->parameters(['users-supervisions' => 'related_id'])
        ->only(['index', 'store']);
    Route::apiResource('users-supervisions.managers', 'Api\UsersSupervisionsToManagersController')
        ->parameters(['users-supervisions' => 'related_id'])
        ->only(['index', 'store']);
    Route::apiResource('users-supervisions.events', 'Api\UsersSupervisionsToEventsController')
        ->parameters(['users-supervisions' => 'related_id'])
        ->only(['index']);
});
