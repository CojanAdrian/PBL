# Big Ed's Jerky

Website and preorder system for Big Ed's Jerky, premium beef and chicken jerky made in Moldova. English, Romanian and Russian.

The site runs on the **CA.web admin panel** (Laravel 12 + Filament 4) in [site/](site/): one page with the flavours and the order builder, and an admin at `/admin` where Ed sees the orders, changes products and prices, and edits every text and photo on the site.

| Path | What it is |
| --- | --- |
| `site/resources/views/front/pages/home.blade.php` | The page (every text is editable from the admin) |
| `site/public/css`, `site/public/js`, `site/public/img` | The page's styles, the order builder, images |
| `site/app/Shop/` | Prices and order checks, emails / Telegram / Google Sheet, the starting texts (EN/RO/RU), the installer |
| `site/app/Filament/Resources/Orders`, `.../Products` | The admin's Shop section |
| `site/config/shop.php` | Currency, custom sizes on/off, order limits, notification keys |
| `site/google-sheets/apps-script.gs` | The script that runs in the orders Google Sheet |
| `site/tests/` | Tests (`php vendor/bin/phpunit --testsuite Feature`) |
| `web-sim/` | The **old** Node site. Kept until the new one is live, then it can be deleted. |

## The admin (`/admin`)

Sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

