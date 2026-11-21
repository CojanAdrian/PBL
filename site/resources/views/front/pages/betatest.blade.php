{{--
    The redesigned Big Ed's page, tested at /{locale}/betatest before it replaces
    home.blade.php. Same products, prices and order endpoint as the main page;
    texts in App\Shop\BetaTexts (order-form errors still come from Text blocks).

    The story section is tall and its stage sticks to the screen: scrolling turns
    the 3D pack (public/js/beta3d.js); each half turn shows the next flavour.
    The captions, the "Choose" cards and the order panel are drawn by public/js/beta.js.
--}}
@php
    $cms = app(\App\Support\Cms\Cms::class);
    $langs = \App\Models\Lang::where('active', true)->orderBy('id')->get(['code', 'name']);
    $locale = app()->getLocale();
    $ingredients = \App\Models\Ingredient::active()->with('text')->orderBy('order_column')->get()
        ->map(fn ($i) => $i->translation()?->name)->filter()->values();
    $contact = collect([
        'phone' => $cms->plain('footer_phone'),
        'email' => $cms->plain('footer_email'),
        'instagram' => ltrim($cms->plain('footer_instagram'), '@'),
        'telegram' => ltrim($cms->plain('footer_telegram'), '@'),
    ])->filter();
    $packGrams = collect($menu['flavours'])->pluck('grams')->unique()->count() === 1 ? $menu['flavours'][0]['grams'] : 100;
    $v = fn ($file) => asset($file).'?v='.filemtime(public_path($file));
@endphp
<!doctype html>
<html lang="{{ $locale }}" class="no-js">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>{{ $b['title'] }}</title>
  <meta name="description" content="{{ $b['description'] }}">
  <meta name="theme-color" content="#000000">
  <link rel="icon" type="image/png" href="{{ $cms->imageUrl('favicon', 'img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ asset('fonts/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/anton-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="modulepreload" href="{{ asset('vendor/three/three.module.min.js') }}">
  <link rel="stylesheet" href="{{ $v('css/beta.css') }}">
  <script>document.documentElement.className = 'js';</script>
  <script type="importmap">{"imports": {"three": "{{ asset('vendor/three/three.module.min.js') }}"}}</script>
