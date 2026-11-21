{{--
    The redesigned Big Ed's page, tested at /{locale}/betatest before it replaces
    home.blade.php. Same products, prices and order endpoint as the main page;
    texts in App\Shop\BetaTexts (order-form errors still come from Text blocks).

    Order of the page: loader · hero (logo + live 3D pack) · tapes · how to order ·
    THE LINEUP (where people order: meat switch, mood chips, one card per flavour) ·
    "click to order" band · flavour chapters (up close) · 3D inspector · Ed · band ·
    FAQ · footer. The lineup, chapters, inspector and cart are drawn by public/js/beta.js.
    Packshots: public/img/packs (rendered from the 3D pack); logo: public/img/brand.
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
    $brand = fn ($f) => asset("img/brand/{$f}");
    $tape = fn () => collect([$b['tape_1'], $b['tape_2'], $b['tape_3'], $b['tape_4']])->map(fn ($t) => '<span>'.e($t).'</span><i>✶</i>')->implode('');
    $arrow = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="square" aria-hidden="true"><path d="M4 12h15M13 5l7 7-7 7"/></svg>';
    // A button whose label rolls up on hover (the copy below is for sighted users only).
    $roll = fn ($text) => '<span class="roll" data-text="'.e($text).'"><span>'.e($text).'</span></span>';
    $currentLang = $langs->firstWhere('code', $locale);
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
  <meta name="theme-color" content="#0b0706">
  <link rel="icon" type="image/png" href="{{ $brand('skull.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ $brand('logo.webp') }}" as="image">
  <link rel="preload" href="{{ asset('fonts/alfa-slab-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/oswald-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
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

  {{-- Loader: the skull fills with colour, then the curtain lifts. --}}
  <div class="loader" data-loader aria-hidden="true">
    <div class="loader__skull">
      <img class="loader__dim" src="{{ $brand('skull.webp') }}" width="466" height="315" alt="">
      <span class="loader__fill"><img src="{{ $brand('skull.webp') }}" width="466" height="315" alt=""></span>
    </div>
    <p class="loader__row"><span>{{ $b['loading'] }}</span><span class="loader__n" data-loader-n>0</span></p>
  </div>

  {{-- Film: grain (drawn by beta.js), and a soft vignette. --}}
  <div class="film" data-film aria-hidden="true"></div>
  <div class="vignette" aria-hidden="true"></div>

  <p class="betabar">{{ $b['beta_bar'] }} <a href="{{ route('front.index', ['locale' => $locale]) }}">{{ $b['beta_back'] }}</a></p>

  <header class="nav" data-nav>
    <nav class="nav__links" aria-label="Main">
      <a href="#shop">{{ $b['nav_shop'] }}</a>
      <a href="#flavours">{{ $b['nav_flavours'] }}</a>
      <a href="#ed">{{ $b['nav_story'] }}</a>
      <a href="#faq">{{ $b['nav_faq'] }}</a>
    </nav>
    <a class="nav__brand" href="#top" aria-label="Big Ed's Jerky"><img src="{{ $brand('wordmark.webp') }}" width="1060" height="353" alt=""></a>
    <div class="nav__end">
      @if ($langs->count() > 1)
        <details class="lang" data-lang>
          <summary aria-label="{{ $b['lang_label'] }}: {{ $currentLang?->name }}">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.8 2.6 15.2 0 18M12 3c-2.6 2.8-2.6 15.2 0 18"/></svg>
            <span>{{ strtoupper($locale) }}</span>
          </summary>
          <div class="lang__menu">
            @foreach ($langs as $lang)
              <a href="{{ route('front.beta', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" @if ($lang->code === $locale) aria-current="true" @endif><b>{{ strtoupper($lang->code) }}</b>{{ $lang->name }}</a>
            @endforeach
          </div>
        </details>
      @endif
      {{-- The cart: a meat tag hanging from the bar. Click (or pull it down) to open. --}}
      <button class="cart" type="button" data-cart data-open-order aria-label="{{ $b['cart_open'] }}">
        <span class="cart__string" aria-hidden="true"></span>
        <span class="cart__tag" data-cart-tag>
          <span class="cart__hole" aria-hidden="true"></span>
          <svg class="cart__icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M4 8h16l-1.5 12h-13z"/><path d="M8.5 8V6a3.5 3.5 0 0 1 7 0v2"/></svg>
          <span class="cart__label">{{ $b['cart'] }}</span>
          <span class="cart__count" data-cart-count>0</span>
          <span class="cart__sum" data-cart-sum>{{ $b['cart_empty'] }}</span>
        </span>
      </button>
      <button class="nav__menu" type="button" data-menu-open aria-expanded="false" aria-controls="menu">{{ $b['nav_menu'] }}</button>
    </div>
  </header>

  {{-- Phones: full-screen menu with big links and big language buttons. --}}
  <div class="menu" id="menu" data-menu hidden>
    <button class="menu__close" type="button" data-menu-close aria-label="{{ $b['sheet_close'] }}">
      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
    </button>
    <nav class="menu__links" aria-label="Mobile">
      <a href="#shop">{{ $b['nav_shop'] }}</a>
      <a href="#flavours">{{ $b['nav_flavours'] }}</a>
      <a href="#how">{{ $b['nav_how'] }}</a>
      <a href="#ed">{{ $b['nav_story'] }}</a>
      <a href="#faq">{{ $b['nav_faq'] }}</a>
    </nav>
    @if ($langs->count() > 1)
      <nav class="menu__langs" aria-label="{{ $b['lang_label'] }}">
        @foreach ($langs as $lang)
          <a href="{{ route('front.beta', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" @if ($lang->code === $locale) aria-current="true" @endif><b>{{ strtoupper($lang->code) }}</b><span>{{ $lang->name }}</span></a>
        @endforeach
      </nav>
    @endif
  </div>

  <main id="main">
    {{-- HERO --}}
    <section class="hero" id="top" aria-labelledby="hero-title">
      <div class="hero__field" aria-hidden="true"><i></i><i></i><i></i></div>
      <div class="hero__grid">
        <div class="hero__copy">
          <h1 class="hero__logo" id="hero-title">
            <img class="hero__skull" src="{{ $brand('skull.webp') }}" width="466" height="315" alt="" data-hero-skull>
            <img class="hero__wordmark" src="{{ $brand('wordmark.webp') }}" width="1060" height="353" alt="Big Ed's Jerky" data-hero-wordmark>
          </h1>
          <div class="hero__text" data-in>
            <p class="label">{{ $b['hero_kicker'] }}</p>
            <p class="hero__line">{{ $b['hero_line'] }}</p>
            <a class="btn btn--ember btn--xl" href="#shop">{!! $roll($b['hero_cta']) !!}<span class="btn__ico">{!! $arrow !!}</span></a>
            <p class="hero__note">{{ str_replace('{price}', $price($cheapest), $b['hero_note']) }}</p>
          </div>
        </div>
        <div class="hero__stage">
          <canvas class="hero__pack" data-hero-canvas aria-hidden="true"></canvas>
          <img class="hero__fallback" data-hero-fallback alt="" hidden>
        </div>
      </div>
    </section>

    {{-- Two tapes crossing --}}
    <div class="tapes" aria-hidden="true">
      <div class="tape tape--a"><div class="tape__track" data-tape>{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}</div></div>
      <div class="tape tape--b"><div class="tape__track" data-tape data-dir="-1">{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}{!! $tape() !!}</div></div>
    </div>

    {{-- HOW TO ORDER --}}
    <section class="how" id="how" aria-labelledby="how-title">
      <p class="label" id="how-title">{{ $b['how_kicker'] }}</p>
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

    {{-- THE LINEUP: where people order --}}
    <section class="shop" id="shop" aria-labelledby="shop-title">
      <header class="shop__head">
        <p class="label">{{ $b['shop_kicker'] }}</p>
        <h2 id="shop-title" class="display" data-split>{{ $b['shop_title'] }}</h2>
        <p class="shop__lead">{{ str_replace('{g}', $packGrams, $b['shop_lead']) }}</p>
      </header>
      <div class="shop__controls">
        <div class="meat-switch" role="radiogroup" aria-label="{{ $b['meat_label'] }}" data-meat-switch></div>
        <div class="moods" role="group" aria-label="{{ $b['mood_label'] }}">
          <span class="moods__label">{{ $b['mood_label'] }}</span>
          @foreach (['sweet', 'smoky', 'savoury', 'hot', 'any'] as $m)
            <button type="button" class="mood" data-mood="{{ $m }}" aria-pressed="false">{{ $b["mood_{$m}"] }}</button>
          @endforeach
        </div>
      </div>
      <div class="lineup" data-lineup></div>
      <div class="shop__checkout" data-shop-checkout hidden>
        <p data-shop-sum></p>
        <button class="btn btn--ember btn--lg" type="button" data-open-order>{!! $roll($b['go_checkout']) !!}<span class="btn__ico">{!! $arrow !!}</span></button>
      </div>
    </section>

    {{-- CLICK TO ORDER --}}
    <a class="band" href="#shop" data-band>
      <span class="band__track" aria-hidden="true">@for ($r = 0; $r < 4; $r++)<span>{{ $b['cta_1_big'] }}</span><i>→</i>@endfor</span>
      <span class="band__small">{{ $b['cta_1_small'] }}</span>
      <span class="sr-only">{{ $b['cta_1_big'] }}</span>
    </a>

    {{-- UP CLOSE: one full-screen chapter per flavour --}}
    <section class="flavours" id="flavours" aria-labelledby="flav-title">
      <header class="flavours__head">
        <p class="label">{{ $b['flav_kicker'] }}</p>
        <h2 id="flav-title" class="display" data-split>{{ $b['flav_title'] }}</h2>
      </header>
      <div data-chapters></div>
    </section>

    {{-- INSPECTOR --}}
    <section class="inspect" aria-labelledby="inspect-title">
      <div class="inspect__copy">
        <p class="label">{{ $b['inspect_kicker'] }}</p>
        <h2 id="inspect-title" class="display" data-split>{{ $b['inspect_title'] }}</h2>
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

    {{-- WHO'S ED --}}
    <section class="ed" id="ed" aria-labelledby="ed-title">
      <div class="ed__side">
        <p class="label">{{ $b['ed_kicker'] }}</p>
        <h2 id="ed-title" class="display ed__title" data-split>{{ $b['ed_title'] }}</h2>
        <img class="ed__skull" src="{{ $brand('skull.webp') }}" width="466" height="315" alt="" loading="lazy" data-ed-skull>
      </div>
      <div class="ed__main">
        <p class="ed__text" data-words>{{ $b['ed_text'] }}</p>
        {{-- "Ed Facts": printed like a nutrition label. --}}
        <div class="facts" data-facts>
          <p class="facts__title">{{ $b['facts_title'] }}</p>
          <p class="facts__serving">{{ $b['facts_serving'] }}</p>
          <dl class="facts__rows">
            @foreach ([1, 2, 3, 4, 5, 6, 7] as $i)
              <div class="facts__row"><dt>{{ $b["facts_{$i}_k"] }}</dt><dd>{{ $b["facts_{$i}_v"] }}</dd></div>
            @endforeach
          </dl>
          <p class="facts__note">* {{ $b['facts_note'] }}</p>
        </div>
      </div>
    </section>

    {{-- CLICK TO ORDER --}}
    <a class="band band--dark" href="#shop" data-band>
      <span class="band__track" aria-hidden="true">@for ($r = 0; $r < 4; $r++)<span>{{ $b['cta_2_big'] }}</span><i>✶</i>@endfor</span>
      <span class="band__small">{{ $b['cta_2_small'] }}</span>
      <span class="sr-only">{{ $b['cta_2_big'] }} {{ $b['cta_2_small'] }}</span>
    </a>

    <section class="faq" id="faq" aria-labelledby="faq-title">
      <h2 id="faq-title" class="display" data-split>{{ $b['faq_title'] }}</h2>
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
        <a class="btn btn--ember btn--lg" href="#shop">{!! $roll($b['hero_cta']) !!}<span class="btn__ico">{!! $arrow !!}</span></a>
      </div>
      <nav class="foot__nav" aria-label="Footer">
        <a href="#shop">{{ $b['nav_shop'] }}</a>
        <a href="#flavours">{{ $b['nav_flavours'] }}</a>
        <a href="#how">{{ $b['nav_how'] }}</a>
        <a href="#ed">{{ $b['nav_story'] }}</a>
        <a href="#faq">{{ $b['nav_faq'] }}</a>
        <a href="#top">{{ $b['footer_top'] }} ↑</a>
      </nav>
    </div>
    <div class="foot__mark" aria-hidden="true">
      <img class="foot__skull" src="{{ $brand('skull.webp') }}" width="466" height="315" alt="" loading="lazy">
      <img class="foot__word" src="{{ $brand('wordmark.webp') }}" width="1060" height="353" alt="" loading="lazy">
    </div>
    <p class="foot__legal">© {{ date('Y') }} Big Ed's Jerky. {{ $b['footer_legal'] }}</p>
  </footer>

  {{-- Phones: a cart bar at the bottom once something is in it. --}}
  <button class="bagbar" type="button" data-bagbar data-open-order hidden>
    <span class="bagbar__count" data-bagbar-count>0</span>
    <span class="bagbar__sum"><small>{{ $b['cart'] }}</small><strong data-bagbar-sum></strong></span>
    <span class="bagbar__go">{{ $b['checkout'] }} {!! $arrow !!}</span>
  </button>

  <dialog class="drawer" data-sheet aria-labelledby="sheet-title">
    <div class="drawer__in">
      <header class="drawer__head">
        <h2 id="sheet-title">{{ $b['sheet_title'] }}</h2>
        <button class="drawer__close" type="button" data-close aria-label="{{ $b['sheet_close'] }}">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </header>
      <div class="bag" data-bag aria-hidden="true"></div>
      <div data-sheet-body></div>
    </div>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script type="application/json" id="menu-data">@json($menu)</script>
  <script type="application/json" id="beta-texts">@json($b + ['_ingredients' => $ingredients, '_packs' => asset('img/packs'), '_brand' => asset('img/brand')])</script>
  <script src="{{ asset('vendor/gsap.min.js') }}" defer></script>
  <script src="{{ asset('vendor/ScrollTrigger.min.js') }}" defer></script>
  <script src="{{ asset('vendor/lenis.min.js') }}" defer></script>
  <script type="module" src="{{ $v('js/beta.js') }}"></script>
</body>
</html>
