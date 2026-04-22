<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagMapping extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
