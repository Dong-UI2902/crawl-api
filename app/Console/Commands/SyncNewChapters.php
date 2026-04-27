<?php

namespace App\Console\Commands;

use App\Jobs\SyncChapterForStory;
use App\Models\Story;
use App\Services\ChapterService;
use Illuminate\Console\Command;

class SyncNewChapters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-new-chapters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(ChapterService $chapterService)
    {
        // Lấy dư ra một chút rồi shuffle bằng PHP để tránh inRandomOrder của SQL
        $stories = Story::needSync()
            ->orderBy('last_synced_at', 'asc') // Ưu tiên bộ "đói" sync nhất
            ->limit(20)
            ->get()
            ->shuffle()
            ->take(10);

        if ($stories->isEmpty()) {
            $this->info("Không có truyện nào cần sync.");
            return;
        }

        foreach ($stories as $story) {
            // Cập nhật ngay để các tiến trình chạy sau không lấy trùng bộ này
            $story->update(['last_synced_at' => now()]);

            // Đẩy vào Queue để xử lý song song, không bắt Command phải đợi
            SyncChapterForStory::dispatch($story);

            $this->info("Đã đẩy bộ [{$story->title}] vào hàng chờ.");
        }
    }
}
