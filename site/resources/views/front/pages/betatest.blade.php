{{--
    The redesigned Big Ed's page, tested at /{locale}/betatest before it replaces
    home.blade.php. Same products, prices and order endpoint as the main page;
    texts in App\Shop\BetaTexts (order-form errors still come from Text blocks).

    Sections: loader · hero (live 3D pack) · tapes · how to order · one full-screen
    chapter per flavour (packshots from public/img/packs) · 3D inspector · Ed · FAQ ·
    footer. The flavour chapters, the inspector and the order drawer are filled in
    by public/js/beta.js from the menu data at the bottom of the page.
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
    $cheapest = collect($menu['flavours'])->min('price');
    $packGrams = collect($menu['flavours'])->pluck('grams')->unique()->count() === 1 ? $menu['flavours'][0]['grams'] : 100;
    $price = fn ($n) => number_format($n, 0, '.', ' ').' '.$menu['currency'];
    $v = fn ($file) => asset($file).'?v='.filemtime(public_path($file));
    $tape = fn () => collect([$b['tape_1'], $b['tape_2'], $b['tape_3'], $b['tape_4']])->map(fn ($t) => '<span>'.e($t).'</span><i>✶</i>')->implode('');
    $icons = [
        1 => '<path d="M4 20 15 9l3 3L7 23H4z"/><path d="M15 9l3-6 3 3-6 6"/>',
        2 => '<path d="M12 3c3 4.5 6 7.6 6 11a6 6 0 0 1-12 0c0-3.4 3-6.5 6-11z"/><path d="M9.5 15a2.5 2.5 0 0 0 2.5 2.5"/>',
        3 => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/>',
        4 => '<path d="M2 7h11v9H2zM13 10h4l3 3v3h-7"/><circle cx="6" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
    ];
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
  <meta name="theme-color" content="#0b0605">
  <link rel="icon" type="image/png" href="{{ asset('img/brand/skull.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ asset('img/brand/logo.webp') }}" as="image">
  <link rel="preload" href="{{ asset('fonts/anton-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="modulepreload" href="{{ asset('vendor/three/three.module.min.js') }}">
  <link rel="stylesheet" href="{{ $v('css/beta.css') }}">
  <script>document.documentElement.className = 'js';</script>
  <script type="importmap">{"imports": {"three": "{{ asset('vendor/three/three.module.min.js') }}"}}</script>
</head>
<body>
  <a class="skip" href="#main">{{ $cms->plain('skip_link', 'Skip to content') }}</a>

  {{-- Gradients for the flat pack drawings (fallback when an image or WebGL is missing). --}}
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

  {{-- Loader: a counter, then the curtain lifts. --}}
  <div class="loader" data-loader aria-hidden="true">
    <img class="loader__mark" src="{{ asset('img/brand/skull.webp') }}" width="466" height="315" alt="">
    <span class="loader__fill" aria-hidden="true"><img src="{{ asset('img/brand/skull.webp') }}" width="466" height="315" alt="" data-loader-fill></span>
    <p class="loader__row"><span>{{ $b['loading'] }}</span><span class="loader__n" data-loader-n>000%</span></p>
    <span class="loader__bar"><i data-loader-bar></i></span>
  </div>

  <div class="grain" aria-hidden="true"></div>

  <p class="betabar">{{ $b['beta_bar'] }} <a href="{{ route('front.index', ['locale' => $locale]) }}">{{ $b['beta_back'] }}</a></p>

  <header class="nav" data-nav>
    <nav class="nav__links" aria-label="Main">
      <a href="#flavours">{{ $b['nav_flavours'] }}</a>
      <a href="#how">{{ $b['nav_how'] }}</a>
      <a href="#ed">{{ $b['nav_story'] }}</a>
      <a href="#faq">{{ $b['nav_faq'] }}</a>
    </nav>
    <a class="nav__brand" href="#top" aria-label="Big Ed's Jerky"><img src="{{ asset('img/brand/wordmark.webp') }}" width="1060" height="353" alt=""></a>
    <div class="nav__end">
      @if ($langs->count() > 1)
        <nav class="nav__langs" aria-label="{{ $cms->plain('nav_languages', 'Language') }}">
          @foreach ($langs as $lang)
            <a href="{{ route('front.beta', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" title="{{ $lang->name }}" @if ($lang->code === $locale) aria-current="true" @endif>{{ strtoupper($lang->code) }}</a>
          @endforeach
        </nav>
      @endif
      <button class="nav__order" type="button" data-open-order>
        <span>{{ $b['pull_order'] }}</span><b class="count" data-count>0</b>
      </button>
    </div>
  </header>

  {{-- The pull tab (desktop): hangs from the top; pull or click it to open the order. --}}
  <button class="pull" type="button" data-pull data-open-order aria-label="{{ $b['pull_order'] }}">
    <span class="pull__holes" aria-hidden="true"></span>
    <span class="pull__label" data-pull-label>{{ $b['pull'] }}</span>
    <span class="pull__sum" data-pull-sum hidden></span>
  </button>

  <main id="main">
    {{-- HERO --}}
    <section class="hero" id="top" aria-labelledby="hero-title">
      <div class="hero__field" aria-hidden="true"><i></i><i></i><i></i></div>
      <div class="hero__grid">
        <div class="hero__copy">
          <h1 class="hero__logo" id="hero-title">
            <img class="hero__skull" src="{{ asset('img/brand/skull.webp') }}" width="466" height="315" alt="" data-hero-skull>
            <img class="hero__wordmark" src="{{ asset('img/brand/wordmark.webp') }}" width="1060" height="353" alt="Big Ed's Jerky" data-hero-wordmark>
          </h1>
          <div data-in>
            <p class="hero__kicker">{{ $b['hero_kicker'] }}</p>
            <p class="hero__line">{{ $b['hero_line'] }}</p>
            <div class="hero__act">
              <a class="btn btn--ember" href="#flavours">{{ $b['hero_cta'] }}<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg></a>
              <span class="hero__from">{{ str_replace('{price}', $price($cheapest), $b['hero_from']) }}</span>
            </div>
          </div>
        </div>
        <div class="hero__stage">
          <canvas class="hero__pack" data-hero-canvas aria-hidden="true"></canvas>
          <img class="hero__fallback" data-hero-fallback alt="" hidden>
        </div>
      </div>
      <p class="clock hero__clock" data-in><span data-clock>--:--</span> · {{ $b['clock_place'] }}<br><i></i> {{ $b['clock_open'] }}</p>
    </section>

    {{-- Two tapes crossing --}}
    <div class="tapes" aria-hidden="true">
      <div class="tape tape--a"><div class="tape__track" data-tape>{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}</div></div>
      <div class="tape tape--b"><div class="tape__track" data-tape data-dir="-1">{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}</div></div>
    </div>

    {{-- HOW TO ORDER: three numbered chapters --}}
    <section class="how" id="how" aria-labelledby="how-title">
      <p class="kicker" id="how-title">{{ $b['how_kicker'] }}</p>
      <ol class="how__list">
        @foreach ([1, 2, 3] as $i)
          <li class="how__row" data-how>
            <span class="how__n" aria-hidden="true">0{{ $i }}</span>
            <h2 class="how__word">{{ $b["how_{$i}"] }}</h2>
            <p class="how__d">{{ $b["how_{$i}_d"] }}</p>
          </li>
        @endforeach
      </ol>
    </section>

    {{-- FLAVOURS: one full-screen chapter each, drawn by beta.js --}}
    <section class="flavours" id="flavours" aria-labelledby="flav-title">
      <header class="flavours__head">
        <p class="kicker">{{ $b['flav_kicker'] }}</p>
        <h2 id="flav-title" class="big" data-split>{{ $b['flav_title'] }}</h2>
        <p class="flavours__lead">{{ str_replace('{g}', $packGrams, $b['flav_lead']) }}</p>
      </header>
      <div data-chapters></div>
    </section>

    {{-- INSPECTOR --}}
    <section class="inspect" aria-labelledby="inspect-title">
      <div class="inspect__copy">
        <p class="kicker">{{ $b['inspect_kicker'] }}</p>
        <h2 id="inspect-title" class="big" data-split>{{ $b['inspect_title'] }}</h2>
        <p class="inspect__lead">{{ $b['inspect_lead'] }}</p>
        <div class="inspect__products" data-inspect-products role="group" aria-label="{{ $b['nav_flavours'] }}"></div>
        <div class="inspect__faces" role="group" aria-label="{{ $b['inspect_kicker'] }}">
          <button type="button" data-face="0" aria-pressed="true"><span>01</span>{{ $b['face_front'] }}</button>
          <button type="button" data-face="1" aria-pressed="false"><span>02</span>{{ $b['face_side'] }}</button>
          <button type="button" data-face="2" aria-pressed="false"><span>03</span>{{ $b['face_back'] }}</button>
          <button type="button" class="inspect__reset" data-reset>{{ $b['reset'] }}</button>
        </div>
        <p class="inspect__ing"><strong>{{ $b['ingredients_title'] }}.</strong> {{ $ingredients->implode(', ') }}. {{ $b['contains_soy'] }}</p>
      </div>
      <div class="inspect__stage">
        <canvas class="inspect__canvas" data-inspect-canvas aria-hidden="true"></canvas>
        <img class="inspect__fallback" data-inspect-fallback alt="" hidden>
      </div>
    </section>

    {{-- ED --}}
    <section class="ed" id="ed" aria-labelledby="ed-title">
      <div class="ed__head">
        <p class="kicker">{{ $b['story_kicker'] }}</p>
        <h2 id="ed-title" class="big"><span data-split>{{ $b['story_title_1'] }}</span></h2>
        <p class="stamp" data-stamp>{{ $b['story_title_2'] }}</p>
        <p class="ed__text">{{ $b['story_text'] }}</p>
      </div>
      <ul class="facts">
        @foreach ([1, 2, 3, 4] as $i)
          <li class="fact" data-in>
            <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$i] !!}</svg>
            <h3>{{ $b["fact_{$i}_t"] }}</h3>
            <p>{{ $b["fact_{$i}_d"] }}</p>
          </li>
        @endforeach
      </ul>
    </section>

    <section class="faq" id="faq" aria-labelledby="faq-title">
      <h2 id="faq-title" class="big" data-split>{{ $b['faq_title'] }}</h2>
      <div class="faq__list">
        @foreach ([1, 2, 3, 4, 5] as $i)
          <details data-in>
            <summary><span class="faq__n">0{{ $i }}</span>{{ $b["faq_{$i}_q"] }}</summary>
            <p>{{ $b["faq_{$i}_a"] }}</p>
          </details>
        @endforeach
      </div>
    </section>
  </main>

  <footer class="foot">
    <div class="foot__field" aria-hidden="true"><i></i><i></i></div>
    <div class="foot__top">
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
      <nav class="foot__nav" aria-label="Footer">
        <a href="#flavours">{{ $b['nav_flavours'] }}</a>
        <a href="#how">{{ $b['nav_how'] }}</a>
        <a href="#ed">{{ $b['nav_story'] }}</a>
        <a href="#faq">{{ $b['nav_faq'] }}</a>
        <a href="#top">{{ $b['footer_top'] }} ↑</a>
      </nav>
    </div>
    <div class="foot__mark" aria-hidden="true">
      <img class="foot__skull" src="{{ asset('img/brand/skull.webp') }}" width="466" height="315" alt="" loading="lazy">
      <img class="foot__word" src="{{ asset('img/brand/wordmark.webp') }}" width="1060" height="353" alt="" loading="lazy">
    </div>
    <div class="foot__legal">
      <p>© {{ date('Y') }} Big Ed's Jerky. {{ $b['footer_legal'] }}</p>
      <p class="clock clock--small"><span data-clock>--:--</span> · {{ $b['clock_place'] }}</p>
    </div>
  </footer>

  {{-- Phones: a bar at the bottom once something is in the order. --}}
  <button class="bagbar" type="button" data-bagbar data-open-order hidden>
    <span class="bagbar__count" data-bagbar-count>0</span>
    <span class="bagbar__sum" data-bagbar-sum></span>
    <span class="bagbar__go">{{ $b['pull_order'] }} →</span>
  </button>

  <dialog class="drawer" data-sheet aria-labelledby="sheet-title">
    <div class="drawer__in">
      <header class="drawer__head">
        <h2 id="sheet-title">{{ $b['sheet_title'] }}</h2>
        <button class="drawer__close" type="button" data-close aria-label="{{ $b['sheet_close'] }}">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </header>
      <div class="bag" data-bag aria-hidden="true"></div>
      <div data-sheet-body></div>
    </div>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script type="application/json" id="menu-data">@json($menu)</script>
  <script type="application/json" id="beta-texts">@json($b + ['_ingredients' => $ingredients, '_packs' => asset('img/packs'), '_logo' => asset('img/brand/logo.png')])</script>
  <script src="{{ asset('vendor/gsap.min.js') }}" defer></script>
  <script src="{{ asset('vendor/ScrollTrigger.min.js') }}" defer></script>
  <script src="{{ asset('vendor/lenis.min.js') }}" defer></script>
  <script type="module" src="{{ $v('js/beta.js') }}"></script>
</body>
</html>
