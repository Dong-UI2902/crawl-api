<?php

namespace App\Services;

use App\Models\Story;
use App\Models\Tag;
use App\Models\TagMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagService
{
    public function syncStoryTags(Story $story, array $sourceTagNames)
    {
        $finalTagIds = [];

        DB::transaction(function () use ($sourceTagNames, &$finalTagIds) {
            foreach ($sourceTagNames as $rawName) {

                $name = data_get($rawName, 'name', is_string($rawName) ? $rawName : '');

                if (empty($name)) continue;

                $slug = data_get($rawName, 'slug');

                $mapping = TagMapping::where('name_variant', $name)->first();

                if ($mapping) {
                    $finalTagIds[] = (int) $mapping->tag_id;
                } else {
                    $tag = Tag::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $name,
                            'is_verified' => false
                        ]
                    );

                    TagMapping::firstOrCreate([
                        'name_variant' => $name,
                        'tag_id' => $tag->id
                    ]);

                    $finalTagIds[] = (int) $tag->id;
                }
            }
        });

        $story->tags()->sync($finalTagIds);
    }
}
