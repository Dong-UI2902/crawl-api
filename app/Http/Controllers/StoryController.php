<?php

namespace App\Http\Controllers;

use App\Events\StoryCreated;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoryRequest;
use App\Models\Story;
use App\Services\CrawlService;
use App\Services\StoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\BrowserKit\HttpBrowser;

class StoryController extends Controller
{
    protected $browser;
    protected $storyService;
    protected $crawlService;

    public function __construct(HttpBrowser $browser, StoryService $storyService, CrawlService $crawlService)
    {
        $this->browser = $browser;
        $this->storyService = $storyService;
        $this->crawlService = $crawlService;
    }

    public function index(): JsonResponse
    {
        $stories = Story::Query()
            ->select($this->getFields())
            ->latest()
            ->paginate();

        return response()
            ->json($stories)
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(StoryRequest $request): JsonResponse
    {
        try {
            $url = $request->input('url');
            $crawler = $this->browser->request('GET', $url);

            $config = $this->crawlService->getConfig($url, $crawler);

            if (!$config) {
                return response()->json(['error' => 'Không hỗ trợ trang web này.'], Response::HTTP_BAD_REQUEST);
            }

            $storyData = $this->storyService->scrapeStory($crawler, $config);
            $chapters = $this->crawlService->crawlChapters($crawler, $url);

            $story = Story::create([
                ...$storyData,
                'latest_chapter' => end($chapters)['title'] ?? null,
                'source_url' => $url,
            ]);

            event(new StoryCreated($story));

            if ($chapters)
                $story->chapters()->createMany($chapters);

            return response()
                ->json($story->only('id', 'title', 'thumbnail', 'slug', 'latest_chapter'))
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json($e)->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(string $slug)
    {
        try {
            $story = Story::with([
                'author:id,name,slug',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'title', 'slug');
                },
                'tags:id,name,slug',
            ])->where('slug', $slug)->firstOrFail();

            return response()->json($story)->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json($e)->setStatusCode(Response::HTTP_NOT_FOUND);
        }

    }

    public function update(StoryRequest $request, int $story_id)
    {
        $story = Story::find($story_id);
        if (!$story) {
            return response()->json(['message' => 'Truyện không tồn tại để cập nhật.'], 404);
        }

        $story->update($request->only(
            [
                'title',
                'thumbnail',
                'source_url',
                'author_id',
                'tags',
                'status',
                'description'
            ]
        ));

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function destroy(string $slug): JsonResponse
    {
        Story::where('slug', $slug)->first()->delete();

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function search(SearchRequest $request)
    {
        $type = $request->query('type');
        $value = $request->query('value');

        $query = Story::query();

        switch ($type) {
            case 'title':
                $query->where('title', 'like', "%{$value}%");
                break;

            case 'author':
                $query->whereHas('author', function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%");
                });
                break;

            default:
                $query->whereHas('tags', function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%")
                        ->orWhere('slug', 'like', "%{$value}%");
                });
        }

        return response()
            ->json($query->latest()->paginate(20))
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getHomeSections()
    {
        $hotStories = Cache::remember(
            Story::CACHE_KEY_HOT,
            now()->addHours(config('settings.cache_duration')),
            fn() => Story::hot()->limit(Story::LIMIT_HOT)->get()
        );

        $hotWeeklyStories = Cache::remember(
            Story::CACHE_KEY_HOT_WEEKLY,
            now()->addMinutes(Story::CACHE_TTL_HOT_WEEKLY),
            function () {
                return Story::hotThisWeek()
                    ->limit(Story::LIMIT_HOT)
                    ->get($this->getFields());
            }
        );

        return response()->json([
            'hot' => $hotStories,
            'hot_weekly' => $hotWeeklyStories,
        ]);
    }

    public function getFields(): array
    {
        return [
            'id',
            'title',
            'thumbnail',
            'slug',
            'latest_chapter',
            'views'
        ];
    }
}
