<x-filament-panels::page>
    <div
        x-data="{
            pages: @js($pages),
            locale: @js($locale),
            path: @js(str_replace('__locale__', $locale, $pages->first()['path'] ?? '/'.$locale)),
            device: 'desktop',
            editing: true,
            loading: true,
            src() {
                const url = new URL(this.path, window.location.origin)
                if (this.editing) url.searchParams.set('cms_edit', '1')
                return url.toString()
            },
            open(path) {
                this.path = path
                this.reload()
            },
            setLocale(code) {
                const segments = this.path.split('/')
                if (segments.length > 1) segments[1] = code
                this.locale = code
                this.open(segments.join('/') || '/' + code)
            },
            reload() {
                this.loading = true
                this.$refs.frame.src = this.src()
            },
            isCurrent(page) {
                return this.path === page.path.replace('__locale__', this.locale)
            },
        }"
        x-on:message.window="if ($event.origin === window.location.origin && $event.data?.type === 'cms:page') { path = $event.data.path; loading = false }"
        x-init="reload()"
        class="ca-editor"
    >
        <div class="ca-editor-toolbar">
            <div class="ca-segmented ca-editor-pages">
                <template x-for="page in pages" :key="page.path">
                    <button type="button" class="ca-segment" :class="{ 'ca-segment-active': isCurrent(page) }"
                            x-on:click="open(page.path.replace('__locale__', locale))" x-text="page.title"></button>
                </template>
            </div>

            <div class="ca-editor-controls">
                <div class="ca-segmented" role="group" aria-label="{{ __('Language') }}">
                    @foreach ($languages as $language)
                        <button type="button" class="ca-segment" :class="{ 'ca-segment-active': locale === @js($language->code) }"
                                x-on:click="setLocale(@js($language->code))" title="{{ $language->name }}">
                            {{ strtoupper($language->code) }}
                        </button>
                    @endforeach
                </div>

                <div class="ca-segmented" role="group" aria-label="{{ __('Device') }}">
                    @foreach (['desktop' => 'heroicon-o-computer-desktop', 'tablet' => 'heroicon-o-device-tablet', 'mobile' => 'heroicon-o-device-phone-mobile'] as $size => $icon)
                        <button type="button" class="ca-segment ca-segment-icon" :class="{ 'ca-segment-active': device === @js($size) }"
                                x-on:click="device = @js($size)" title="{{ __(ucfirst($size)) }}">
                            <x-filament::icon :icon="$icon" class="size-4" />
                        </button>
                    @endforeach
                </div>

                <button type="button" class="ca-pill-btn" :class="{ 'ca-pill-btn-on': editing }" x-on:click="editing = ! editing; reload()">
                    <x-filament::icon icon="heroicon-m-pencil-square" class="size-4" />
                    <span x-text="editing ? @js(__('Editing')) : @js(__('Preview'))"></span>
                </button>
            </div>
        </div>

        <div class="ca-glass ca-browser" :class="'ca-browser-' + device">
            <div class="ca-browser-bar">
                <span class="ca-browser-dots"><i></i><i></i><i></i></span>
                <span class="ca-browser-url">
                    <x-filament::icon icon="heroicon-m-lock-closed" class="size-3.5 shrink-0" />
                    <span class="truncate" x-text="window.location.host + path"></span>
                </span>
                <span class="ca-browser-actions">
                    <button type="button" x-on:click="reload()" title="{{ __('Reload') }}">
                        <x-filament::icon icon="heroicon-m-arrow-path" class="size-4" />
                    </button>
                    <a :href="path" target="_blank" title="{{ __('Open in a new tab') }}">
                        <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="size-4" />
                    </a>
                </span>
            </div>

            <div class="ca-browser-viewport">
                <iframe x-ref="frame" x-on:load="loading = false" title="{{ __('Website preview') }}"></iframe>
                <div class="ca-browser-loading" x-show="loading" x-transition.opacity>
                    <x-filament::loading-indicator class="size-6" />
                </div>
            </div>
        </div>

        <p class="ca-editor-hint">
            <span><i class="ca-hint-swatch"></i>{{ __('Editable') }}</span>
            <span><i class="ca-hint-swatch ca-hint-missing"></i>{{ __('Not translated yet in this language') }}</span>
            <span>{{ __('Enter saves · Shift+Enter adds a line · Esc cancels') }}</span>
        </p>
    </div>
</x-filament-panels::page>