- **Dashboard**: visitors, page views, orders and order value for the week / month / quarter, latest orders, top flavours, where visitors come from.
- **Shop → Orders**: every preorder, newest first. Click **View** for the details, **Delete** removes an order here and its row in the Google Sheet. Boxes at the top say whether order emails and the Google Sheet are on and whether the last one worked; **Open Google Sheet**, **Send test email** and **Sync all to Google Sheet** are next to them. Order status (New, Confirmed, Done, Cancelled) is kept in the Google Sheet.
- **Shop → Products**: everything on the menu. Each product is a flavour of one **meat type** in one **pack type** (Texas BBQ · Beef · 100 g pack), or a mix like the taster box. Change prices, weights, names, taglines and descriptions (in every language), heat, colours; drag to reorder; switch **On the website** off to hide a product. The price is for one of it, as sold.
- **Shop → Meat types**: beef, chicken, … Add more (e.g. pork): name in every language and the pouch colours. Switching a meat off hides all its products. A meat can only be deleted when no product uses it.
- **Shop → Pack types**: pack, box, … Add more (e.g. sack): name in every language, how it is drawn (pouch, box or sack), and whether it is a **mix** of several flavours (like the taster box). Same on/off and delete rules as meats.
- **Shop → Ingredients**: the "What goes in" list in the story. Add, delete, reorder, translate.
- **Website → Visual editor**: the real site inside the admin. Click any outlined text to change it (Enter saves), including the order section's texts. Click a logo (header, footer, hero) or the story photo to upload a new one. Switch the language at the top to translate. Texts that still need a translation are outlined in amber. Product names and descriptions are changed under **Shop → Products**, the ingredients under **Shop → Ingredients**.
- **Website → Text blocks**: every text, including the order builder's own (the `shop_…` keys) and the footer contact details (`footer_phone`, `footer_email`, `footer_instagram`, `footer_telegram`; empty ones are hidden). Keep `{placeholders}` like `{name}` in the texts (texts with placeholders are edited here, not in the Visual editor). Texts and images can't be deleted, only changed; the favicon is the `favicon` image.
- **Website → Pages**: the page title and description for Google and link previews, per language.
- **System**: users (invite people), roles, languages (English, Romanian and Russian; switch one off to hide it, new ones can't be added). New pages can't be added either: the site is one page.

The starter's other sections (news, services, testimonials, FAQ, terms, privacy, inbox) are hidden from the menu and their pages are switched off, because the jerky site doesn't use them.

## Prices

Set in the admin under **Shop → Products**. They start as:

| What | Price |
| --- | --- |
| Beef, 100 g pack | 130 lei |
| Chicken, 100 g pack | 70 lei |
| Taster box (all six flavours, 6 x 50 g = 300 g) | 285 lei |

Custom weights are switched off (`custom.enabled` in `site/config/shop.php`). Switched on, customers can pick any weight of a one-flavour product, priced from its own weight and price (250 g of a 100 g / 130 lei product = 325 lei). The server checks and prices every preorder itself, so what people see is what they are charged.

## Deploy on Railway

The existing Railway project (website service + Postgres) is reused. The new site keeps the old orders: on its first deploy it copies them from the old `preorders` table.

1. Website service → **Settings**:
   - **Root Directory**: `/site` (was `/web-sim`).
   - **Deploy → Pre-deploy Command**: `php artisan shop:install` (it runs the migrations first)
2. **Volume**: add a volume to the website service, mount path `/app/storage/app/public`. Photos uploaded in the admin are stored there; without a volume they disappear on every deploy.
3. **Variables** on the website service:

   | Variable | Value |
   | --- | --- |
   | `APP_KEY` | A key from `php artisan key:generate --show` |
   | `APP_URL` | The site's address, e.g. `https://bigeds.up.railway.app` |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_URL` | `${{Postgres.DATABASE_URL}}` |
   | `RAILPACK_PHP_EXTENSIONS` | `pdo_pgsql,intl,zip` |
   | `ADMIN_EMAIL`, `ADMIN_PASSWORD` | The first admin's login (only used while there is no user yet) |
   | `QUEUE_CONNECTION` | `sync` |
   | `LOG_CHANNEL` | `stderr` |
   | `ORDER_EMAIL_TO` | *(optional)* who gets order emails. Defaults to `kenny@igtfreight.com` |
   | `BREVO_API_KEY` **or** `RESEND_API_KEY`, and `EMAIL_FROM` | Order emails (same as before) |
   | `SHEETS_WEBHOOK_URL`, `SHEETS_TOKEN` | The Google Sheet (same values as before) |
   | `SHEETS_SPREADSHEET_URL` | *(optional)* the sheet's own link for the **Open Google Sheet** button. Not needed once the sheet runs the latest script, which sends its link by itself |
   | `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID` | *(optional)* a Telegram message for every order |

   The old `DATABASE_URL` and `ADMIN_KEY` variables are not used any more and can be removed.
4. Deploy. Railway builds the PHP app (Railpack: FrankenPHP, Composer, `npm run build`). The pre-deploy command creates the tables and runs `shop:install`, which adds the languages, the admin user, all texts in English, Romanian and Russian, the products, and copies the old orders. It only ever adds what is missing, so it is safe on every deploy and never overwrites what was changed in the admin.
5. Open `/admin`, sign in, and check the two boxes on **Shop → Orders**.

## Order emails

Every new preorder is emailed to `ORDER_EMAIL_TO` through Brevo or Resend's web API (Railway blocks normal SMTP on its cheaper plans). Set **one** of them up:

**Brevo** (free, 300 emails a day, no domain needed): create an account, add and verify a sender address under *Senders, Domains & Dedicated IPs → Senders*, create a key under *SMTP & API → API keys*, then set `BREVO_API_KEY` and `EMAIL_FROM` (e.g. `Big Ed's Jerky <that verified address>`).

**Resend** (free, 100 emails a day): verify your own domain, create an API key, set `RESEND_API_KEY` and `EMAIL_FROM` (an address on that domain).

**Send test email** on **Shop → Orders** sends one right away and shows the exact reason if the service refuses it. If an email fails, the order is still saved.

These keys are only for order emails. The admin's own emails (user invitations, password resets) use Laravel's normal mail settings (`MAIL_*`); on Railway's cheaper plans SMTP is blocked, so add users with a password under **System → Users** instead of inviting them.

## Google Sheets (every order, live)

Every new preorder is added to a Google Sheet as it comes in, with Status **New**. Ed keeps track of orders there: the Status column has a dropdown (New, Confirmed, Done, Cancelled) and colours itself. Deleting an order in the admin deletes its row too.

The sheet set up for the old site keeps working as it is: use the same `SHEETS_WEBHOOK_URL` and `SHEETS_TOKEN`. For a new sheet:

1. Create a Google Sheet. Under *File → Settings* set the time zone to *(GMT+02:00) Chisinau*.
2. *Extensions → Apps Script*: delete everything in `Code.gs`, paste the whole of [`site/google-sheets/apps-script.gs`](site/google-sheets/apps-script.gs) (it must start with `/**`, not inside `function myFunction() {`), and save.
3. *Project Settings → Script properties → Add script property*: `TOKEN` = a long random password.
4. *Deploy → New deployment → Web app*, *Execute as*: **Me**, *Who has access*: **Anyone**. Copy the URL ending in `/exec`.
5. Set `SHEETS_WEBHOOK_URL` (that URL) and `SHEETS_TOKEN` (the same password) on Railway.
6. In the admin, **Shop → Orders → Sync all to Google Sheet** adds every order so far (an `Orders` tab is created).

Notes:
- **Sync all** only adds orders that aren't in the sheet yet; rows already there are never changed.
- Changes made in the sheet stay in the sheet. To delete an order, use **Delete** in the admin, otherwise **Sync all** would add it back.
- Need Excel? *File → Download → Microsoft Excel (.xlsx)* in the sheet.
- After changing the script: *Deploy → Manage deployments → ✏️ → Version: New version* keeps the same URL.
- The **Open Google Sheet** button on **Shop → Orders** appears once the site knows the sheet's link: the script sends it with every reply (paste the latest script and make a new version), or set `SHEETS_SPREADSHEET_URL`.

## Telegram notifications (optional)

1. In Telegram, talk to **@BotFather**, send `/newbot`, and copy the token.
2. Send any message to your new bot, then open `https://api.telegram.org/bot<TOKEN>/getUpdates` and copy the `chat` `id`.
3. Set `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` on Railway.

## Preview with Docker (like Railway, with Postgres)

Needs Docker Desktop, plus PHP, Composer and Node once to install the packages:

```bash
cd site
composer install && npm install && npm run build
docker compose -f docker/docker-compose.yml up --build
```

Site: http://localhost:8080 · Admin: http://localhost:8080/admin (`admin@bigeds.local` / `bigeds-admin`). It runs the same setup as Railway's pre-deploy command, and its database starts with one order in the old site's table, to show the import. Stop it with `docker compose -f docker/docker-compose.yml down` (add `-v` to wipe the database).

## Run it locally

Needs PHP 8.3+ (with `intl`, `pdo_sqlite`, `zip`), Composer and Node 20+.

```bash
cd site
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
ADMIN_EMAIL=you@example.com ADMIN_PASSWORD=choose-one php artisan shop:install
php artisan serve        # http://localhost:8000/en and /admin
php vendor/bin/phpunit --testsuite Feature
```

More about the panel itself (visual editor, analytics, roles, adding sections) is in [site/README.md](site/README.md).

## Notes

- Name and phone number are required, email is optional. Delivery is the only option and there is no address field.
- A customer can send up to 15 preorders an hour from one connection. Invalid forms never count. A hidden spam-trap field catches bots.
- The Romanian and Russian texts are a first translation: have a native speaker read them in the Visual editor (switch the language at the top).
- Russian headings use Oswald, because the headline font (Anton) has no Cyrillic letters.
