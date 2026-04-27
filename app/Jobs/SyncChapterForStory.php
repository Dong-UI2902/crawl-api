<?php

namespace App\Jobs;

use App\Models\Story;
use App\Services\ChapterService;
use App\Services\CrawlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncChapterForStory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chapterService;

    /**
     * Create a new job instance.
     */
    public function __construct(ChapterService $chapterService)
    {
        $this->chapterService = $chapterService;
    }

    /**
     * Execute the job.
     */
    public function handle(Story $story): void
    {
        $this->chapterService->syncChapters($story);
    }
}
