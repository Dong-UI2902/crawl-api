<?php

namespace App\Listeners;

use App\Events\StoryCreated;
use App\Services\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleStoryCover implements ShouldQueue
{
    protected $imageService;
    /**
     * Create the event listener.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Handle the event.
     */
    public function handle(StoryCreated $event): void
    {
        try {
            $story = $event->story;
            $remoteUrl = $story->thumbnail;

            if (filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
                $newPath = $this->imageService->uploadToImageKit($story->thumbnail, 'covers');

                if ($newPath) {
                    $story->update(['thumbnail' => $newPath]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Lỗi khi lưu ảnh bài của truyện: {$event->story->id}: " . $e->getMessage());
        }
    }
}
