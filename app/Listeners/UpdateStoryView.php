<?php

namespace App\Listeners;

use App\Events\ChapterViewed;
use App\Models\Chapter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateStoryView
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ChapterViewed $event): void
    {
        $event->chapter->story->increment('views');
    }
}
