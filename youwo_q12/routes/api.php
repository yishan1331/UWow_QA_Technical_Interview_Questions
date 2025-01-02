<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ArticleController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Article Routes
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::post('/', [ArticleController::class, 'store']);
    Route::get('/trashed', [ArticleController::class, 'trashed']);
    Route::get('/search', [ArticleController::class, 'search']);
    Route::get('/{article}', [ArticleController::class, 'show']);
    Route::patch('/{article}', [ArticleController::class, 'update']);
    Route::delete('/{article}', [ArticleController::class, 'destroy']);
    Route::patch('/restore/{id}', [ArticleController::class, 'restore']);
    Route::delete('/force/{id}', [ArticleController::class, 'forceDelete']);
    Route::post('/order', [ArticleController::class, 'updateOrder']);
    Route::patch('/toggle-active/{article}', [ArticleController::class, 'toggleActive']);
});