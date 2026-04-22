<?php

namespace App\Providers;

use App\Events\ChapterViewed;
use App\Events\StoryCreated;
use App\Listeners\HandleStoryCover;
use App\Listeners\PrefetchNextChapter;
use App\Listeners\ProcessStoryTags;
use App\Listeners\UpdateStoryView;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        StoryCreated::class => [
            HandleStoryCover::class,
            ProcessStoryTags::class
        ],

        ChapterViewed::class => [
            UpdateStoryView::class,
            PrefetchNextChapter::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
