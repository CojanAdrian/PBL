<?php

namespace App\Support\Analytics;

/**
 * Just enough user-agent parsing for the dashboard: device type, browser and OS family.
 */
class UserAgent
{
    public static function isBot(?string $agent): bool
    {
        return blank($agent)
            || preg_match('~bot|crawl|spider|slurp|preview|fetch|monitor|headless|lighthouse|curl|wget|python|scrapy~i', $agent);
    }

    /** @return array{device: string, browser: string, os: string} */
    public static function parse(?string $agent): array
    {
        $agent ??= '';

        return [
            'device' => match (true) {
                (bool) preg_match('~iPad|Tablet|(Android(?!.*Mobile))~i', $agent) => 'tablet',
                (bool) preg_match('~Mobi|iPhone|Android~i', $agent) => 'mobile',
                default => 'desktop',
            },
            'browser' => match (true) {
                str_contains($agent, 'Edg/') => 'Edge',
                str_contains($agent, 'OPR/') || str_contains($agent, 'Opera') => 'Opera',
                str_contains($agent, 'SamsungBrowser') => 'Samsung Internet',
                str_contains($agent, 'Firefox/') => 'Firefox',
                str_contains($agent, 'Chrome/') || str_contains($agent, 'CriOS') => 'Chrome',
                str_contains($agent, 'Safari/') => 'Safari',
                default => 'Other',
            },
            'os' => match (true) {
                (bool) preg_match('~iPhone|iPad|iPod~', $agent) => 'iOS',
                str_contains($agent, 'Android') => 'Android',
                str_contains($agent, 'Windows') => 'Windows',
                str_contains($agent, 'Mac OS X') => 'macOS',
                str_contains($agent, 'CrOS') => 'ChromeOS',
                str_contains($agent, 'Linux') => 'Linux',
                default => 'Other',
            },
        ];
    }
}
