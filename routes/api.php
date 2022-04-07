<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController, UserController, PostController};

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
Route::prefix('V1')->group(function () {

    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
    });

    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::post('/info',   'info');
        Route::post('/create', 'create');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
    });

    Route::prefix('post')->controller(PostController::class)->group(function () {
        Route::post('/detail', 'detail');
        Route::post('/create', 'create');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
    });

});
