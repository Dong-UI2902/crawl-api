<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public test
Route::prefix('v1')->group(function () {

    Route::get('/stories', [StoryController::class, 'index']);
    Route::get('/stories/home', [StoryController::class, 'getHomeSections']);
    Route::get('/stories/search', [StoryController::class, 'search']);
    Route::get('/stories/{slug}', [StoryController::class, 'show']);

    Route::get('/stories/{storySlug}/{chapterSlug}', [ChapterController::class, 'show']);

    Route::apiResource('tags', TagController::class);
});

// Private
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/stories', [StoryController::class, 'store']);

    Route::Delete('/stories/{slug}', [StoryController::class, 'destroy']);

    Route::get('/followed', [FollowController::class, 'index']);
    Route::post('/follow/{story}', [FollowController::class, 'toggle']);

    Route::get('/tags/not-verified', [TagController::class, 'getTagsNotVerified']);
    Route::post('/tags/merge', [TagController::class, 'mergeTags']);
});
