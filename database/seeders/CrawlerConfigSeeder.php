<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CrawlerConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'chapter_list_selector' => 'ul.version-chap',
                'image_selector' => '.reading-content .item-list',
                'tags_selector' => '.genres-content',
                'author_selector' => '.author-content',
                'description_selector' => '.description-summary p',
                'domain' => 'hentaivnxx.com',
                'status_selector' => '.post-status .summary-content',
                'title_selector' => '.post-title h1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_list_selector' => '.list-chapter',
                'image_selector' => '.page-chapter',
                'tags_selector' => 'li.kind',
                'author_selector' => '.author.row',
                'description_selector' => null,
                'domain' => 'hentaivnx.us',
                'status_selector' => 'li.status p.col-xs-8',
                'title_selector' => 'h1.title-detail',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_list_selector' => '#list-chap li.chapter',
                'image_selector' => '.contentimg',
                'tags_selector' => '.thong-tin',
                'author_selector' => '.thong-tin',
                'description_selector' => '[itemprop="description"] p',
                'domain' => 'sayhentai.baby',
                'status_selector' => '.thong-tin span',
                'title_selector' => '.movie-detail h1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_list_selector' => '.box-list-chapter',
                'image_selector' => '.reading-content',
                'tags_selector' => '.genres-content',
                'author_selector' => '.post-content',
                'description_selector' => '.description-summary div div.seo-text',
                'status_selector' => null,
                'domain' => 'sayhentai.vc',
                'title_selector' => '.post-title h1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chapter_list_selector' => '#chapterList',
                'image_selector' => '#chapter-content',
                'tags_selector' => '#genres-list',
                'author_selector' => 'div.grow.flex.flex-col.min-w-0',
                'description_selector' => null,
                'domain' => 'damconuong.city',
                'status_selector' => 'div.grow.flex.flex-col.min-w-0 .dark:bg-yellow-900\\/40',
                'title_selector' => 'main h1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        \App\Models\CrawlerConfig::insert($configs);
    }
}
