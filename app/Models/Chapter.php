<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    const TIME_LOCK = 60;
    const CACHE_TTL_DAYS = 7;

    protected $guarded = ['id'];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function getSortedImages()
    {
        if ($this->relationLoaded('images')) {
            return $this->images;
        }

        return $this->images()->orderBy('order', 'asc')->get();
    }

    public function getNavigationLinks()
    {
        if (str_contains($this->title, 'Oneshot')) {
            return [
                'prev_slug' => null,
                'next_slug' => null,
            ];
        }

        $prev = self::where('story_id', $this->story_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->first(['slug']);

        $next = self::where('story_id', $this->story_id)
            ->where('id', '>', $this->id)
            ->orderBy('id', 'asc')
            ->first(['slug']);

        return [
            'prev_slug' => $prev?->slug,
            'next_slug' => $next?->slug,
        ];
    }
}
