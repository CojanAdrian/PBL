<?php

namespace App\Support\Cms;

use App\Models\Content;
use App\Models\Image;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

/**
 * Editable texts and images for the public site, used from Blade:
 *
 *   <h1>@text('hero_title', 'Welcome to our studio')</h1>
 *   <img @image('hero', 'images/hero.jpg') alt="">
 *
 * A key is created the first time a page uses it, with the default text in the
 * site's main language, so it shows up under Text blocks / Images right away.
 * In the admin's Visual editor these elements become click-to-edit.
 */
class Cms
{
    /** @var Collection<string, Content>|null */
    protected ?Collection $contents = null;

    /** @var Collection<string, Image>|null */
    protected ?Collection $images = null;

    protected ?bool $editing = null;

    /** The request the cached values above belong to. */
    protected ?int $requestId = null;

    /** True while an admin views the site inside the Visual editor. */
    public function editing(): bool
    {
        $this->forCurrentRequest();

        return $this->editing ??= request()->query('cms_edit') === '1'
            && (bool) request()->user()?->isAdmin();
    }

    /** Drops cached values when the app serves a new request (tests, Octane). */
    protected function forCurrentRequest(): void
    {
        if ($this->requestId !== ($id = spl_object_id(request()))) {
            [$this->requestId, $this->contents, $this->images, $this->editing] = [$id, null, null, null];
        }
    }

    /** The text as a plain string, e.g. for alt="" or title="" attributes (not click-to-edit). */
    public function plain(string $key, string $default = ''): string
    {
        $rows = $this->content($key, $default)->text;

        return $rows->firstWhere('lang', app()->getLocale())?->text
            ?? $rows->firstWhere('lang', $this->mainLocale())?->text
            ?? $default;
    }

    public function text(string $key, string $default = ''): HtmlString
    {
        $translation = $this->content($key, $default)->text->firstWhere('lang', app()->getLocale());
        $text = $this->plain($key, $default);
        // <br> only (no kept newline), so the editor can show line breaks with white-space: pre-wrap.
        $html = str_replace(["\r\n", "\n"], '<br>', e($text));

        if (! $this->editing()) {
            return new HtmlString($html);
        }

        return new HtmlString(sprintf(
            '<span data-cms-text="%s"%s>%s</span>',
            e($key),
            $translation ? '' : ' data-cms-missing',
            $html,
        ));
    }

    /** The image's URL, or the template's default when nothing was uploaded yet. */
    public function imageUrl(string $key, string $default = ''): string
    {
        $image = $this->images()->get($key);

        return filled($image?->img)
            ? Storage::disk('image')->url($image->img)
            : asset($default);
    }

    /** `src="…"` for an <img>, plus the edit hook inside the Visual editor. */
    public function imageAttributes(string $key, string $default = ''): HtmlString
    {
        $attributes = 'src="'.e($this->imageUrl($key, $default)).'"';

        if ($this->editing()) {
            $attributes .= ' data-cms-image="'.e($key).'"';
        }

        return new HtmlString($attributes);
    }

    /** The `pages` row for the current route, used to group new keys in the admin. */
    public function currentPageId(): ?int
    {
        $route = Route::currentRouteName();

        return $route ? Page::where('label', $route)->value('id') : null;
    }

    /** The site's main language. Not app.locale, which follows the visitor's language. */
    protected function mainLocale(): string
    {
        return config('app.fallback_locale');
    }

    protected function content(string $key, string $default): Content
    {
        $this->forCurrentRequest();
        $this->contents ??= Content::with('text')->get()->keyBy('key');

        if (! $this->contents->has($key)) {
            $content = Content::create(['key' => $key, 'page_id' => $this->currentPageId()]);

            if ($default !== '') {
                $content->text()->create(['lang' => $this->mainLocale(), 'text' => $default]);
            }

            $this->contents->put($key, $content->load('text'));
        }

        return $this->contents->get($key);
    }

    /** @return Collection<string, Image> */
    protected function images(): Collection
    {
        $this->forCurrentRequest();

        return $this->images ??= Image::all()->keyBy('key');
    }
}
