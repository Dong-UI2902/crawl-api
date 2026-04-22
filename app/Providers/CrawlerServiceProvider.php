<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use ImageKit\ImageKit;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class CrawlerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(HttpBrowser::class, function ($app) {
            return new HttpBrowser(HttpClient::create([
                'timeout' => 30,
                'max_redirects' => 5,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ],
            ]));
        });

        $this->app->singleton(ImageKit::class, function ($app) {
            return new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_URL_ENDPOINT')
            );
         });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
