<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use App\Support\Analytics\Geo;
use App\Support\Analytics\UserAgent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a page view for the dashboard. Runs after the response has been sent,
 * so the location lookup never slows the page down.
 */
class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldTrack($request, $response)) {
            return;
        }

        $agent = $request->userAgent();
        $referrer = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        Visit::create([
            // Same visitor within a day, without keeping their IP address.
            'visitor' => substr(hash('sha256', $request->ip().'|'.$agent.'|'.now()->toDateString().'|'.config('app.key')), 0, 32),
            'path' => '/'.ltrim(substr($request->path(), 0, 500), '/'),
            'referrer' => $referrer && $referrer !== $request->getHost() ? preg_replace('/^www\./', '', $referrer) : null,
            ...UserAgent::parse($agent),
            ...Geo::locate($request),
        ]);
    }

    protected function shouldTrack(Request $request, Response $response): bool
    {
        return config('analytics.enabled')
            && $request->isMethod('GET')
            && $response->isSuccessful()
            && $this->isPage($request, $response)
            && ! $request->is(...static::excludedPaths())
            && ! $request->user()
            && ! UserAgent::isBot($request->userAgent());
    }

    /** An HTML page, or an Inertia page visit from a single-page front-end. */
    protected function isPage(Request $request, Response $response): bool
    {
        return str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || $request->hasHeader('X-Inertia');
    }

    /** @return array<int, string> */
    protected static function excludedPaths(): array
    {
        $admin = trim(filament()->getDefaultPanel()->getPath(), '/');

        return [$admin, "{$admin}/*", 'livewire/*', 'filament/*', 'invite/*', 'cms/*', 'storage/*', 'up'];
    }
}
