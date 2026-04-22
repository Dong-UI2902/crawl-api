<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function stories(): BelongsToMany {
        return $this->belongsToMany(Story::class, 'story_tags', 'tag_id', 'story_id');
    }

    public function variants(): HasMany {
        return $this->hasMany(TagMapping::class);
    }
}
