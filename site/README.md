# CA.web Admin Panel

A multilingual website starter with a modern admin panel, built on **Laravel 12** and **Filament 4**.

- **Admin panel at `/admin`**: news, services (with galleries), pages and SEO, text blocks, images, testimonials, social links, FAQ, terms, privacy policy, inbox, users, roles and languages.
- **Multilingual by default**: each translated form gets a tab for every active language, and every tab must be filled in before saving. Add a language under *System → Languages* and it shows up everywhere.
- **Fast to work with**: global search (`Ctrl/⌘ + K`), SPA navigation, drag-and-drop reordering, in-browser image cropping, dark mode, and unsaved-changes warnings.
- **Inbox** collects contact form messages, with an unread counter, reply-by-email, and an email notification to `ADMIN_EMAIL`.
- **User invitations**: invite someone by email; they choose their own name and password.
- **Public site** with localized URLs (`/en`, `/ro`, …), Vite, Tailwind 4 and Alpine.

## Requirements

- PHP 8.2+ with `intl`, `pdo_sqlite` or `pdo_mysql`, `fileinfo` and `zip`
- Composer 2
- Node.js 20+

## Getting started

```bash
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Set your database and the first administrator in `.env`:

```dotenv
DB_CONNECTION=sqlite        # or mysql with DB_HOST, DB_DATABASE, ...

ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=choose-a-strong-password
```

Then:

```bash
php artisan migrate --seed
php artisan storage:link
composer run dev            # app + Vite dev server + queue + logs
```

Open `http://localhost:8000/admin` and sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

Want sample data to see the dashboard in action? Run `php artisan db:seed --class=DemoSeeder`.

## Look & feel

The panel uses a "liquid glass" theme (`resources/css/filament/admin/theme.css`): a soft colour wallpaper behind frosted-glass panels, a floating toolbar and sidebar, and the Manrope font. Light by default, with a matching dark mode. The dashboard has a Week / Month / Quarter switch that drives every widget. To re-brand it for a client, change the `--ca-wallpaper` colours in the theme, `primary` in `AdminPanelProvider`, and swap the logo in `public/images`. Run `npm run build` after editing the theme.

## Visual editor (editing the website itself)

**Website → Visual editor** shows the client's real website inside the admin. Every editable text and image is outlined: click a text to type over it (Enter saves, Shift+Enter adds a line, Esc cancels), click an image to upload a new one. A toolbar switches page, language and device size (desktop / tablet / phone). Texts that still need translating in the selected language are outlined in amber. Only admins can edit, forms are switched off while editing, and visitors never see any of it.

When you build a front-end, make text and images editable like this:

```blade
<h1>@text('hero_title', 'We build websites people love to use')</h1>
<p>@text('hero_intro', 'One or two sentences about the business.')</p>
<img @image('hero', 'images/hero.jpg') alt="">
```

- The first argument is a unique key, the second is the default (your design copy, or a path in `public/`).
- A key is created automatically the first time its page is opened, so there is nothing to seed. It also appears under **Text blocks** / **Images**, grouped by page.
- Reusing a key (e.g. a phone number in the header and footer) shows and edits the same text everywhere.
- For a background image, use `{{ app(\App\Support\Cms\Cms::class)->imageUrl('key', 'images/bg.jpg') }}`. It is editable from **Images**, not by clicking.
- Inside attributes (`alt`, `title`, meta tags) use `{{ app(\App\Support\Cms\Cms::class)->plain('key', 'Default') }}`; edit those under **Text blocks**.
- The older `$content['key']->text` / `$img['key']` style still works with the same data.

Building a new front-end with an AI assistant? Hand it [`AI_FRONTEND_PROMPT.md`](AI_FRONTEND_PROMPT.md): it explains this project's conventions and how to keep every text and image editable.

To list a page in the editor's page switcher, give it a row in **Pages** whose label is its route name (e.g. `front.index`).

## Website analytics

The dashboard shows who visits the public site: visitors and page views over time, top locations (country and city), pages, traffic sources, devices and a live feed. Visits are recorded by `App\Http\Middleware\TrackVisit` on every public page, including pages you add for a new front-end. The admin panel, logged-in users and bots are never counted.

- **Privacy:** raw IP addresses are not stored. A visitor is a hash of IP, browser and date, so it changes daily.
- **Location:** taken from Cloudflare's `CF-IPCountry` / `CF-IPCity` headers when the site is behind Cloudflare, otherwise looked up on ip-api.com. Set `ANALYTICS_GEO=headers` to never send IPs anywhere, or `none` to skip locations.
- **Retention:** visits older than `ANALYTICS_KEEP_MONTHS` (default 13) are pruned daily by `php artisan model:prune`. Make sure the scheduler runs (`php artisan schedule:work`, or a cron entry for `schedule:run`).
- **Sample data:** `php artisan db:seed --class=VisitSeeder` adds three months of made-up traffic.

## Dashboard shortcuts

The round buttons on the dashboard's website card are per user. Click **Edit** to add, remove, reorder or rename them, pick an icon, and point each one at any admin page or a custom link (up to 8). **Reset to default** brings back the original four.

## Roles

Users with the **admin** role can manage everything. Every other active user can see the dashboard and the inbox. Inactive users can't sign in.

To open a resource to every active user, set `protected static bool $adminOnly = false;` on it.

## Project layout

| Path | What lives there |
|---|---|
| `app/Filament/Resources` | One folder per admin section (resource, pages) |
| `app/Filament/Support/Translations.php` | Language tabs and saving of the `*_trans` tables |
| `app/Filament/Support/Fields.php` | Shared image, gallery and SEO fields |
| `app/Http/Controllers/front` | Public site controllers |
| `resources/views/front` | Public site Blade views |
| `config/filesystems.php` | One public disk per content type (`news`, `service`, …) |

### Adding a translated section

1. Create `Thing` and `ThingTrans` models. `ThingTrans` needs a `lang` column and a `thing_id` foreign key. Give `Thing` a `text()` relation and use the `HasTranslations` trait.
2. Run `php artisan make:filament-resource Thing`. Build the form with `Translations::tabs(...)` and use the `CreatesTranslatableRecord` / `EditsTranslatableRecord` traits on the create and edit pages. `FaqResource` is the smallest example to copy.

## Testing

```bash
php artisan test
```

## Production

Before deploying, run:

```bash
npm run build
php artisan optimize
php artisan filament:optimize
```
