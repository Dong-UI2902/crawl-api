<?php

namespace App\Console\Commands;

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
        // Lấy danh sách truyện cần sync
        $stories = Story::where('status', 'ongoing')
            ->where(function($query) {
                $query->where(function($q) {
                    // Nhóm Hot: 1 tiếng quét 1 lần
                    $q->where('views', '>', Story::HOT_VIEW_THRESHOLD)
                        ->where('last_synced_at', '<', now()->subMinutes(Story::SYNC_INTERVAL_HOT));
                })
                    ->orWhere(function($q) {
                        // Nhóm Thường: 24 tiếng mới quét 1 lần
                        $q->where('views', '<=', Story::HOT_VIEW_THRESHOLD)
                            ->where('last_synced_at', '<', now()->subMinutes(Story::SYNC_INTERVAL_NORMAL));
                    });
            })
            ->inRandomOrder() // Trộn ngẫu nhiên trong những bộ đã đến hạn
            ->limit(10)
            ->get();

        foreach ($stories as $story) {
            $this->info("Đang kiểm tra bộ: " . $story->title);

            // 2. Gọi Service để xử lý
            $chapterService->syncChapters($story);

            // 3. Cập nhật thời gian đã quét
            $story->update(['last_synced_at' => now()]);
        }
    }
}
