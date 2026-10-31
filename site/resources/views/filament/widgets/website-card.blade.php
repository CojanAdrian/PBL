<x-filament-widgets::widget class="h-full">
    <div class="ca-glass ca-card flex h-full flex-col" wire:poll.30s.visible>
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="ca-label">{{ __('Your website') }}</p>
                <a href="{{ $url }}" target="_blank" class="mt-1 flex items-center gap-1.5 truncate text-[15px] font-semibold text-gray-950 hover:underline dark:text-white">
                    {{ $host }}
                    <x-filament::icon icon="heroicon-m-arrow-up-right" class="size-3.5 shrink-0 text-gray-400" />
                </a>
            </div>
            <span class="ca-chip ca-chip-success shrink-0" title="{{ __('Visitors in the last 5 minutes') }}">
                <span class="ca-live-dot"></span>{{ __(':count online', ['count' => $online]) }}
            </span>
        </div>

        <div class="mt-8">
            <p class="ca-number">{{ number_format($visitors) }}</p>
            <p class="ca-muted mt-1">
                {{ __('visitors in the last :days days', ['days' => $days]) }}
                · <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($views) }}</span> {{ __('page views') }}
            </p>
        </div>

        <div class="mt-auto grid grid-cols-5 gap-x-1 gap-y-5 pt-8">
            @foreach ($shortcuts as $shortcut)
                <a href="{{ $shortcut['url'] }}"
                   @if ($shortcut['external']) target="_blank" rel="noopener" @else wire:navigate @endif
                   class="ca-action">
                    <span class="ca-action-icon">
                        <x-filament::icon :icon="$shortcut['icon']" class="size-5" />
                    </span>
                    <span class="ca-action-label">{{ $shortcut['label'] }}</span>
                </a>
            @endforeach

            <button type="button" wire:click="mountAction('editShortcuts')" class="ca-action" title="{{ __('Edit shortcuts') }}">
                <span class="ca-action-icon ca-action-icon-ghost">
                    <x-filament::icon :icon="count($shortcuts) ? 'heroicon-o-adjustments-horizontal' : 'heroicon-o-plus'" class="size-5" />
                </span>
                <span class="ca-action-label">{{ count($shortcuts) ? __('Edit') : __('Add') }}</span>
            </button>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
