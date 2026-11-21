{{--
    The redesigned Big Ed's page, tested at /{locale}/betatest before it replaces
    home.blade.php. Same products, prices and order endpoint as the main page;
    texts in App\Shop\BetaTexts (order-form errors still come from Text blocks).
--}}
@php
    $cms = app(\App\Support\Cms\Cms::class);
    $langs = \App\Models\Lang::where('active', true)->orderBy('id')->get(['code', 'name']);
    $locale = app()->getLocale();
    $ingredients = \App\Models\Ingredient::active()->with('text')->orderBy('order_column')->get();
    $contact = collect([
        'phone' => $cms->plain('footer_phone'),
        'email' => $cms->plain('footer_email'),
        'instagram' => ltrim($cms->plain('footer_instagram'), '@'),
        'telegram' => ltrim($cms->plain('footer_telegram'), '@'),
    ])->filter();
    $cheapest = collect($menu['flavours'])->min('price');
    $packGrams = collect($menu['flavours'])->pluck('grams')->unique()->count() === 1 ? $menu['flavours'][0]['grams'] : 100;
    $price = fn ($n) => number_format($n, 0, '.', ' ').' '.$menu['currency'];
@endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>{{ $b['title'] }}</title>
  <meta name="description" content="{{ $b['description'] }}">
  <meta name="theme-color" content="#f2eadc">
  <link rel="icon" type="image/png" href="{{ $cms->imageUrl('favicon', 'img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ asset('fonts/anton-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="{{ asset('css/beta.css') }}?v={{ filemtime(public_path('css/beta.css')) }}">
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
        <stop offset="0" stop-color="#fff" stop-opacity=".12"/>
        <stop offset=".22" stop-color="#fff" stop-opacity="0"/>
        <stop offset=".62" stop-color="#fff" stop-opacity=".05"/>
        <stop offset="1" stop-color="#fff" stop-opacity="0"/>
      </linearGradient>
    </defs>
  </svg>

  <p class="betabar">{{ $b['beta_bar'] }} <a href="{{ route('front.index', ['locale' => $locale]) }}">{{ $b['beta_back'] }}</a></p>

  <header class="top">
    <div class="wrap top__in">
      <a class="brand" href="#top">
        <img src="{{ $cms->imageUrl('site_logo', 'img/logo.webp') }}" width="40" height="40" alt="">
        <span>Big Ed's Jerky</span>
      </a>
      <nav class="top__nav" aria-label="Main">
        <a href="#menu">{{ $b['nav_menu'] }}</a>
        <a href="#how">{{ $b['nav_how'] }}</a>
        <a href="#about">{{ $b['nav_about'] }}</a>
        <a href="#faq">{{ $b['nav_faq'] }}</a>
      </nav>
      @if ($langs->count() > 1)
        <nav class="langs" aria-label="{{ $cms->plain('nav_languages', 'Language') }}">
          @foreach ($langs as $lang)
            <a href="{{ route('front.beta', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" title="{{ $lang->name }}" @if ($lang->code === $locale) aria-current="true" @endif>{{ strtoupper($lang->code) }}</a>
          @endforeach
        </nav>
      @endif
      <button class="top__order" type="button" data-open-order>
        {{ $b['nav_order'] }} <span class="count" data-count hidden>0</span>
      </button>
    </div>
  </header>

  <main id="main">
    <section class="hero" id="top" aria-labelledby="hero-title">
      <div class="wrap hero__grid">
        <div class="hero__copy">
          <p class="kicker">{{ $b['hero_kicker'] }}</p>
          <h1 id="hero-title">{{ $b['hero_title'] }}</h1>
          <p class="hero__lead">{{ $b['hero_lead'] }}</p>
          <div class="hero__act">
            <a class="btn btn--red" href="#menu">{{ $b['hero_cta'] }}</a>
            <p class="hero__price">{{ str_replace('{price}', $price($cheapest), $b['hero_from']) }}<br><span>{{ $b['hero_pay'] }}</span></p>
          </div>
        </div>
        <figure class="hero__photo">
          <img src="{{ $cms->imageUrl('story_photo', 'img/jerky-board.webp') }}" width="900" height="1348" alt="{{ $b['hero_photo_alt'] }}" fetchpriority="high">
        </figure>
      </div>
    </section>

    <section class="menu" id="menu" aria-labelledby="menu-title">
      <div class="wrap">
        <header class="head">
          <h2 id="menu-title">{{ $b['menu_title'] }}</h2>
          <p>{{ str_replace('{g}', $packGrams, $b['menu_lead']) }}</p>
        </header>
        <div class="cards" data-cards></div>
        <div data-boxes></div>
        <noscript><p class="note">{{ $b['menu_noscript'] }}</p></noscript>
      </div>
    </section>

    <section class="how" id="how" aria-labelledby="how-title">
      <div class="wrap">
        <h2 id="how-title">{{ $b['how_title'] }}</h2>
        <ol class="how__list">
          @foreach ([1, 2, 3] as $i)
            <li>
              <span class="how__n" aria-hidden="true">{{ $i }}</span>
              <h3>{{ $b["how_{$i}_t"] }}</h3>
              <p>{{ $b["how_{$i}_d"] }}</p>
            </li>
          @endforeach
        </ol>
      </div>
    </section>

    <section class="about" id="about" aria-labelledby="about-title">
      <div class="wrap about__grid">
        <h2 id="about-title">{{ $b['about_title'] }}</h2>
        <div class="about__text">
          <p class="about__big">{{ $b['about_1'] }}</p>
          <p>{{ $b['about_2'] }}</p>
        </div>
        <div class="about__ing">
          <h3>{{ $b['ingredients_title'] }}</h3>
          <ul>
            @foreach ($ingredients as $ingredient)
              <li>{{ $ingredient->translation()?->name }}</li>
            @endforeach
          </ul>
          <p class="note">{{ $b['contains_soy'] }}</p>
        </div>
      </div>
    </section>

    <section class="faq" id="faq" aria-labelledby="faq-title">
      <div class="wrap faq__grid">
        <h2 id="faq-title">{{ $b['faq_title'] }}</h2>
        <div class="faq__list">
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
    <div class="wrap foot__grid">
      <div class="foot__brand">
        <img src="{{ $cms->imageUrl('site_logo', 'img/logo.webp') }}" width="64" height="64" alt="" loading="lazy">
        <p>Big Ed's Jerky</p>
      </div>
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
      <p class="foot__legal">© {{ date('Y') }} Big Ed's Jerky. {{ $b['footer_legal'] }}</p>
    </div>
  </footer>

  {{-- Appears once something is in the order. --}}
  <div class="bar" data-bar hidden>
    <p class="bar__sum"><span data-bar-packs></span><strong data-bar-total></strong></p>
    <button class="btn btn--red" type="button" data-open-order>{{ $b['bar_review'] }}</button>
  </div>

  <dialog class="sheet" data-sheet aria-labelledby="sheet-title">
    <div class="sheet__in">
      <header class="sheet__head">
        <h2 id="sheet-title">{{ $b['sheet_title'] }}</h2>
        <button class="sheet__close" type="button" data-close aria-label="{{ $b['sheet_close'] }}">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </header>
      <div data-sheet-body></div>
    </div>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script type="application/json" id="menu-data">@json($menu)</script>
  <script type="application/json" id="beta-texts">@json($b)</script>
  <script type="module" src="{{ asset('js/beta.js') }}?v={{ filemtime(public_path('js/beta.js')) }}"></script>
</body>
</html>
