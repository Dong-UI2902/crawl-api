<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FollowController extends Controller
{
    public function index(Request $request)
    {
        $stories = $request->user()->followedStories()
            ->paginate();
//            ->paginate(config('settings.page_size'));

        return response()->json($stories);
    }

    public function toggle(Request $request, Story $story): JsonResponse
    {
        $user = $request->user();

        $status = $user->followedStories()->toggle($story->id);

        $isFollowing = count($status['attached']) > 0;

        return response()->json([
            'message' => $isFollowing ? 'Đã theo dõi truyện' : 'Đã bỏ theo dõi',
            'is_following' => $isFollowing
        ])->setStatusCode(Response::HTTP_OK);
    }
}
