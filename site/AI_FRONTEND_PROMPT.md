# Prompt: build a front-end on top of this admin panel

Copy everything below the line into your AI coding assistant (Claude Code, Cursor, …) from the root of a fresh copy of this project. Replace the `[…]` parts first.

---

You are working in a Laravel 12 project that already contains a finished admin panel (Filament 4, at `/admin`). Your job is to build the **public website** for a client on top of it, so that the client can manage all of its content from the admin.

## The website to build

- Client / business: [name, what they do, audience]
- Pages: [e.g. Home, Services, Service detail, News, Article, Contact, FAQ, Privacy, Terms]
- Design: [paste a description, screenshots, a Figma link, or an existing HTML/CSS template in `[path]`]
- Languages: [e.g. English (main), Romanian, Russian]
- Anything special: [e.g. booking button, Google Map, Instagram feed]

## Do not touch

- The admin panel: `app/Filament/**`, `app/Providers/Filament/**`, `resources/css/filament/**`, `resources/views/filament/**`.
- Visit tracking and the visual editor: `app/Http/Middleware/TrackVisit.php`, `app/Http/Middleware/InjectVisualEditor.php`, `app/Support/**`, `resources/views/cms/**`, `routes` under `/cms`.
- Existing migrations and the database structure. Add new migrations only if the design truly needs data the admin can't hold yet, and say so before doing it.

## How the public site is wired

- Routes live in `routes/web.php` inside the `Route::prefix('{locale}')` group (URLs look like `/en/services`). `SetLocale` sets the language. Always build links with `route('name')`; the locale is filled in automatically.
- Controllers are in `app/Http/Controllers/front`. Views are in `resources/views/front` and extend `resources/views/layouts/front.blade.php` (`@yield('content')`), which includes `front/inc/header.blade.php` (head + SEO), `front/inc/menu.blade.php` (site header) and `front/inc/footer.blade.php`.
- Styling: Tailwind CSS 4 and Alpine.js via Vite (`resources/css/app.css`, `resources/js/app.js`). Run `npm run build` (or `npm run dev`).
- **URL gotcha:** a single word of 2-10 letters right after the domain (e.g. `/about`, `/landing`) is treated as a language code. Put new pages inside the `{locale}` group (`/en/about`) or use a hyphenated path (`/about-us`).

## Content the admin already manages, and how to show it

Existing controllers already pass these to their views (in the current language, only published items). Reuse them; add a controller method only for new pages.

| Admin section | In Blade | Fields | Images |
|---|---|---|---|
| Services | `$services` (Home, Services, FAQ, Contact), `$service` + `$relevantServices` (detail) | `title`, `sub_title`, `text` (HTML), `slug`, `img` | `Storage::disk('service')->url($s->img)` |
| News | `$newses` (list), `$news` (article) | `title`, `sub_title`, `excerpt`, `text` (HTML), `slug`, `img`, `cover_img`, `created_at` | disk `news` |
| Testimonials | `$testimonials` (Home) | `name`, `function`, `text`, `img` | disk `testimonial` |
| Social links | `$socials` (Home) | `name`, `url`, `img` | disk `social` |
| FAQ | `$faqs` (FAQ page) | `title`, `text` (HTML) | – |
| Terms / Privacy | their pages | `title`, `text` (HTML) | – |
| Pages (SEO) | `$meta` | `title`, `description`, `keywords`, `img` | handled by `front/inc/meta.blade.php` |
| Inbox | contact form | see below | – |

- Detail URLs: `route('front.single.service', $service->slug)` and `route('front.single_news', $news->slug)`.
- Rich text fields (`text`) contain HTML from the admin editor: print them with `{!! $item->text !!}` and style them with Tailwind's `prose` classes (add `@tailwindcss/typography` if needed).
- Menu: `getMenuPages()` returns the active pages with their translated `title` and route name `label`.
- Language switcher: `getLangs()` returns the other active languages (`code`, `name`).
- **Contact form:** `POST route('front.contact.storage')` with `@csrf` and the fields `name`, `email`, `phone`, `subject`, `message` (all required). It saves to the admin Inbox, emails `ADMIN_EMAIL`, redirects to the thank-you page, and is rate-limited. Show validation errors with `@error`.

## Make every static text and image editable (important)

The admin has a **Visual editor** where the client clicks on the live site to change texts and images. That only works for content written like this:

```blade
<h1>@text('home_hero_title', 'We build websites people love to use')</h1>
<p>@text('home_hero_intro', 'One or two sentences about the business.')</p>
<a href="{{ route('front.contact') }}">@text('home_hero_cta', 'Get in touch')</a>
<img @image('home_hero', 'images/hero.jpg') alt="{{ app(\App\Support\Cms\Cms::class)->plain('home_hero_alt', 'Our team at work') }}">
```

Rules:

1. **No hard-coded copy.** Every heading, paragraph, button label, stat, list item, footer line, contact detail and decorative image in the design goes through `@text` or `@image`. Only data from the admin sections above (services, news, …) is printed directly.
2. Keys are `snake_case` and prefixed by page or area: `home_…`, `services_…`, `contact_…`, `header_…`, `footer_…`. Use the same key for the same text in several places (brand name, phone, email) so one edit updates everywhere.
3. The second argument is the real design copy in the main language (English unless told otherwise) and, for images, a file you place in `public/images/…`. Keys are created automatically the first time the page is opened; never seed them.
4. `@text` returns escaped text with line breaks as `<br>`. Don't put HTML in it. For repeated items, loop with numbered keys: `@text("home_feature_{$n}_title", $default)`.
5. `@image` prints `src="…"` for an `<img>` tag only. For CSS backgrounds use `{{ app(\App\Support\Cms\Cms::class)->imageUrl('key', 'images/bg.jpg') }}`. That stays editable from Admin → Images, but not by clicking.
6. Inside attributes (`alt`, `title`, `placeholder`, `aria-label`, `<title>`, meta tags) use `{{ app(\App\Support\Cms\Cms::class)->plain('key', 'Default') }}`, never `@text`: in the Visual editor `@text` adds markup that would break the attribute. Those texts are edited under Admin → Text blocks.
7. Each page that should appear in the Visual editor's page switcher needs a `pages` row whose `label` is its route name (`PageSeed` creates them for the built-in pages). For a new page, add it to `database/seeders/PageSeed.php` and to the live database.
8. Remove the demo content in `resources/views/front/pages/home.blade.php` and the demo images `public/images/demo*.svg` once the real design is in.

## Quality bar

- Pixel-faithful to the design, responsive from 360px to wide screens, accessible (semantic HTML, `alt`, focus states, contrast), fast (sized and lazy-loaded images, no unused JS).
- Every page has a sensible `@section('title', …)` and uses `$meta` for SEO.
- Works in every active language. Untranslated texts fall back to the main language, so the site never shows empty spots.
- No secrets, client data or real email addresses committed; use `.env`.

## When you are done

1. `npm run build` and `php artisan test` pass.
2. Open each page at `/en/...` (and the other languages) and check that nothing is missing or broken.
3. Log in at `/admin`, open **Website → Visual editor**, switch through every page and confirm all texts and images are outlined and can be changed.
4. Report: the pages built, any text or image you could **not** make editable and why, new routes, new migrations (if any), and anything the client still has to fill in (translations, photos, SEO).
