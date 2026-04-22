<?php

namespace App\Listeners;

use App\Events\StoryCreated;
use App\Services\CrawlService;
use App\Services\TagService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessStoryTags implements ShouldQueue
{
    protected $crawlService;
    protected $tagService;

    /**
     * Create the event listener.
     */
    public function __construct(CrawlService $crawlService, TagService $tagService)
    {
        $this->crawlService = $crawlService;
        $this->tagService = $tagService;
    }

    /**
     * Handle the event.
     */
    public function handle(StoryCreated $event): void
    {
        try {
            $story = $event->story;
            $tags = $this->crawlService->crawlTags($story);

            if ($tags)
                $this->tagService->syncStoryTags($story, $tags);
            Log::info("Đã xử lý xong tags cho truyện: {$story->title}");
        } catch (\Exception $e) {
            Log::error('Error processing story tags: ' . $e->getMessage());
        }
    }
}
