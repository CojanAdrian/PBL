{{--
    Big Ed's Jerky: the whole site is this one page. Every text goes through
    @text / plain() and the photos through @image, so they can be changed in
    Admin -> Visual editor (or Text blocks / Images). The order builder's own
    texts are the `shop_…` keys under Text blocks.
--}}
@php
    $cms = app(\App\Support\Cms\Cms::class);
    $langs = \App\Models\Lang::where('active', true)->orderBy('id')->get(['code', 'name']);
    $locale = app()->getLocale();
    $title = $meta?->title ?: "Big Ed's Jerky | ".$cms->plain('hero_title_1', 'Real meat.').' '.$cms->plain('hero_title_2', 'Bold flavor.');
    $description = $meta?->description ?: $cms->plain('hero_lead');
    $ogImage = $meta?->img ? Storage::disk('page')->url($meta->img) : asset('img/og.jpg');
    $arrow = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
    $contact = collect([
        'phone' => $cms->plain('footer_phone'),
        'email' => $cms->plain('footer_email'),
        'instagram' => ltrim($cms->plain('footer_instagram'), '@'),
        'telegram' => ltrim($cms->plain('footer_telegram'), '@'),
    ])->filter();
@endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}">
  @if ($meta?->keywords)
    <meta name="keywords" content="{{ $meta->keywords }}">
  @endif
  <meta name="theme-color" content="#0a0908">
  <link rel="icon" type="image/png" href="{{ $cms->imageUrl('favicon', 'img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
  @foreach ($langs as $lang)
    <link rel="alternate" hreflang="{{ $lang->code }}" href="{{ route('front.index', ['locale' => $lang->code]) }}">
  @endforeach
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="preload" href="{{ asset('fonts/anton-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('fonts/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ $cms->imageUrl('site_logo', 'img/logo.webp') }}" as="image">
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site.css') }}">
  <link rel="stylesheet" href="{{ asset('css/builder.css') }}">
  <script src="{{ asset('js/flag.js') }}"></script>
