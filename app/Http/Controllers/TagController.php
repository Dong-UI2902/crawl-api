<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use App\Models\TagMapping;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Tag::all(), Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagRequest $request)
    {
        $name = $request['name'];
        $tag = Tag::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);

        return response()->json($tag, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return response()->json($tag, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $tag->update($request->all());

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function mergeTags(Request $request)
    {
        try {
            $sourceTagId = $request['sourceTagId'];
            $targetTagId = $request['targetTagId'];
            // 1. Cập nhật tất cả Mapping đang trỏ về Tag cũ sang Tag mới
            TagMapping::where('tag_id', $sourceTagId)
                ->update(['tag_id' => $targetTagId]);

            // 2. Cập nhật các truyện đang gắn Tag cũ sang Tag mới
            // Lấy danh sách story_id đang dùng tag cũ
            $storyIds = DB::table('story_tags')->where('tag_id', $sourceTagId)->pluck('story_id');

//            if (!$storyIds)
//                return

            foreach ($storyIds as $id) {
                // Gắn tag mới (syncWithoutDetaching để tránh trùng lặp)
                DB::table('story_tags')->insertOrIgnore([
                    'story_id' => $id,
                    'tag_id' => $targetTagId
                ]);
            }

            // 3. Xóa các liên kết cũ và xóa luôn Tag rác
//            DB::table('story_tags')->where('tag_id', $sourceTagId)->delete();
            Tag::findOrFail($sourceTagId)->delete();

            return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTagsNotVerified()
    {
        $tags = Tag::where('is_verified', false)->get();

        return response()->json($tags, Response::HTTP_OK);
    }
}
