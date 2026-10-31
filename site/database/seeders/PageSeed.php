<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeed extends Seeder
{
    /**
     * Pages are ordered to match the Page::HOME… constants; labels are front-end route names.
     */
    public function run(): void
    {
        $pages = [
            ['label' => 'front.index', 'title' => 'Home'],
            ['label' => 'front.service', 'title' => 'Services'],
            ['label' => 'front.portfolio', 'title' => 'Portfolio', 'active' => false],
            ['label' => 'front.news', 'title' => 'News'],
            ['label' => 'front.contact', 'title' => 'Contact'],
            ['label' => 'front.privacy', 'title' => 'Privacy policy'],
            ['label' => 'front.faq', 'title' => 'FAQ'],
            ['label' => 'front.terms', 'title' => 'Terms & conditions'],
        ];

        foreach ($pages as $page) {
            $model = Page::create(['label' => $page['label'], 'active' => $page['active'] ?? true]);

            $model->meta()->create()->text()->create([
                'lang' => config('app.locale'),
                'title' => $page['title'],
            ]);
        }
    }
}
