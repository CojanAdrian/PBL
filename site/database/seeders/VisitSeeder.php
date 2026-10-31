<?php

namespace Database\Seeders;

use App\Models\Visit;
use App\Support\Analytics\Geo;
use Illuminate\Database\Seeder;

/**
 * Three months of made-up website traffic for the dashboard:
 *   php artisan db:seed --class=VisitSeeder
 */
class VisitSeeder extends Seeder
{
    protected const PLACES = [
        ['MD', 'Chișinău', 30], ['MD', 'Bălți', 6], ['RO', 'Bucharest', 14], ['RO', 'Iași', 6],
        ['RO', 'Cluj-Napoca', 5], ['UA', 'Kyiv', 5], ['DE', 'Berlin', 5], ['GB', 'London', 5],
        ['US', 'New York', 4], ['US', 'San Francisco', 2], ['IT', 'Rome', 3], ['FR', 'Paris', 3],
        ['ES', 'Madrid', 2], ['NL', 'Amsterdam', 2], ['PL', 'Warsaw', 2], ['TR', 'Istanbul', 2],
    ];

    protected const PAGES = [
        '/en' => 30, '/ro' => 18, '/en/services' => 14, '/en/news' => 8, '/en/contact' => 9,
        '/en/single-services/web-design' => 6, '/en/single-news/meet-the-team' => 4,
        '/en/faq' => 4, '/ro/services' => 5, '/en/terms' => 1, '/en/privacy-policy' => 1,
    ];

    protected const SOURCES = [
        '' => 34, 'google.com' => 38, 'facebook.com' => 10, 'instagram.com' => 8,
        'linkedin.com' => 4, 'bing.com' => 3, 'duckduckgo.com' => 2, 'news.ycombinator.com' => 1,
    ];

    protected const DEVICES = [
        ['desktop', 'Chrome', 'Windows', 26], ['desktop', 'Edge', 'Windows', 7], ['desktop', 'Safari', 'macOS', 9],
        ['desktop', 'Firefox', 'Linux', 3], ['mobile', 'Safari', 'iOS', 22], ['mobile', 'Chrome', 'Android', 25],
        ['mobile', 'Samsung Internet', 'Android', 3], ['tablet', 'Safari', 'iOS', 5],
    ];

    public function run(): void
    {
        $rows = [];

        foreach (range(89, 0) as $daysAgo) {
            $day = now()->subDays($daysAgo)->startOfDay();
            $growth = 1 + (89 - $daysAgo) / 120;
            $weekend = $day->isWeekend() ? 0.65 : 1;
            $visitors = (int) round(random_int(28, 48) * $growth * $weekend);

            foreach (range(1, $visitors) as $n) {
                [$code, $city] = $this->pick(self::PLACES);
                [$device, $browser, $os] = $this->pick(self::DEVICES);
                $referrer = $this->pickKey(self::SOURCES);
                $visitor = substr(hash('sha256', "{$daysAgo}-{$n}"), 0, 32);
                $at = $daysAgo === 0
                    ? now()->subMinutes(random_int(0, max(1, (int) $day->diffInMinutes(now()))))
                    : $day->copy()->addMinutes(random_int(7 * 60, 23 * 60));

                foreach (range(1, random_int(1, 4)) as $page) {
                    $rows[] = [
                        'visitor' => $visitor,
                        'path' => $this->pickKey(self::PAGES),
                        'referrer' => $page === 1 ? ($referrer ?: null) : null,
                        'country_code' => $code,
                        'country' => Geo::countryName($code),
                        'city' => $city,
                        'device' => $device,
                        'browser' => $browser,
                        'os' => $os,
                        'created_at' => $at->copy()->addMinutes($page - 1)->min(now()),
                    ];
                }
            }

            Visit::insert($rows);
            $rows = [];
        }

        // A few people on the site right now.
        foreach (range(1, 3) as $n) {
            $rows[] = [
                'visitor' => substr(hash('sha256', "now-{$n}"), 0, 32), 'path' => $this->pickKey(self::PAGES),
                'referrer' => 'google.com', 'country_code' => 'MD', 'country' => 'Moldova', 'city' => 'Chișinău',
                'device' => 'mobile', 'browser' => 'Chrome', 'os' => 'Android', 'created_at' => now()->subMinutes($n),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Visit::insert($chunk);
        }
    }

    /** Picks a row whose last element is its weight. */
    protected function pick(array $weighted): array
    {
        $roll = random_int(1, array_sum(array_column($weighted, count($weighted[0]) - 1)));

        foreach ($weighted as $row) {
            if (($roll -= end($row)) <= 0) {
                return $row;
            }
        }

        return $weighted[0];
    }

    /** Picks a key from a [key => weight] map. */
    protected function pickKey(array $weighted): string
    {
        $roll = random_int(1, array_sum($weighted));

        foreach ($weighted as $key => $weight) {
            if (($roll -= $weight) <= 0) {
                return (string) $key;
            }
        }

        return (string) array_key_first($weighted);
    }
}
