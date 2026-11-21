{{--
    The redesigned Big Ed's page, tested at /{locale}/betatest before it replaces
    home.blade.php. Same products, prices and order endpoint as the main page;
    texts in App\Shop\BetaTexts (order-form errors still come from Text blocks).

    One WebGL canvas (public/js/beta3d.js) sits behind the page. The pack flies to
    whichever [data-anchor] box is on screen: the hero, the intro, the flavours.
    Everything else (flavour list, order panel) is drawn by public/js/beta.js.
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
    $hint = '<span class="hint" aria-hidden="true"><i></i>'.e($b['hint_drag']).' · '.e($b['hint_tap']).'</span>';
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
  <meta name="theme-color" content="#0e0b09">
  <link rel="icon" type="image/png" href="{{ $cms->imageUrl('favicon', 'img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ asset('fonts/anton-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="modulepreload" href="{{ asset('vendor/three/three.module.min.js') }}">
  <link rel="stylesheet" href="{{ $v('css/beta.css') }}">
  <script>document.documentElement.className = 'js';</script>
  <script type="importmap">{"imports": {"three": "{{ asset('vendor/three/three.module.min.js') }}"}}</script>
</head>
<body>
  <a class="skip" href="#main">{{ $cms->plain('skip_link', 'Skip to content') }}</a>

  {{-- Gradients used by the 2D pack drawings in the order panel (pouch.js adds one per meat). --}}
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

  <div class="glow" aria-hidden="true"></div>
  <canvas class="stage3d" data-stage aria-hidden="true"></canvas>

  <p class="betabar">{{ $b['beta_bar'] }} <a href="{{ route('front.index', ['locale' => $locale]) }}">{{ $b['beta_back'] }}</a></p>

  <header class="nav" data-nav>
    <div class="nav__in">
      <a class="nav__brand" href="#top" aria-label="Big Ed's Jerky">
        <img src="{{ $cms->imageUrl('site_logo', 'img/logo.webp') }}" width="40" height="40" alt="">
        <span>Big Ed's</span>
      </a>
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
      <button class="nav__order" type="button" data-open-order>
        <span>{{ $b['nav_order'] }}</span><span class="count" data-count>0</span>
      </button>
    </div>
  </header>

  <main id="main">
    <section class="hero" id="top" aria-labelledby="hero-title">
      <div class="hero__grid">
        <div class="hero__copy">
          <p class="kicker" data-in>{{ $b['hero_kicker'] }}</p>
          <h1 class="hero__title" id="hero-title">
            <span class="hl" data-split>{{ $b['hero_title_1'] }}</span>
            <span class="hl hl--accent" data-split>{{ $b['hero_title_2'] }}</span>
          </h1>
          <p class="hero__lead" data-in>{{ $b['hero_lead'] }}</p>
          <div class="hero__act" data-in>
            <a class="btn btn--fire" href="#flavours"><span>{{ $b['hero_cta'] }}</span><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg></a>
            <p class="hero__price"><strong>{{ str_replace('{price}', $price($cheapest), $b['hero_from']) }}</strong><span>{{ $b['hero_pay'] }}</span></p>
          </div>
        </div>
        <div class="anchor hero__anchor" data-anchor data-fill="1" data-drag>
          <div class="fallback" data-fallback></div>
          {!! $hint !!}
        </div>
      </div>
    </section>

    <section class="intro" id="new" aria-labelledby="new-title">
      <div class="wrap intro__grid">
        <div class="anchor intro__anchor" data-anchor data-fill=".8" data-desktop-only></div>
        <div class="intro__copy">
          <p class="kicker" data-in>{{ $b['new_kicker'] }}</p>
          <h2 id="new-title" data-words>{{ $b['new_title'] }}</h2>
          <ul class="points">
            @foreach ([1, 2, 3] as $i)
              <li data-in>
                <h3>{{ $b["new_{$i}_t"] }}</h3>
                <p>{{ $b["new_{$i}_d"] }}</p>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </section>

    {{-- THE FLAVOURS: pinned on large screens; scrolling (or clicking) changes flavour and the pack explodes. --}}
    <section class="lab" id="flavours" data-lab aria-labelledby="lab-title">
      <div class="lab__stage">
        <div class="lab__copy">
          <div class="lab__head">
            <p class="kicker" id="lab-title">{{ $b['lab_kicker'] }}</p>
            <p class="lab__lead">{{ str_replace('{g}', $packGrams, $b['lab_lead']) }}</p>
          </div>
          <div class="lab__list" role="tablist" aria-labelledby="lab-title" data-list></div>
          <div class="lab__details" role="tabpanel" data-details aria-live="polite"></div>
          <noscript><p>{{ $b['lab_noscript'] }}</p></noscript>
        </div>
        <div class="anchor lab__anchor" data-anchor data-fill="1.05" data-drag>
          <div class="fallback" data-fallback></div>
          {!! $hint !!}
        </div>
        <div class="lab__progress" aria-hidden="true"><span data-progress></span></div>
      </div>
    </section>

    <section class="how" id="how" aria-labelledby="how-title">
      <div class="wrap">
        <h2 id="how-title" data-words>{{ $b['how_title'] }}</h2>
        <ol class="steps">
          @foreach ([1, 2, 3] as $i)
            <li data-in>
              <span class="steps__n" aria-hidden="true">0{{ $i }}</span>
              <h3>{{ $b["how_{$i}_t"] }}</h3>
              <p>{{ $b["how_{$i}_d"] }}</p>
            </li>
          @endforeach
        </ol>
        <p class="ingredients" data-in><strong>{{ $b['ingredients_title'] }}:</strong> {{ $ingredients->implode(', ') }}. <em>{{ $b['contains_soy'] }}</em></p>
      </div>
    </section>

    <section class="faq" id="faq" aria-labelledby="faq-title">
      <div class="wrap faq__grid">
        <h2 id="faq-title" data-words>{{ $b['faq_title'] }}</h2>
        <div class="faq__list">
          @foreach ([1, 2, 3, 4, 5] as $i)
            <details data-in>
              <summary>{{ $b["faq_{$i}_q"] }}</summary>
              <div class="faq__a"><p>{{ $b["faq_{$i}_a"] }}</p></div>
            </details>
          @endforeach
        </div>
      </div>
    </section>

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
          <a href="#faq">{{ $b['nav_faq'] }}</a>
          <a href="#top">{{ $b['footer_top'] }} ↑</a>
        </nav>
      </div>
      <p class="foot__word" aria-hidden="true" data-foot-word>Big Ed's</p>
      <p class="wrap foot__legal">© {{ date('Y') }} Big Ed's Jerky. {{ $b['footer_legal'] }}</p>
    </footer>
  </main>

  {{-- Shows once something is in the order. --}}
  <button class="ticket" type="button" data-ticket data-open-order hidden>
    <span class="ticket__count" data-ticket-count>0</span>
    <span class="ticket__sum"><small data-ticket-packs></small><strong data-ticket-total></strong></span>
    <span class="ticket__go">{{ $b['bar_review'] }}</span>
  </button>

  <dialog class="panel" data-sheet aria-labelledby="sheet-title">
    <div class="panel__in">
      <header class="panel__head">
        <h2 id="sheet-title">{{ $b['sheet_title'] }}</h2>
        <button class="panel__close" type="button" data-close aria-label="{{ $b['sheet_close'] }}">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
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
