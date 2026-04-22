<?php

namespace App\Services;

use Illuminate\Support\Str;
use ImageKit\ImageKit;

class ImageService
{
    protected $imageKit;

    public function __construct(ImageKit $imageKit)
    {
        $this->imageKit = $imageKit;
    }

    public function uploadToImageKit(string $sourceUrl, string $folder = '/stories'): ?string
    {
        $fileName = Str::random(10) . '_' . basename(parse_url($sourceUrl, PHP_URL_PATH));

        $upload = $this->imageKit->uploadFile([
            'file' => $sourceUrl,
            'fileName' => $fileName,
            'folder' => $folder,
            'useUniqueFileName' => true
        ]);

        return $upload->result->url ?? null;
    }
}
