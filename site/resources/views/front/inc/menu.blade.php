<header class="sticky top-0 z-40 border-b border-gray-900/5 bg-white/80 backdrop-blur-xl">
    <nav class="mx-auto flex max-w-6xl items-center gap-8 px-6 py-4">
        <a href="{{ route('front.index') }}" class="text-lg font-semibold tracking-tight">@text('site_name', 'Studio Nord')</a>

        <ul class="hidden flex-1 items-center gap-6 text-sm text-gray-600 md:flex">
            @foreach (getMenuPages() as $page)
                @if (Route::has($page->label))
                    <li>
                        <a href="{{ route($page->label, app()->getLocale()) }}"
                           @class(['transition hover:text-gray-950', 'font-semibold text-gray-950' => request()->routeIs($page->label)])>
                            {{ $page->title }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>

        <a href="{{ route('front.contact') }}" class="ms-auto rounded-full bg-gray-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 md:ms-0">
            @text('nav_cta', 'Start a project')
        </a>
    </nav>
</header>
