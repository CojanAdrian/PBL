<header class="ca-hero">
    <div class="min-w-0">
        <p class="ca-eyebrow">
            <span class="ca-live-dot"></span>
            {{ __('Updated :date at :time', ['date' => now()->format('d.m.Y'), 'time' => now()->format('H:i')]) }}
        </p>
        <h1 class="ca-display">{{ $this->getTitle() }}</h1>
        <p class="ca-hero-sub">
            {{ __('Welcome back, :name', ['name' => str(auth()->user()?->name)->before(' ')]) }}
        </p>
    </div>

    <div class="ca-segmented" role="tablist" aria-label="{{ __('Period') }}">
        @foreach ($periods as $value => $label)
            <button type="button" role="tab" wire:click="setPeriod('{{ $value }}')"
                    aria-selected="{{ $period === (string) $value ? 'true' : 'false' }}"
                    @class(['ca-segment', 'ca-segment-active' => $period === (string) $value])>
                {{ $label }}
            </button>
        @endforeach
    </div>
</header>
