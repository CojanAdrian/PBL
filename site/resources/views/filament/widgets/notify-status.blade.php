<x-filament-widgets::widget>
    <div class="grid gap-3 sm:grid-cols-2 sm:gap-6">
        @foreach ($boxes as $box)
            @php($failed = $box['on'] && filled($box['lastError']))
            <div class="ca-glass ca-card flex items-start gap-3">
                <span @class([
                    'mt-1.5 size-2.5 shrink-0 rounded-full',
                    'bg-emerald-500' => $box['on'] && ! $failed,
                    'bg-rose-500' => $failed,
                    'bg-gray-400' => ! $box['on'],
                ])></span>
                <div class="min-w-0">
                    <p class="font-semibold">
                        {{ $box['title'] }}:
                        {{ ! $box['on'] ? 'off' : ($failed ? 'the last one failed' : 'on') }}
                    </p>
                    <p class="ca-muted mt-1 text-sm">{{ $box['on'] ? $box['onText'] : $box['offText'] }}</p>
                    @if ($failed)
                        <p class="mt-1 break-words text-sm text-rose-600 dark:text-rose-400">{{ $box['lastError'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
