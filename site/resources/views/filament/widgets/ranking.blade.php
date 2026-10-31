<x-filament-widgets::widget class="h-full">
    <div class="ca-glass ca-card h-full">
        <div class="flex items-baseline justify-between gap-4">
            <div>
                <p class="ca-title">{{ $heading }}</p>
                <p class="ca-muted">{{ $description }}</p>
            </div>
            <span class="text-[12px] font-medium text-gray-400">{{ $valueLabel }}</span>
        </div>

        <ul class="mt-5 space-y-1.5">
            @forelse ($rows as $row)
                <li class="ca-rank">
                    <span class="ca-rank-bar" style="width: {{ max(2, $row['share']) }}%"></span>
                    <span class="relative flex min-w-0 flex-1 items-center gap-2.5">
                        @if ($row['flag'] ?? null)
                            <img src="{{ asset('images/flags/'.strtolower($row['flag']).'.svg') }}" alt="" class="ca-flag">
                        @elseif ($row['icon'] ?? null)
                            <x-filament::icon :icon="$row['icon']" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" />
                        @endif
                        <span class="truncate text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $row['label'] }}</span>
                        @if ($row['sub'] ?? null)
                            <span class="truncate text-[12px] text-gray-500 dark:text-gray-400">{{ $row['sub'] }}</span>
                        @endif
                    </span>
                    <span class="relative text-[13px] font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($row['value']) }}</span>
                </li>
            @empty
                <li class="ca-muted py-8 text-center">{{ __('No visits in this period yet.') }}</li>
            @endforelse
        </ul>
    </div>
</x-filament-widgets::widget>
