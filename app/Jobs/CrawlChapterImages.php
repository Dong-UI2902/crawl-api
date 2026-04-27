<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Services\CrawlService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrawlChapterImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chapter;

    /**
     * Số lần thử lại tối đa nếu Job thất bại
     */
    const TRIES = 3;

    /**
     * Số giây Job thử lại lần tiếp theo nếu thất bại
     */
    const WAITING_TIME = 30;

    public function backoff()
    {
        return [60, 120, 300, 600]; // Lần 1 đợi 1p, lần 2 đợi 2p, lần 3 đợi 5p...
    }

    public function failed(Exception $exception)
    {
        Log::error("Job cào ảnh THẤT BẠI HOÀN TOÀN sau nhiều lần thử. Chapter ID: {$this->chapter->id}. Lỗi: " . $exception->getMessage());

        // bắn thông báo Telegram/Slack ở đây để biết đường sửa bot
    }

    /**
     * Create a new job instance.
     *
     * @param mixed $chapter
     */
    public function __construct(Chapter $chapter)
    {
        $this->chapter = $chapter;
    }

    /**
     * Execute the job.
     */
    public function handle(CrawlService $crawlService)
    {
        return Cache::lock('crawl_chapter_' . $this->chapter->id, Chapter::TIME_LOCK)->get(function () use ($crawlService) {
            // Chương đã có ảnh rồi thì không cào lại nữa
            if ($this->chapter->images()->exists()) {
                return;
            }

            try {
                Log::info("Bắt đầu cào ảnh cho chương: ID {$this->chapter->id} - {$this->chapter->title}");

                $imageUrls = $crawlService->crawlImages($this->chapter);

                if (empty($imageUrls))
                    throw new Exception("Không tìm thấy ảnh nào từ nguồn.");

                DB::transaction(function () use ($imageUrls) {
                    $this->chapter->images()->createMany($imageUrls);
                });

                Log::info("Đã lưu " . count($imageUrls) . " ảnh cho Chapter ID: {$this->chapter->id}");
            } catch (Exception $e) {
                Log::error("Lỗi khi cào ảnh chương {$this->chapter->id}: " . $e->getMessage());

                if ($this->attempts() >= self::TRIES) {
                    throw $e;
                }

                // Đẩy lại vào hàng chờ để thử lại sau $waitingTime nếu chưa quá số lần $tries
                $this->release(self::WAITING_TIME);
            }
        });
    }
}
