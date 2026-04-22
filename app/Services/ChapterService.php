<?php

namespace App\Services;

use App\Models\Story;

class ChapterService {
    protected $crawlService;

    public function __construct(CrawlService $crawlService)
    {
        $this->crawlService = $crawlService;
    }

    public function syncChapters(Story $story)
    {
        $existingSlugs = $story->chapters()->pluck('slug')->toArray();

        $sourceChapters = $this->crawlService->crawlChapters($story->source_url);

        $newChapters = [];
        foreach ($sourceChapters as $sourceChapter) {
            // Nếu slug chưa tồn tại trong DB thì mới thêm vào danh sách mới
            if (!in_array($sourceChapter['slug'], $existingSlugs)) {
                $newChapters[] = [
                    'story_id' => $story->id,
                    'title' => $sourceChapter['title'],
                    'slug' => $sourceChapter['slug'],
                    'source_url' => $sourceChapter['source_url'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($newChapters)) {
            foreach ($newChapters as $chapterData) {
                $story->chapters()->create($chapterData);
            }
        }
    }
}
