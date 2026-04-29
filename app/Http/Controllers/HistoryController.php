<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $history = $user->readStories()->latest()->paginate(10);

        return response()->json($history);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $storyId = $request->input('story_id');
        $chapterId = $request->input('chapter_id');

        $user->readStories()->sync([
            $storyId => ['chapter_id' => $chapterId]
        ], false);

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function destroy(Request $request, $storyId = null)
    {
        $user = $request->user();
        if ($storyId) {
            $user->readStories()->detach($storyId);
        } else {
            $user->readStories()->detach();
        }

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
