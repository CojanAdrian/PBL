<?php

namespace App\Filament\Pages;

use App\Models\Lang;
use App\Models\Page as SitePage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;
use UnitEnum;

/**
 * The client's website inside the admin, with click-to-edit texts and images.
 */
class VisualEditor extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = -10;

    protected static ?string $slug = 'visual-editor';

    protected string $view = 'filament.pages.visual-editor';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function getNavigationLabel(): string
    {
        return __('Visual editor');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Visual editor');
    }

    public function getSubheading(): ?string
    {
        return __('Click any outlined text or image on your website to change it.');
    }

    protected function getViewData(): array
    {
        $languages = Lang::query()->where('active', true)->orderBy('id')->get(['code', 'name']);
        $default = config('app.locale');

        return [
            'languages' => $languages,
            'locale' => $languages->contains('code', $default) ? $default : $languages->first()?->code,
            'pages' => SitePage::query()
                ->where('active', true)
                ->with('meta.text')
                ->get()
                ->filter(fn (SitePage $page) => Route::has($page->label))
                ->map(fn (SitePage $page) => [
                    'title' => $page->meta?->translation()?->title ?? str($page->label)->afterLast('.')->headline()->toString(),
                    // The page's path with a {locale} placeholder, filled in by the browser.
                    'path' => parse_url(route($page->label, ['locale' => '__locale__']), PHP_URL_PATH),
                ])
                ->values(),
        ];
    }
}
