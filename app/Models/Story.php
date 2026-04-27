<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Story extends Model
{
    use HasFactory;

    const TYPE = ['title', 'author', 'tags'];

    // Ngưỡng view để xác định truyện Hot
    const HOT_VIEW_THRESHOLD = 1000;

    // Thời gian giãn cách giữa các lần quét (phút)
    const SYNC_INTERVAL_HOT = 60;    // 1 tiếng
    const SYNC_INTERVAL_NORMAL = 1440; // 24 tiếng

    public const LIMIT_HOT = 10;
    public const CACHE_KEY_HOT = 'stories_hot_section';

    public const CACHE_KEY_HOT_WEEKLY = 'stories_hot_weekly';
    public const CACHE_TTL_HOT_WEEKLY = 60;

    protected $guarded = ['id'];

//    protected $hidden = ['author_id', 'created_at', 'updated_at', 'source_url'];
    /**
     * Ép kiểu dữ liệu (Casts).
     * Laravel 10 sử dụng property $casts thay vì method casts().
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'story_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'story_tags', 'story_id', 'tag_id');
    }

    public function scopeHot($query) {
        return $query->orderBy('views', 'desc');
    }

    public function scopeHotThisWeek($query)
    {
        // Sắp xếp theo (view tổng - view chốt tuần trước) = view mới trong tuần này
        return $query->orderByRaw('(views - views_last_week) DESC');
    }

    public function scopeNeedSync($query)
    {
        return $query->where('status', 'ongoing')
            ->where(function($q) {
                $q->where(function($hot) {
                    $hot->where('views', '>', self::HOT_VIEW_THRESHOLD)
                        ->where('last_synced_at', '<', now()->subMinutes(self::SYNC_INTERVAL_HOT));
                })->orWhere(function($normal) {
                    $normal->where('views', '<=', self::HOT_VIEW_THRESHOLD)
                        ->where('last_synced_at', '<', now()->subMinutes(self::SYNC_INTERVAL_NORMAL));
                });
            });
    }
}
