<?php

namespace App\Http\Controllers;

use App\Events\ChapterViewed;
use App\Http\Requests\ChapterRequest;
use App\Models\Chapter;
use App\Services\CrawlService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\BrowserKit\HttpBrowser;

class ChapterController extends Controller
{
    protected $browser;
    protected $crawlService;

    public function __construct(HttpBrowser $browser, CrawlService $crawlService)
    {
        $this->browser = $browser;
        $this->crawlService = $crawlService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChapterRequest $request)
    {
        $chapter = Chapter::create($request->all());

        return response()->json($chapter, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $storySlug, string $chapterSlug)
    {
        /** @var \App\Models\Chapter $chapter */
        $chapter = Chapter::with(['images', 'story'])
            ->where('slug', $chapterSlug)
            ->whereHas('story', function($q) use ($storySlug) {
                $q->where('slug', $storySlug);
            })
            ->firstOrFail();

        if (!Str::contains($chapter->title, 'Oneshot'))
            event(new ChapterViewed($chapter));

        $images = $chapter->images;
        if ($images->isNotEmpty()) {
            return response()->json([
                'images' => $images,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'chapter_title' => $chapter->title,
            'images' => $images,
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chapter $chapter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chapter $chapter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $chapterId)
    {
        $this->findById($chapterId)->delete();

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
