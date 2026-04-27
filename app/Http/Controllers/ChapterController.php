<?php

namespace App\Http\Controllers;

use App\Events\ChapterViewed;
use App\Http\Requests\ChapterRequest;
use App\Models\Chapter;
use App\Services\CrawlService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $data = $request->all();
        $chapter = Chapter::firstOrCreate(
            [
                'story_id' => $data['story_id'],
                'slug' => $data['slug'],
            ],
            $data,
        );

        return response()->json($chapter)->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $storySlug, string $chapterSlug)
    {
        $chapter = Chapter::with([
            'story:id,title,slug', // Chỉ lấy những cột cần thiết cho nhẹ
            'images' => fn($q) => $q->orderBy('order', 'asc')
        ])
            ->where('slug', $chapterSlug)
            ->whereHas('story', function ($q) use ($storySlug) {
                $q->where('slug', $storySlug);
            })
            ->firstOrFail();

        if (!Str::contains($chapter->title, 'Oneshot'))
            event(new ChapterViewed($chapter));

        if ($chapter->images->isNotEmpty()) {
            return $this->responseWithCache($chapter);
        }

        return Cache::lock('crawl_chapter_' . $chapter->id, Chapter::TIME_LOCK)->get(function () use ($chapter) {
            if ($chapter->images()->exists()) {
                return $this->responseFunction($chapter);
            }

            $imagesData = $this->crawlService->crawlImages($chapter);

            if (empty($imagesData)) {
                return response()
                    ->json(['message' => 'Không tìm thấy ảnh nào.'])
                    ->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            return DB::transaction(function () use ($chapter, $imagesData) {
                $chapter->images()->createMany($imagesData);

                return $this->responseFunction($chapter, $imagesData);
            });
        });
    }

    private function responseFunction(Chapter $chapter, $imagesData = null)
    {
        return response()->json([
            'story' => [
                'id' => $chapter->story->id,
                'title' => $chapter->story->title,
                'slug' => $chapter->story->slug,
            ],
            'chapter' => [
                'title' => $chapter->title,
                'slug' => $chapter->slug,
                'images' => $imagesData ?? $chapter->getSortedImages(),
                'navigation' => $chapter->getNavigationLinks(),
            ]
        ])->setStatusCode(Response::HTTP_OK);
    }

    private function responseWithCache(Chapter $chapter)
    {
        $cacheKey = "chapter_content_" . $chapter->id;

        $data = Cache::remember($cacheKey, now()->addDays(Chapter::CACHE_TTL_DAYS), function () use ($chapter) {
            return [
                'images' => $chapter->getSortedImages(),
                'navigation' => $chapter->getNavigationLinks(),
            ];
        });

        return $this->responseFunction($chapter, $data['images']);
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
