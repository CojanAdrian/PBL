<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Inbox;
use App\Models\News;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Sample content so the dashboard has something to show:
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $placeholder = file_get_contents(public_path('images/demo.svg'));
        Storage::disk('news')->put('demo.svg', $placeholder);
        Storage::disk('service')->put('demo.svg', $placeholder);

        $names = ['Ana Popescu', 'Ion Rusu', 'Maria Ceban', 'Victor Lungu', 'Elena Moraru', 'Andrei Țurcan'];
        $subjects = ['Request a quote', 'Website redesign', 'Partnership', 'Support question', 'Pricing', 'Hello!'];

        foreach (range(1, 45) as $i) {
            $name = fake()->randomElement($names);
            $createdAt = now()->subDays(fake()->numberBetween(0, 29))->subMinutes(fake()->numberBetween(0, 1440));

            Inbox::forceCreate([
                'name' => $name,
                'email' => str($name)->ascii()->lower()->replace(' ', '.').'@example.com',
                'phone' => '+373 6'.fake()->numerify('#######'),
                'subject' => fake()->randomElement($subjects),
                'message' => fake()->paragraph(3),
                'read_at' => $i > 6 ? $createdAt->copy()->addHour() : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        foreach (['Launching our new website', 'Five tips for faster pages', 'Meet the team'] as $i => $title) {
            $news = News::create(['img' => 'demo.svg', 'cover_img' => 'demo.svg', 'active' => $i < 2]);
            $news->text()->create([
                'lang' => 'en', 'title' => $title, 'sub_title' => 'Company news',
                'excerpt' => fake()->sentence(12), 'text' => '<p>'.fake()->paragraph(5).'</p>',
            ]);
        }

        foreach (['Web design', 'Online stores', 'SEO & analytics', 'Hosting & support'] as $title) {
            $service = Service::create(['img' => 'demo.svg', 'active' => true]);
            $service->text()->create([
                'lang' => 'en', 'title' => $title, 'sub_title' => fake()->sentence(6), 'text' => '<p>'.fake()->paragraph(4).'</p>',
            ]);
        }

        foreach (['How long does a project take?', 'Can I edit the content myself?', 'Do you offer hosting?'] as $question) {
            $faq = Faq::create(['active' => true]);
            $faq->text()->create(['lang' => 'en', 'title' => $question, 'text' => '<p>'.fake()->paragraph(2).'</p>']);
        }

        $this->call(VisitSeeder::class);
    }
}
