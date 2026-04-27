<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\CrawlerConfig;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;

class CrawlService
{
    protected $browser;

    public function __construct(HttpBrowser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Tìm config phù hợp nhất bằng cách thử sai
     */
    public function getConfig(string $url, Crawler $crawler)
    {
        $host = $this->getDomain($url);
        $cacheKey = "crawler_config_" . $host;

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($host, $crawler) {
            $exactConfig = CrawlerConfig::where('domain', $host)->first();
            if ($exactConfig) return $exactConfig;

            $rootName = explode('.', $host)[0];
            $potentialConfigs = CrawlerConfig::where('domain', 'LIKE', '%' . $rootName . '%')->get();

            if ($potentialConfigs->count() === 1) return $potentialConfigs;

            foreach ($potentialConfigs as $config) {
                if ($crawler->filter($config->tags_selector)->count() > 0) {
                    $this->autoUpdateDomain($config, $host);

                    return $config;
                }
            }

            return null;
        });

    }

    public function getDomain(string $source_url) {
        $host = parse_url($source_url, PHP_URL_HOST);

        return preg_replace('/^www\./', '', $host);
    }

    /**
     * Tự động thêm domain mới vào bộ config cũ
     */
    private function autoUpdateDomain($config, $host)
    {
        $config->update(['domain' => $host]);

        Log::info("Đã cập nhật tên miền [$host] vào config [{$config->domain}]");
    }

    public function crawlChapters(Crawler $crawler, string $source_url): array
    {
        $chapters = [];

        $host = $this->getDomain($source_url);
        $siteKey = explode('.', $host)[0];

        if ($siteKey === "hentaivnxx") {
            return $this->crawlChaptersByAjax($source_url);
        }

        $mainContent = $crawler->filter('main, #main-content, .post-body, .site-content')->first();

        $xpath = "(//*[contains(translate(normalize-space(.), 'DANH SÁCH CHƯƠNG', 'danh sách chương'), 'danh sách chương') or contains(translate(normalize-space(.), 'CHAPTER', 'chapter'), 'chapter')]/following::ul)[1]";

        $chapterNode = $mainContent->filterXPath($xpath);
        $chapterNode->filter('a')->each(function (Crawler $linkNode) use (&$chapters) {
            $chapterName = $linkNode->text();

            if ($linkNode->filter('span')->count() > 0) {
                $chapterName = $linkNode->filter('span')->first()->text();
            }

            $chapters[] = [
                'title' => trim($chapterName),
                'source_url' => $linkNode->attr('href'),
                'slug' => Str::slug($chapterName),
            ];
        });

        return array_reverse($chapters);
    }

    private function crawlChaptersByAjax($sourceUrl): array
    {
        $chapters = [];
        $cleanUrl = preg_replace('/\/$/', "", $sourceUrl);
        $ajaxUrl = "{$cleanUrl}/ajax/chapters/?t=1";

        $response = $this->browser->request('POST', $ajaxUrl);

        $response->filter('ul a')->each(function (Crawler $node) use (&$chapters) {
            $name = trim($node->text());

            // Xử lý dấu chấm để tránh biến 4.2 thành 42
            $slugName = str_replace('.', '-', $name);

            $chapters[] = [
                'title' => $name,
                'source_url' => $node->attr('href'),
                'slug' => Str::slug($slugName),
            ];
        });

        return array_reverse($chapters);
    }

    public function crawlImages(Chapter $chapter): array
    {
        $crawler = $this->browser->request('GET', $chapter->source_url);
        $config = $this->getConfig($chapter->source_url, $crawler);

        $imageList = [];


        $imageNode = $crawler->filter($config->image_selector);

        if (!$imageNode) {
            $imageNode = $crawler->filter('div')->reduce(function ($node) {
                // Chỉ giữ lại các div có trên 3 ảnh
                return $node->filter('img')->count() > 3;
            })->first();
        }

        $imageNode->filter('img')->each(function ($image, $i) use (&$imageList) {
            $src = $image->attr('data-src');

            if (!$src || str_contains($src, 'data:image/svg+xml')) {
                $src = $image->attr('src');
            }

            $imageList[] = [
                'src' => $src,
                'order' => $i,
            ];
        });

        return array_filter($imageList);
    }

    public function crawlTags(Story $story)
    {
        $crawler = $this->browser->request('GET', $story->source_url);
        $config = $this->getConfig($story->source_url, $crawler);

        $rawTags = $crawler->filter($config->tags_selector)->filter('a')->each(function ($node) {
            $href = (string)$node->attr('href');
            $text = trim((string)$node->text());

            if (str_contains($href, 'the-loai') || str_contains($href, 'genre')) {
                return [
                    'name' => $text,
                    'slug' => Str::slug($text)
                ];
            }
            return null;
        });

        return collect($rawTags)
                ->filter()
                ->unique('name')
                ->values()
                ->all() ?? null;
    }
}
