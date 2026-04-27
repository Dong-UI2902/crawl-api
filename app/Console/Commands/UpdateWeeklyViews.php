<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateWeeklyViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:update-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chốt số lượt xem cuối tuần để tính toán tốc độ tăng trưởng cho tuần mới';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        {
            $this->info('Bắt đầu cập nhật views_last_week...');

            try {
                // Sử dụng chunk để xử lý dữ liệu lớn một cách an toàn
                Story::query()->chunkById(100, function ($stories) {
                    foreach ($stories as $story) {
                        $story->update([
                            'views_last_week' => $story->views
                        ]);
                    }
                });

                Log::info("Successfully updated weekly views at " . now());
                $this->info('Cập nhật hoàn tất!');

            } catch (\Exception $e) {
                Log::error("Failed to update weekly views: " . $e->getMessage());
                $this->error('Có lỗi xảy ra, vui lòng kiểm tra log.');
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }
    }
}
