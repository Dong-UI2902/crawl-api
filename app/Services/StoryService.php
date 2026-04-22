<?php

namespace App\Services;

use App\Models\Author;
use App\Models\CrawlerConfig;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class StoryService
{
    public function scrapeStory(Crawler $crawler, CrawlerConfig $config)
    {
        $title = $this->getTitle($crawler, $config->title_selector);
        $description = $this->getDescription($crawler, $config->decription_selector);

        $status = $this->getStatus($crawler, $config->status_selector);

        $authorName = $this->getAuthorName($crawler, $config->author_selector);
        $author = $this->getOrCreateAuthor($authorName);

        $thumbnail = $this->getThumbnail($crawler, $title);

        return [
            'title' => $title,
            'description' => $description,
            'slug' => Str::slug($title),
            'thumbnail' => $thumbnail,
            'author_id' => $author->id,
            'status' => $status,
        ];
    }

    private function getThumbnail(Crawler $crawler, string $title)
    {
        $xpath = "//img[@alt='" . addslashes($title) . "']";
        $coverNode = $crawler->filterXPath($xpath);
        if ($coverNode->count() > 0) {
            return $coverNode->attr('src') ?? $coverNode->attr('data-src');
        }

        return null;
    }

    private function getTitle(Crawler $crawler, ?string $config)
    {
        if ($config) {
            $title = $crawler->filter($config)->first();

            return $title->text() ?? $crawler->filter('title')->text();
        }

        return null;
    }

    private function getDescription(Crawler $crawler, ?string $config)
    {
        if ($config) {
            $decription = $crawler->filter($config)->first();

            return $decription->text() ?? null;
        }

        return null;
    }

    private function getAuthorName(Crawler $crawler, ?string $config): ?string
    {
        $author = $crawler->filter($config)->filter('a')->reduce(function ($node) {
            $href = $node->attr('href');
            // Trả về true nếu URL là link tác giả
            return str_contains($href, 'tac-gia') || str_contains($href, 'author');
        })->first();

        if ($author->count() > 0) {
            return trim($author->text());
        }

        return null;
    }

    private function getOrCreateAuthor(?string $name)
    {
        $name = $name ?: 'Đang cập nhật';

        return Author::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name)]
        );
    }

    private function getStatus(Crawler $crawler, ?string $config)
    {
        $xpath = "//*[contains(@class, '$config')]";
        $status = $crawler->filterXPath($xpath)->reduce(function ($node) {
            $statusText = trim($node->text());
            return str_contains(strtolower($statusText), 'Đã hoàn thành') || str_contains(strtolower($statusText), 'complete');
        })->first();

        if (!$status || $status->count() === 0) {
            return 'Đang tiến hành';
        }

        return 'Đã hoàn thành';
    }
}