</head>
<body>
  <a class="skip" href="#main">{{ $cms->plain('skip_link', 'Skip to content') }}</a>

  {{-- Gradients for the flat pack drawings (used in the order panel and when 3D is unavailable). --}}
  <svg class="svg-defs" width="0" height="0" aria-hidden="true" focusable="false">
    <defs>
      <linearGradient id="pouchBody" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#2a2624"/><stop offset="1" stop-color="#0d0b0a"/>
      </linearGradient>
      <linearGradient id="pouchSheen" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0" stop-color="#fff" stop-opacity=".14"/>
        <stop offset=".22" stop-color="#fff" stop-opacity="0"/>
        <stop offset=".62" stop-color="#fff" stop-opacity=".06"/>
        <stop offset="1" stop-color="#fff" stop-opacity="0"/>
      </linearGradient>
    </defs>
  </svg>

  <p class="betabar">{{ $b['beta_bar'] }} <a href="{{ route('front.index', ['locale' => $locale]) }}">{{ $b['beta_back'] }}</a></p>

  <header class="nav" data-nav>
    <div class="nav__in">
      <a class="nav__brand" href="#top">Big Ed's</a>
      <nav class="nav__links" aria-label="Main">
        <a href="#flavours">{{ $b['nav_flavours'] }}</a>
        <a href="#how">{{ $b['nav_how'] }}</a>
        <a href="#faq">{{ $b['nav_faq'] }}</a>
      </nav>
      @if ($langs->count() > 1)
        <nav class="nav__langs" aria-label="{{ $cms->plain('nav_languages', 'Language') }}">
          @foreach ($langs as $lang)
            <a href="{{ route('front.beta', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" title="{{ $lang->name }}" @if ($lang->code === $locale) aria-current="true" @endif>{{ strtoupper($lang->code) }}</a>
          @endforeach
        </nav>
      @endif
      <a class="nav__buy" href="#choose">{{ $b['nav_choose'] }}</a>
      <button class="nav__order" type="button" data-open-order aria-label="{{ $b['nav_order'] }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
        <span class="count" data-count hidden>0</span>
      </button>
    </div>
  </header>

  <main id="main">
    {{-- STORY: the stage sticks while the section scrolls by. --}}
    <section class="story" id="top" data-story aria-label="{{ $b['nav_flavours'] }}">
      <div class="story__stage">
        <canvas class="story__canvas" data-canvas aria-hidden="true"></canvas>
        <div class="story__fallback" data-fallback aria-hidden="true"></div>

        <div class="cap cap--hero" data-cap-hero>
          <p class="cap__kicker">{{ $b['hero_kicker'] }}</p>
          <h1 class="cap__title">{{ $b['hero_title'] }}</h1>
        </div>
        <p class="cap cap--lead" data-cap-hero>{{ $b['hero_lead'] }}</p>
        <p class="story__cue" data-cue aria-hidden="true">{{ $b['hero_scroll'] }}<span></span></p>

        <div class="caps" data-caps aria-live="polite"></div>
        <nav class="story__index" data-index aria-label="{{ $b['nav_flavours'] }}"></nav>
      </div>
      <span id="flavours" class="story__mark" aria-hidden="true"></span>
    </section>

    <section class="choose" id="choose" aria-labelledby="choose-title">
      <div class="wrap">
        <header class="head" data-reveal>
          <h2 id="choose-title">{{ $b['choose_title'] }}</h2>
          <p>{{ str_replace('{g}', $packGrams, $b['choose_lead']) }}</p>
        </header>
        <div class="grid" data-grid></div>
        <div data-box></div>
      </div>
    </section>

    <section class="how" id="how" aria-labelledby="how-title">
      <div class="wrap">
        <h2 id="how-title" data-reveal>{{ $b['how_title'] }}</h2>
        <ol class="how__list">
          @foreach ([1, 2, 3] as $i)
            <li data-reveal>
              <span class="how__n" aria-hidden="true">{{ $i }}</span>
              <h3>{{ $b["how_{$i}_t"] }}</h3>
              <p>{{ $b["how_{$i}_d"] }}</p>
            </li>
          @endforeach
        </ol>
      </div>
    </section>

    <section class="made" aria-labelledby="made-title">
      <div class="wrap made__grid">
        <h2 id="made-title" data-reveal>{{ $b['made_title'] }}</h2>
        <div data-reveal>
          <p class="made__text">{{ $b['made_text'] }}</p>
          <p class="made__ing"><span>{{ $b['ingredients_title'] }}</span> {{ $ingredients->implode(', ') }}. {{ $b['contains_soy'] }}</p>
        </div>
      </div>
    </section>

    <section class="faq" id="faq" aria-labelledby="faq-title">
      <div class="wrap faq__grid">
        <h2 id="faq-title" data-reveal>{{ $b['faq_title'] }}</h2>
        <div class="faq__list" data-reveal>
          @foreach ([1, 2, 3, 4, 5] as $i)
            <details>
              <summary>{{ $b["faq_{$i}_q"] }}</summary>
              <p>{{ $b["faq_{$i}_a"] }}</p>
            </details>
          @endforeach
        </div>
      </div>
    </section>
  </main>

  <footer class="foot">
    <div class="wrap">
      <div class="foot__top">
        <p class="foot__brand">Big Ed's Jerky</p>
        <div class="foot__contact">
          @if ($contact->isEmpty())
            <p>{{ $b['footer_ask_empty'] }}</p>
          @else
            <p>{{ $b['footer_ask'] }}</p>
            <ul>
              @isset($contact['phone'])
                <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></li>
              @endisset
              @isset($contact['telegram'])
                <li><a href="https://t.me/{{ $contact['telegram'] }}" rel="noopener">Telegram</a></li>
              @endisset
              @isset($contact['instagram'])
                <li><a href="https://instagram.com/{{ $contact['instagram'] }}" rel="noopener">Instagram</a></li>
              @endisset
              @isset($contact['email'])
                <li><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
              @endisset
            </ul>
          @endif
        </div>
      </div>
      <p class="foot__legal">© {{ date('Y') }} Big Ed's Jerky. {{ $b['footer_legal'] }}</p>
    </div>
  </footer>

  {{-- Shows once something is in the order. --}}
  <div class="bag" data-bag hidden>
    <p class="bag__sum"><span data-bag-packs></span><strong data-bag-total></strong></p>
    <button class="btn btn--light" type="button" data-open-order>{{ $b['bar_review'] }}</button>
  </div>

  <dialog class="panel" data-sheet aria-labelledby="sheet-title">
    <div class="panel__in">
      <header class="panel__head">
        <h2 id="sheet-title">{{ $b['sheet_title'] }}</h2>
        <button class="panel__close" type="button" data-close aria-label="{{ $b['sheet_close'] }}">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </header>
      <div data-sheet-body></div>
    </div>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script type="application/json" id="menu-data">@json($menu)</script>
  <script type="application/json" id="beta-texts">@json($b)</script>
  <script type="module" src="{{ $v('js/beta.js') }}"></script>
</body>
</html>
