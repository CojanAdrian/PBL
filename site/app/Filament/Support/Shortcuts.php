<?php

namespace App\Filament\Support;

use App\Filament\Resources\Inboxes\InboxResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;

/**
 * The dashboard's quick actions. Each user can arrange their own; they are kept
 * in `users.shortcuts` as [label, icon, target, url] where target is either
 * "ResourceClass@page" or "custom" (then `url` is used).
 */
class Shortcuts
{
    public const MAX = 8;

    /** @return array<int, array{label: string, icon: string, target: string, url: ?string}> */
    public static function defaults(): array
    {
        return [
            ['label' => __('Article'), 'icon' => 'pencil-square', 'target' => NewsResource::class.'@create', 'url' => null],
            ['label' => __('Service'), 'icon' => 'plus', 'target' => ServiceResource::class.'@create', 'url' => null],
            ['label' => __('Inbox'), 'icon' => 'inbox', 'target' => InboxResource::class.'@index', 'url' => null],
            ['label' => __('Invite'), 'icon' => 'user-plus', 'target' => UserResource::class.'@index', 'url' => null],
        ];
    }

    /** The user's saved shortcuts, or the defaults. */
    public static function stored(User $user): array
    {
        return $user->shortcuts ?? static::defaults();
    }

    /** @return array<int, array{label: string, icon: string, url: string, external: bool}> */
    public static function resolve(User $user): array
    {
        return collect(static::stored($user))
            ->map(function (array $shortcut) {
                $url = ($shortcut['target'] ?? null) === 'custom'
                    ? ($shortcut['url'] ?? null)
                    : static::urlFor($shortcut['target'] ?? '');

                return $url ? [
                    'label' => $shortcut['label'],
                    'icon' => 'heroicon-o-'.(array_key_exists($shortcut['icon'] ?? '', static::icons()) ? $shortcut['icon'] : 'link'),
                    'url' => $url,
                    'external' => ! str_starts_with($url, url('/')),
                ] : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /** Admin pages a shortcut can open, as [target => label]. */
    public static function destinations(): array
    {
        $destinations = [];

        foreach (Filament::getResources() as $resource) {
            if (! $resource::canAccess()) {
                continue;
            }

            $pages = $resource::getPages();
            $destinations["{$resource}@index"] = (string) $resource::getNavigationLabel();

            if (isset($pages['create']) && $resource::canCreate()) {
                $destinations["{$resource}@create"] = __(':section › New', ['section' => $resource::getNavigationLabel()]);
            }
        }

        asort($destinations);

        return $destinations + ['custom' => __('Custom link…')];
    }

    /** Icons to pick from, as [heroicon name => label]. */
    public static function icons(): array
    {
        return [
            'pencil-square' => 'Write', 'plus' => 'Add', 'inbox' => 'Inbox', 'envelope' => 'Mail',
            'user-plus' => 'Invite', 'users' => 'People', 'newspaper' => 'News', 'briefcase' => 'Services',
            'photo' => 'Images', 'document-text' => 'Page', 'question-mark-circle' => 'FAQ', 'chat-bubble-left-right' => 'Reviews',
            'share' => 'Social', 'language' => 'Languages', 'globe-alt' => 'Website', 'chart-bar' => 'Stats',
            'shopping-bag' => 'Shop', 'calendar' => 'Calendar', 'megaphone' => 'Announce', 'star' => 'Star',
            'cog-6-tooth' => 'Settings', 'link' => 'Link', 'bolt' => 'Quick', 'heart' => 'Favourite',
        ];
    }

    /** Icon options with a preview, for a Select with allowHtml(). */
    public static function iconOptions(): array
    {
        return collect(static::icons())
            ->mapWithKeys(fn (string $label, string $name) => [
                $name => '<span style="display:inline-flex;align-items:center;gap:.5rem">'
                    .svg("heroicon-o-{$name}", '', ['style' => 'width:1.1rem;height:1.1rem'])->toHtml()
                    .e(__($label)).'</span>',
            ])
            ->all();
    }

    protected static function urlFor(string $target): ?string
    {
        [$resource, $page] = array_pad(explode('@', $target, 2), 2, 'index');

        if (! is_a($resource, Resource::class, true) || ! array_key_exists($page, $resource::getPages()) || ! $resource::canAccess() || ($page === 'create' && ! $resource::canCreate())) {
            return null;
        }

        return $resource::getUrl($page);
    }
}
