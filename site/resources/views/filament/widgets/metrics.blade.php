<x-filament-widgets::widget class="h-full">
    <div class="grid h-full grid-cols-2 gap-3 sm:gap-6 xl:grid-cols-4">
        @foreach ($tiles as $tile)
            @php($max = max(1, ...$tile['bars']))
            @php($tag = $tile['url'] ? 'a' : 'div')
            <{{ $tag }} @if ($tile['url']) href="{{ $tile['url'] }}" wire:navigate @endif class="ca-glass ca-card ca-tile ca-tint-{{ $tile['tint'] }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="ca-label whitespace-nowrap">{{ $tile['label'] }}</span>
                    @if ($tile['change'] !== null)
                        <span @class(['ca-chip', 'ca-chip-success' => $tile['change'] >= 0, 'ca-chip-danger' => $tile['change'] < 0])>
                            {{ $tile['change'] >= 0 ? '+' : '' }}{{ $tile['change'] }}%
                        </span>
                    @endif
                </div>

                <p class="ca-number mt-6">{{ number_format($tile['value']) }}</p>
                <p class="ca-muted mt-1 truncate">{{ $tile['caption'] }}</p>

                <div class="ca-ticks mt-auto pt-5" aria-hidden="true">
                    @foreach ($tile['bars'] as $bar)
                        <span style="height: {{ max(10, round($bar / $max * 100)) }}%" @class(['ca-tick-on' => $bar > 0])></span>
                    @endforeach
                </div>
            </{{ $tag }}>
        @endforeach
    </div>
</x-filament-widgets::widget>
