<?php

use App\Http\Controllers\ChapterController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public test
Route::prefix('v1')->group(function () {

    Route::post('/stories', [StoryController::class, 'store']);

    Route::get('/stories', [StoryController::class, 'index']);
    Route::get('/stories/{slug}', [StoryController::class, 'show']);

    Route::get('/stories/{storySlug}/{chapterSlug}', [ChapterController::class, 'show']);

    Route::Delete('/stories/{slug}', [StoryController::class, 'destroy']);

    Route::get('/tags/not-verified', [TagController::class, 'getTagsNotVerified']);
    Route::post('/tags/merge', [TagController::class, 'mergeTags']);
    Route::apiResource('tags', TagController::class);
});

// Private
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Route::post('/stories/crawl', [StoryController::class, 'store']);
});