</head>
<body>
  <a class="skip" href="#main">@text('skip_link', 'Skip to content')</a>

  <!-- Shared gradients for the pouch illustrations -->
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

  <div class="preloader" aria-hidden="true">
    <div class="preloader__inner">
      <svg class="preloader__ring" viewBox="0 0 120 120"><circle cx="60" cy="60" r="54" pathLength="100"/></svg>
      <img @image('site_logo', 'img/logo.webp') width="96" height="96" alt="">
    </div>
  </div>

  <div class="progress" aria-hidden="true"><span></span></div>
  <div class="cursor" aria-hidden="true"></div>

  <header class="nav" id="nav">
    <div class="nav__bar">
      <a class="nav__brand" href="#top" aria-label="Big Ed's Jerky">
        <img @image('site_logo', 'img/logo.webp') width="44" height="44" alt="">
        <span>@text('nav_brand', "Big Ed's")</span>
      </a>
      <nav class="nav__links" aria-label="Main">
        <a href="#story">@text('nav_story', 'Story')</a>
        <a href="#flavours">@text('nav_flavours', 'Flavours')</a>
      </nav>
      @if ($langs->count() > 1)
        <nav class="nav__langs" aria-label="{{ $cms->plain('nav_languages', 'Language') }}">
          @foreach ($langs as $lang)
            <a href="{{ route('front.index', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" title="{{ $lang->name }}" @if ($lang->code === $locale) aria-current="true" @endif>{{ strtoupper($lang->code) }}</a>
          @endforeach
        </nav>
      @endif
      <a class="btn btn--primary btn--sm magnetic nav__cta" href="#preorder"><span>@text('nav_preorder', 'Preorder')</span></a>
      <button class="nav__toggle" type="button" aria-expanded="false" aria-controls="menu" aria-label="{{ $cms->plain('nav_open_menu', 'Open menu') }}">
        <span></span><span></span>
      </button>
    </div>
  </header>

  <div class="menu" id="menu" hidden>
    <nav aria-label="Mobile">
      <a href="#story">@text('nav_story', 'Story')</a>
      <a href="#flavours">@text('nav_flavours', 'Flavours')</a>
      <a href="#preorder" class="menu__cta">@text('nav_preorder', 'Preorder')</a>
    </nav>
    @if ($langs->count() > 1)
      <nav class="menu__langs" aria-label="{{ $cms->plain('nav_languages', 'Language') }}">
        @foreach ($langs as $lang)
          <a href="{{ route('front.index', ['locale' => $lang->code]) }}" hreflang="{{ $lang->code }}" lang="{{ $lang->code }}" @if ($lang->code === $locale) aria-current="true" @endif>{{ $lang->name }}</a>
        @endforeach
      </nav>
    @endif
    <p>@text('menu_tagline', "Premium beef & chicken jerky.\nMade in Moldova.")</p>
  </div>

  <main id="main">
    <!-- HERO -->
    <section class="hero" id="top" aria-labelledby="hero-title">
      <canvas class="embers" aria-hidden="true"></canvas>
      <div class="hero__word" aria-hidden="true">@text('hero_word', 'JERKY')</div>
      <div class="container hero__grid">
        <div class="hero__copy">
          <p class="eyebrow">@text('hero_eyebrow', 'Beef & chicken jerky · Home-made · Moldova')</p>
          <h1 class="hero__title" id="hero-title">
            <span class="line"><span>@text('hero_title_1', 'Real meat.')</span></span>
            <span class="line"><span class="hot">@text('hero_title_2', 'Bold flavor.')</span></span>
          </h1>
          <p class="hero__lead">@text('hero_lead', \App\Shop\Texts::en('hero_lead'))</p>
          <div class="hero__cta">
            <a class="btn btn--primary btn--lg magnetic" href="#preorder"><span>@text('hero_cta', 'Preorder your pack')</span>{!! $arrow !!}</a>
            <a class="btn btn--ghost btn--lg" href="#flavours"><span>@text('hero_cta_2', 'Explore flavours')</span></a>
          </div>
          <ul class="hero__facts">
            <li><strong>@text('hero_fact_1_strong', 'Beef & chicken')</strong> @text('hero_fact_1_text')</li>
            <li><strong>@text('hero_fact_2_strong', '3')</strong> @text('hero_fact_2_text', 'flavours')</li>
            <li><strong>@text('hero_fact_3_strong', '100 g')</strong> @text('hero_fact_3_text', 'packs')</li>
            <li>@text('hero_fact_4_text', 'Made in') <strong>@text('hero_fact_4_strong', 'Moldova')</strong></li>
          </ul>
        </div>

        <div class="hero__visual" data-parallax>
          <div class="hero__glow" aria-hidden="true"></div>
          <img class="hero__logo" @image('hero_logo', 'img/logo.webp') width="900" height="905" alt="{{ $cms->plain('hero_logo_alt', \App\Shop\Texts::en('hero_logo_alt')) }}" style="--depth:1">
          <a class="hero__badge" href="#preorder" style="--depth:1.6" aria-label="{{ $cms->plain('hero_badge_aria', 'Preorders are open. Go to the preorder builder.') }}">
            <svg viewBox="0 0 120 120" aria-hidden="true">
              <defs><path id="badgeCircle" d="M60,60 m-44,0 a44,44 0 1,1 88,0 a44,44 0 1,1 -88,0"/></defs>
              <text><textPath href="#badgeCircle" startOffset="0" textLength="270" lengthAdjust="spacing">{{ $cms->plain('hero_badge', 'PREORDERS OPEN • SMALL BATCH • ') }}</textPath></text>
            </svg>
            <span class="hero__badge-arrow">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
            </span>
          </a>
        </div>
      </div>
      <a class="scroll-cue" href="#story" aria-label="{{ $cms->plain('hero_scroll_aria', 'Scroll to the story') }}"><span></span></a>
    </section>

    <!-- MARQUEE -->
    <div class="marquee" aria-hidden="true">
      <div class="marquee__track">
        @for ($group = 0; $group < 2; $group++)
          <div class="marquee__group">
            <span>@text('marquee_1', 'Beef & chicken')</span><i></i><span class="outline">@text('marquee_2', 'Bold flavor')</span><i></i><span>@text('marquee_3', 'Made in Moldova')</span><i></i><span class="outline">@text('marquee_4', 'Home-made')</span><i></i><span>@text('marquee_5', 'Small batch')</span><i></i>
          </div>
        @endfor
      </div>
    </div>

    <!-- STORY -->
    <section class="story" id="story" aria-labelledby="story-title">
      <div class="container story__grid">
        <div class="story__text">
          <p class="eyebrow" data-reveal>@text('story_eyebrow', 'The story')</p>
          <h2 class="h2" id="story-title" data-reveal>@text('story_title_1', 'Straight from') <span class="hot">@text('story_title_2', "Ed's kitchen.")</span></h2>
          <p class="story__big" data-words>@text('story_text', \App\Shop\Texts::en('story_text'))</p>
          <div class="story__ingredients" data-reveal>
            <h3>@text('story_ingredients_title', 'What goes in')</h3>
            <ul>
              {{-- Admin -> Shop -> Ingredients --}}
              @foreach (\App\Models\Ingredient::active()->with('text')->orderBy('order_column')->get() as $ingredient)
                <li>{{ $ingredient->translation()?->name }}</li>
              @endforeach
            </ul>
            <p class="note">@text('story_contains', 'Contains soy.')</p>
          </div>
        </div>
        <figure class="story__photo" data-clip>
          <div class="story__photo-frame">
            <img @image('story_photo', 'img/jerky-board.webp') width="900" height="1348" alt="{{ $cms->plain('story_photo_alt', \App\Shop\Texts::en('story_photo_alt')) }}" loading="lazy">
          </div>
          <figcaption>@text('story_caption', 'Real beef. Bold flavor.')</figcaption>
        </figure>
      </div>
    </section>

    {{-- The "Craft" section (four steps) is switched off for now. --}}

    <!-- FLAVOURS -->
    <section class="flavours" id="flavours" aria-labelledby="flavours-title">
      <div class="container">
        <header class="section-head">
          <p class="eyebrow" data-reveal>@text('flavours_eyebrow', 'The flavours')</p>
          <h2 class="h2" id="flavours-title" data-reveal>@text('flavours_title_1', 'Pick your') <span class="hot">@text('flavours_title_2', 'fire.')</span></h2>
          <p class="lead" data-reveal>@text('flavours_lead', \App\Shop\Texts::en('flavours_lead'))</p>
        </header>
        <div class="fstage" id="fstage" data-reveal>
          <div class="fstage__tabs" role="tablist" aria-label="{{ $cms->plain('nav_flavours', 'Flavours') }}" aria-orientation="vertical"></div>
          <div class="fstage__panel" role="tabpanel" aria-live="polite">
            <div class="fstage__glow" aria-hidden="true"></div>
            <div class="fstage__pouch"></div>
            <div class="fstage__info">
              <p class="fstage__tag"></p>
              <h3 class="fstage__name"></h3>
              <p class="fstage__notes"></p>
              <div class="fstage__heat"></div>
              <button class="btn btn--primary magnetic" type="button" id="fstage-add"><span>{{ $cms->plain('flavours_add', 'Add to my box') }}</span></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- PREORDER -->
    <section class="preorder" id="preorder" aria-labelledby="preorder-title">
      <div class="container">
        <header class="section-head">
          <p class="eyebrow" data-reveal>@text('preorder_eyebrow', 'Preorder')</p>
          <h2 class="h2" id="preorder-title" data-reveal>@text('preorder_title_1', 'Build your') <span class="hot">@text('preorder_title_2', 'box.')</span></h2>
          <p class="lead" data-reveal>@text('preorder_lead', \App\Shop\Texts::en('preorder_lead'))</p>
        </header>
        <div class="builder" id="builder">
          <noscript><p class="builder__noscript">{{ $cms->plain('preorder_noscript', \App\Shop\Texts::en('preorder_noscript')) }}</p></noscript>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container footer__grid">
      <div class="footer__brand">
        <img @image('site_logo', 'img/logo.webp') width="96" height="96" alt="Big Ed's Jerky" loading="lazy">
        <p>@text('footer_tagline', "Premium beef & chicken jerky.\nMade by hand in Moldova.")</p>
      </div>
      <nav class="footer__nav" aria-label="Footer">
        <a href="#story">@text('nav_story', 'Story')</a>
        <a href="#flavours">@text('nav_flavours', 'Flavours')</a>
        <a href="#preorder">@text('nav_preorder', 'Preorder')</a>
      </nav>
      <ul class="footer__contact">
        @if ($cms->editing())
          {{-- In the Visual editor the contact details are always shown, so they can be filled in. --}}
          <li><span>@text('footer_phone')</span></li>
          <li><span>@text('footer_email')</span></li>
          <li><span>@text('footer_instagram')</span></li>
          <li><span>@text('footer_telegram')</span></li>
        @elseif ($contact->isEmpty())
          <li><span>@text('footer_contact_empty', \App\Shop\Texts::en('footer_contact_empty'))</span></li>
        @else
          @isset($contact['phone'])
            <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></li>
          @endisset
          @isset($contact['email'])
            <li><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
          @endisset
          @isset($contact['instagram'])
            <li><a href="https://instagram.com/{{ $contact['instagram'] }}" rel="noopener">Instagram {{ '@'.$contact['instagram'] }}</a></li>
          @endisset
          @isset($contact['telegram'])
            <li><a href="https://t.me/{{ $contact['telegram'] }}" rel="noopener">Telegram {{ '@'.$contact['telegram'] }}</a></li>
          @endisset
        @endif
      </ul>
    </div>
    <div class="container footer__legal">
      <p>© {{ date('Y') }} @text('footer_legal', \App\Shop\Texts::en('footer_legal'))</p>
    </div>
  </footer>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script type="application/json" id="menu-data">@json($menu)</script>
  <script src="{{ asset('vendor/gsap.min.js') }}" defer></script>
  <script src="{{ asset('vendor/ScrollTrigger.min.js') }}" defer></script>
  <script src="{{ asset('vendor/lenis.min.js') }}" defer></script>
  <script type="module" src="{{ asset('js/main.js') }}"></script>
</body>
</html>
