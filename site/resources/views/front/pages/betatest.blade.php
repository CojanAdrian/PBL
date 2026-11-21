{{--
    The redesigned Big Ed's page, tested at /{locale}/betatest before it replaces
    home.blade.php. Same products, prices and order endpoint as the main page;
    texts in App\Shop\BetaTexts (order-form errors still come from Text blocks).
    The flavour chapters, the taster crate and the order receipt are drawn by
    public/js/beta.js from the menu data at the bottom of this page.
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
    $photo = $cms->imageUrl('story_photo', 'img/jerky-board.webp');
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
  <meta name="theme-color" content="#120f0d">
  <link rel="icon" type="image/png" href="{{ $cms->imageUrl('favicon', 'img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ asset('fonts/anton-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ $photo }}" as="image">
  <link rel="stylesheet" href="{{ $v('css/beta.css') }}">
  <script>document.documentElement.className = 'js';</script>
</head>
<body>
  <a class="skip" href="#main">{{ $cms->plain('skip_link', 'Skip to content') }}</a>

  {{-- Gradients used by the pack drawings (pouch.js adds one per meat). --}}
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
      <a class="nav__brand" href="#top" aria-label="Big Ed's Jerky">
        <img src="{{ $cms->imageUrl('site_logo', 'img/logo.webp') }}" width="42" height="42" alt="">
        <span>Big Ed's</span>
      </a>
      <nav class="nav__links" aria-label="Main">
        <a href="#flavours">{{ $b['nav_flavours'] }}</a>
        <a href="#how">{{ $b['nav_how'] }}</a>
        <a href="#about">{{ $b['nav_about'] }}</a>
        <a href="#faq">{{ $b['nav_faq'] }}</a>
      </nav>
      @if ($langs->count() > 1)
        <nav class="nav__langs" aria-label="{{ $cms->plain('nav_languages', 'Language') }}">
          @foreach ($langs as $lang)
            <a href="{{ route('front.beta', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" title="{{ $lang->name }}" @if ($lang->code === $locale) aria-current="true" @endif>{{ strtoupper($lang->code) }}</a>
          @endforeach
        </nav>
      @endif
      <button class="nav__order" type="button" data-open-order>
        <span>{{ $b['nav_order'] }}</span><span class="count" data-count hidden>0</span>
      </button>
    </div>
  </header>

  <main id="main">
    {{-- HERO: smoke (WebGL) drifting over the photo; the pointer clears it. --}}
    <section class="hero" id="top" aria-labelledby="hero-title">
      <div class="hero__media" aria-hidden="true">
        <img class="hero__img" src="{{ $photo }}" width="900" height="1348" alt="" fetchpriority="high">
        <canvas class="hero__smoke" data-smoke data-src="{{ $photo }}"></canvas>
        <div class="hero__shade"></div>
      </div>
      <div class="hero__content">
        <p class="hero__kicker" data-rise>{{ $b['hero_kicker'] }}</p>
        <h1 class="hero__title" id="hero-title">
          <span class="hero__line" data-split>{{ $b['hero_title_1'] }}</span>
          <span class="hero__line hero__line--2" data-split>{{ $b['hero_title_2'] }}</span>
        </h1>
        <div class="hero__foot">
          <p class="hero__lead" data-rise>{{ $b['hero_lead'] }}</p>
          <div class="hero__act" data-rise>
            <a class="btn btn--red btn--lg" href="#flavours">
              <span>{{ $b['hero_cta'] }}</span>
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
            </a>
            <p class="hero__price">{{ str_replace('{price}', $price($cheapest), $b['hero_from']) }}<span>{{ $b['hero_pay'] }}</span></p>
          </div>
        </div>
      </div>
      <p class="hero__hint" data-hint aria-hidden="true" data-touch="{{ $b['hero_hint_touch'] }}">{{ $b['hero_hint'] }}</p>
    </section>

    {{-- FLAVOURS: one full-screen chapter per flavour, drawn by beta.js. --}}
    <section class="flavours" id="flavours" aria-labelledby="flav-title">
      <header class="flavours__head">
        <h2 id="flav-title"><span data-reveal-line>{{ $b['flav_title_1'] }}</span> <span data-reveal-line>{{ $b['flav_title_2'] }}</span></h2>
        <p data-rise>{{ str_replace('{g}', $packGrams, $b['flav_lead']) }}</p>
      </header>
      <div class="chapters" data-chapters></div>
      <noscript><p class="noscript">{{ $b['menu_noscript'] }}</p></noscript>
    </section>

    {{-- TASTER BOX: the crate opens as you scroll. --}}
    <section class="crate" id="taster" data-crate aria-labelledby="crate-title"></section>

    <section class="how" id="how" aria-labelledby="how-title">
      <div class="wrap">
        <h2 id="how-title" data-reveal-line>{{ $b['how_title'] }}</h2>
        <ol class="how__list">
          @foreach ([1, 2, 3] as $i)
            <li data-rise>
              <span class="how__n" aria-hidden="true">0{{ $i }}</span>
              <h3>{{ $b["how_{$i}_t"] }}</h3>
              <p>{{ $b["how_{$i}_d"] }}</p>
            </li>
          @endforeach
        </ol>
        <p class="stamp" data-stamp>{{ $b['how_stamp'] }}</p>
      </div>
    </section>

    <section class="about" id="about" aria-labelledby="about-title">
      <div class="wrap about__grid">
        <div class="about__main">
          <p class="kicker">{{ $b['about_kicker'] }}</p>
          <h2 id="about-title"><span data-reveal-line>{{ $b['about_title'] }}</span></h2>
          <p class="about__big" data-rise>{{ $b['about_1'] }}</p>
          <p class="about__small" data-rise>{{ $b['about_2'] }}</p>
        </div>
        <aside class="label" data-rise aria-labelledby="ing-title">
          <p class="label__brand">Big Ed's Jerky</p>
          <h3 id="ing-title">{{ $b['ingredients_title'] }}</h3>
          <p class="label__list">{{ $ingredients->implode(', ') }}.</p>
          <p class="label__warn">{{ $b['contains_soy'] }}</p>
          <p class="label__small">{{ $b['pack_store'] }}</p>
          <svg class="label__bars" viewBox="0 0 120 30" aria-hidden="true" data-barcode="BIGEDS"></svg>
        </aside>
      </div>
    </section>

    <section class="faq" id="faq" aria-labelledby="faq-title">
      <div class="wrap faq__grid">
        <h2 id="faq-title" data-reveal-line>{{ $b['faq_title'] }}</h2>
        <div class="faq__list">
          @foreach ([1, 2, 3, 4, 5] as $i)
            <details data-rise>
              <summary>{{ $b["faq_{$i}_q"] }}</summary>
              <p>{{ $b["faq_{$i}_a"] }}</p>
            </details>
          @endforeach
        </div>
      </div>
    </section>
  </main>

  <footer class="foot">
    <div class="wrap foot__grid">
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
              <li><a href="https://t.me/{{ $contact['telegram'] }}" rel="noopener">Telegram {{ '@'.$contact['telegram'] }}</a></li>
            @endisset
            @isset($contact['instagram'])
              <li><a href="https://instagram.com/{{ $contact['instagram'] }}" rel="noopener">Instagram {{ '@'.$contact['instagram'] }}</a></li>
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
        <a href="#about">{{ $b['nav_about'] }}</a>
        <a href="#faq">{{ $b['nav_faq'] }}</a>
        <a href="#top">{{ $b['footer_top'] }} ↑</a>
      </nav>
    </div>
    <p class="foot__word" aria-hidden="true" data-split-foot>Big Ed's</p>
    <p class="wrap foot__legal">© {{ date('Y') }} Big Ed's Jerky. {{ $b['footer_legal'] }}</p>
  </footer>

  {{-- The order ticket: shows once something is in the order. --}}
  <button class="ticket" type="button" data-ticket data-open-order hidden>
    <span class="ticket__sum"><small data-ticket-packs></small><strong data-ticket-total></strong></span>
    <span class="ticket__go">{{ $b['bar_review'] }} <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
  </button>

  <dialog class="sheet" data-sheet aria-labelledby="sheet-title">
    <div class="receipt">
      <button class="receipt__close" type="button" data-close aria-label="{{ $b['sheet_close'] }}">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <header class="receipt__head">
        <img src="{{ $cms->imageUrl('site_logo', 'img/logo.webp') }}" width="56" height="56" alt="">
        <h2 id="sheet-title">{{ $b['sheet_title'] }}</h2>
        <p>Big Ed's Jerky · {{ $b['receipt_sub'] }}</p>
        <p data-receipt-date></p>
      </header>
      <div data-sheet-body></div>
    </div>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script type="application/json" id="menu-data">@json($menu)</script>
  <script type="application/json" id="beta-texts">@json($b + ['_ingredients' => $ingredients])</script>
  <script src="{{ asset('vendor/gsap.min.js') }}" defer></script>
  <script src="{{ asset('vendor/ScrollTrigger.min.js') }}" defer></script>
  <script src="{{ asset('vendor/lenis.min.js') }}" defer></script>
  <script type="module" src="{{ $v('js/beta.js') }}"></script>
</body>
</html>
