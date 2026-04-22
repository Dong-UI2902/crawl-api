<?php

namespace App\Listeners;

use App\Events\ChapterViewed;
use App\Jobs\CrawlChapterImages;
use App\Models\Chapter;
use App\Services\CrawlService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PrefetchNextChapter implements ShouldQueue
{
    protected $crawlService;
    /**
     * Create the event listener.
     */
    public function __construct(CrawlService $crawlService)
    {
        $this->crawlService = $crawlService;
    }

    /**
     * Handle the event.
     */
    public function handle(ChapterViewed $event): void
    {
        $currentChapter = $event->chapter;
        $nextChapter = Chapter::where('story_id', $currentChapter->story_id)
            ->where('id', '>', $currentChapter->id)
            ->orderBy('id', 'asc')
            ->first();

        if ($nextChapter && $nextChapter->images()->count() === 0) {
            CrawlChapterImages::dispatch($nextChapter);
        }
    }
}
