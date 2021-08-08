<?php

use App\Http\Controllers\AdminFormumController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware(['json.response', 'auth:api'])->group(function () {

    Route::get('user', [UserController::class, 'getUser']);
    Route::post('post/store', [ForumController::class, 'storePost']);
    Route::post('post/edit', [ForumController::class, 'editPost']);
    Route::delete('post/delete/{id}', [ForumController::class, 'deletePost']);
    Route::get('post/get/{id}', [ForumController::class, 'getPostById']);
    Route::get('post/get-all', [ForumController::class, 'getAllPosts']);
    Route::get('post/get-my-posts', [ForumController::class, 'getMyPosts']);
    Route::post('post/comment', [ForumController::class, 'postComment']);
    Route::get('post/comment/get-all', [ForumController::class, 'getComments']);

    Route::group(['prefix' => 'admin'], function () {
        Route::post('post/status/change', [ForumController::class, 'postChangeStatus']);
    });

});

Route::post('register', [UserController::class, 'registerUser']);
Route::post('login', [UserController::class, 'loginUser']);
