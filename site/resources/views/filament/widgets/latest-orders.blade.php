<x-filament-widgets::widget class="h-full">
    <div class="ca-glass ca-card h-full">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="ca-title">{{ __('Orders') }}</p>
                <p class="ca-muted">{{ __('Latest preorders from the website') }}</p>
            </div>
            <a href="{{ $ordersUrl }}" wire:navigate class="ca-pill-btn">
                {{ __('View all') }}
                <x-filament::icon icon="heroicon-m-arrow-right" class="size-3.5" />
            </a>
        </div>

        @forelse ($orders as $order)
            @if ($loop->first)
                <ul class="-mx-3 mt-4">
            @endif
                    <li>
                        <a href="{{ $ordersUrl }}" wire:navigate class="ca-row">
                            <span class="ca-avatar" style="--hue: {{ crc32($order->phone) % 360 }}">
                                {{ str($order->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2">
                                    <span class="truncate text-[13px] font-semibold text-gray-950 dark:text-white">{{ $order->name }}</span>
                                    <span class="shrink-0 text-[12px] text-gray-400">{{ $order->code }}</span>
                                </span>
                                <span class="block truncate text-[12px] text-gray-500 dark:text-gray-400">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $order->total_price }} {{ $order->currency }}</span> — {{ implode(', ', $order->lines()) }}
                                </span>
                            </span>
                            <span class="shrink-0 text-[12px] tabular-nums text-gray-400">{{ $order->created_at->diffForHumans(short: true) }}</span>
                        </a>
                    </li>
            @if ($loop->last)
                </ul>
            @endif
        @empty
            <div class="flex flex-col items-center py-12 text-center">
                <span class="ca-action-icon"><x-filament::icon icon="heroicon-o-clipboard-document-list" class="size-5" /></span>
                <p class="ca-title mt-3">{{ __('No orders yet') }}</p>
                <p class="ca-muted">{{ __('Preorders from the website will show up here.') }}</p>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>
