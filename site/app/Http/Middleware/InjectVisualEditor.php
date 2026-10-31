<?php

namespace App\Http\Middleware;

use App\Support\Cms\Cms;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the click-to-edit script to public pages opened inside the Visual editor.
 */
class InjectVisualEditor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $cms = app(Cms::class);

        if (! $cms->editing() || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = (string) $response->getContent();
        $editor = view('cms.editor', [
            'config' => [
                'locale' => app()->getLocale(),
                'page' => $cms->currentPageId(),
                'csrf' => csrf_token(),
                'textUrl' => route('cms.text'),
                'imageUrl' => route('cms.image'),
            ],
        ])->render();

        $position = strripos($content, '</body>');
        $response->setContent($position === false ? $content.$editor : substr_replace($content, $editor, $position, 0));

        return $response;
    }
}
