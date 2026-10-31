<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VisitTrackingTest extends TestCase
{
    protected string $browser = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1';

    public function test_public_page_views_are_recorded_without_the_ip(): void
    {
        $this->withHeaders(['User-Agent' => $this->browser, 'Referer' => 'https://www.google.com/search?q=x'])
            ->get('/en')
            ->assertOk();

        $visit = Visit::sole();
        $this->assertSame('/en', $visit->path);
        $this->assertSame('google.com', $visit->referrer);
        $this->assertSame(['mobile', 'Safari', 'iOS'], [$visit->device, $visit->browser, $visit->os]);
        $this->assertSame(32, strlen($visit->visitor));
        $this->assertStringNotContainsString('127.0.0.1', json_encode($visit->getAttributes()));
    }

    public function test_location_comes_from_cloudflare_headers(): void
    {
        $this->withHeaders(['User-Agent' => $this->browser, 'CF-IPCountry' => 'MD', 'CF-IPCity' => 'Chișinău'])
            ->get('/en');

        $this->assertSame(['MD', 'Moldova', 'Chișinău'], [Visit::sole()->country_code, Visit::sole()->country, Visit::sole()->city]);
    }

    public function test_location_is_looked_up_for_public_ips(): void
    {
        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success', 'countryCode' => 'RO', 'city' => 'Iași'])]);

        $this->withHeaders(['User-Agent' => $this->browser])
            ->withServerVariables(['REMOTE_ADDR' => '81.180.70.1'])
            ->get('/en');

        $this->assertSame(['RO', 'Romania', 'Iași'], [Visit::sole()->country_code, Visit::sole()->country, Visit::sole()->city]);
    }

    public function test_behind_railways_proxies_the_real_visitor_is_located(): void
    {
        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success', 'countryCode' => 'MD', 'city' => 'Chisinau'])]);

        // As on Railway: visitor -> CDN server (Bucharest) -> Railway edge -> the app.
        $this->withHeaders(['User-Agent' => $this->browser, 'X-Forwarded-For' => '89.28.1.2, 151.101.2.3', 'X-Forwarded-Proto' => 'https'])
            ->withServerVariables(['REMOTE_ADDR' => '10.250.1.1'])
            ->get('/en');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'ip-api.com/json/89.28.1.2'));
        $this->assertSame(['MD', 'Chisinau'], [Visit::sole()->country_code, Visit::sole()->city]);
    }

    public function test_pages_added_outside_the_language_routes_are_counted_too(): void
    {
        Route::middleware('web')->get('/landing-page', fn () => response('<h1>Landing</h1>')->header('Content-Type', 'text/html'));

        $this->withHeaders(['User-Agent' => $this->browser])->get('/landing-page')->assertOk();

        $this->assertSame('/landing-page', Visit::sole()->path);
    }

    public function test_bots_admins_and_the_admin_panel_are_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->get('/en');
        $this->withHeaders(['User-Agent' => $this->browser])->get('/admin/login')->assertOk();
        $this->actingAs(User::factory()->admin()->create())->withHeaders(['User-Agent' => $this->browser])->get('/en');
        $this->get('/admin');

        $this->assertSame(0, Visit::count());
    }
}
