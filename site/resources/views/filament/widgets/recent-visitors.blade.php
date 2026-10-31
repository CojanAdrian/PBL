<x-filament-widgets::widget class="h-full">
    <div class="ca-glass ca-card h-full" wire:poll.30s>
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="ca-title">{{ __('Live activity') }}</p>
                <p class="ca-muted">{{ __('Latest page views on your site') }}</p>
            </div>
            <span class="ca-live-dot"></span>
        </div>

        <ol class="mt-5 space-y-1">
            @forelse ($visits as $visit)
                <li class="ca-row -mx-3">
                    <span class="ca-flag-lg">
                        @if ($visit->country_code)
                            <img src="{{ asset('images/flags/'.strtolower($visit->country_code).'.svg') }}" alt="{{ $visit->country }}">
                        @else
                            <x-filament::icon icon="heroicon-o-globe-alt" class="size-4 text-gray-400" />
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium text-gray-950 dark:text-white">{{ $visit->location ?? __('Unknown location') }}</span>
                        <span class="flex items-center gap-1.5 truncate text-[12px] text-gray-500 dark:text-gray-400">
                            <x-filament::icon :icon="match ($visit->device) { 'mobile' => 'heroicon-m-device-phone-mobile', 'tablet' => 'heroicon-m-device-tablet', default => 'heroicon-m-computer-desktop' }" class="size-3.5 shrink-0" />
                            <span class="truncate">{{ $visit->path }}</span>
                        </span>
                    </span>
                    <span class="shrink-0 text-[12px] tabular-nums text-gray-400">{{ $visit->created_at->diffForHumans(short: true) }}</span>
                </li>
            @empty
                <li class="ca-muted py-8 text-center">{{ __('Visits to your website will show up here.') }}</li>
            @endforelse
        </ol>
    </div>
</x-filament-widgets::widget>
