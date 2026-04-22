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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrawlChapterImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần thử lại tối đa nếu Job thất bại
     */
    public $tries = 3;

    /**
     * Số giây Job được phép chạy trước khi timeout
     * Cào ảnh có thể lâu nên để khoảng 2-5 phút
     */
    public $timeout = 300;

    /**
     * Số giây Job thử lại lần tiếp theo nếu thất bại
     */
    public $waitingTime = 30;

    protected $chapter;


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
        // Chương đã có ảnh rồi thì không cào lại nữa
        if ($this->chapter->images()->exists()) {
            return;
        }

        try {
            Log::info("Bắt đầu cào ảnh cho chương: ID {$this->chapter->id} - {$this->chapter->title}");

            $imageUrls = $crawlService->crawlImages($this->chapter);

            if (empty($imageUrls)) {
                throw new Exception("Không tìm thấy ảnh nào từ nguồn.");
            }

            DB::transaction(function () use ($imageUrls) {
                foreach ($imageUrls as $imageData) {
                    $this->chapter->images()->create([
                        'src' => $imageData['src'],
                        'order' => $imageData['order'],
                    ]);
                }
            });

            Log::info("Đã lưu " . count($imageUrls) . " ảnh cho Chapter ID: {$this->chapter->id}");
        } catch (Exception $e) {
            Log::error("Lỗi khi cào ảnh chương {$this->chapter->id}: " . $e->getMessage());

            // Đẩy lại vào hàng chờ để thử lại sau $waitingTime nếu chưa quá số lần $tries
            $this->release($this->waitingTime);
        }
    }
}
