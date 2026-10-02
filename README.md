# Big Ed's Jerky

Landing page and preorder system for Big Ed's Jerky, premium beef jerky made in Moldova.

Everything lives in [web-sim/](web-sim/): a small Node server that serves the website, takes preorders, and emails them.

## What's where

| Path | What it is |
| --- | --- |
| `web-sim/config.json` | **Flavours, sizes and prices.** Edit this to change the menu. |
| `web-sim/public/` | The website (HTML, CSS, JS, images, fonts) |
| `web-sim/src/` | The server: preorder API, admin API, email, database setup |
| `web-sim/test/` | Server tests (`npm test`) |

## Prices

Set in `web-sim/config.json`:

| Pack | Price |
| --- | --- |
| 50 g | 50 MDL |
| 100 g | 80 MDL |
| 150 g | 110 MDL |
| Custom size | 20 + 0.6 per gram (so 250 g = 170, 500 g = 320, 1000 g = 620) |

- `prices`: the price of each preset size. Change these freely.
- `customPrice`: `base` and `perGram` for custom sizes. The defaults line up exactly with the three presets above.
- `currency`: shown next to every price. It is `MDL`; change it to `lei` if you prefer.

The server checks and prices every preorder from this same file, so what people see is what they are charged.

## Other things in `config.json`

- `flavours`: name, heat level (1 to 5), colours, and the text shown on the site.
- `contact`: phone, email, Instagram and Telegram shown in the footer. Empty ones are hidden.
- `paymentNote`: the "how do I pay" line shown while ordering.

## Deploy on Railway

1. Service **Root Directory**: `/web-sim`. Railway runs `npm start`.
2. Add a **PostgreSQL** database to the project.
3. On the website service, set these **variables**:

   | Variable | Value |
   | --- | --- |
   | `DATABASE_URL` | `${{Postgres.DATABASE_URL}}` (reference to the Postgres service) |
   | `ADMIN_KEY` | A long secret password for Ed's admin page |
   | `RESEND_API_KEY` **or** `BREVO_API_KEY` | For preorder emails (see below) |
   | `EMAIL_FROM` | The sender, e.g. `Big Ed's Jerky <orders@yourdomain.com>` |
   | `ORDER_EMAIL_TO` | *(optional)* who gets the emails. Defaults to `kenny@igtfreight.com` |
   | `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID` | *(optional)* a Telegram message for every preorder |

4. **Settings → Networking → Generate Domain**.

The table is created automatically on the first start. Without `DATABASE_URL` the site works, but preorders are switched off.

## Preorder emails

Every new preorder is emailed to `kenny@igtfreight.com` (change it with `ORDER_EMAIL_TO`). The email lists the items, prices, total, the customer's details and a button to open the admin page. Replying goes to the customer if they left an email.

Railway blocks normal email (SMTP) on its cheaper plans, so the server uses an email service's web API instead. Pick **one**:

**Brevo** (free, 300 emails a day, no domain needed)
1. Create an account at brevo.com.
2. Go to *Senders, Domains & Dedicated IPs → Senders* and add a sender email address you can open (for example `orders@yourdomain.com`, or any mailbox you own). Click the verification link they send.
3. Go to *SMTP & API → API keys* and create a key.
4. In Railway set `BREVO_API_KEY` to the key and `EMAIL_FROM` to `Big Ed's Jerky <that verified address>`.

**Resend** (free, 100 emails a day)
1. Create an account at resend.com and add and verify your own domain (it needs a few DNS records). Without a verified domain Resend only delivers to the email you signed up with.
2. Create an API key.
3. In Railway set `RESEND_API_KEY` and `EMAIL_FROM` (an address on your verified domain).

**Check that it works:** open `/admin`. At the top of the Orders tab a box says whether email is on or off. Press **Send test email**: it sends a message to `ORDER_EMAIL_TO` right away and shows the exact reason if the email service refuses it (wrong key, sender not verified, and so on).

If an email fails, the preorder is still saved, the problem is shown in that box, and it is written to the Railway logs.

## Ed's preorder list

Open `/admin` on the site and enter the `ADMIN_KEY`.

- **Orders**: every preorder. Change its status (New, Confirmed, Done, Cancelled) with the dropdown. There is a "to make" total per flavour.
- **Stats**: total value, still to do, done, cancelled, average order, a table by status, flavour and pack-size breakdowns, pickup vs delivery, and the last 14 days. Switch between all time, 30 days and 7 days.

Only **wrong** keys count towards the lockout (10 wrong keys in 15 minutes). Using the page normally never locks you out.

## Telegram notifications (optional)

1. In Telegram, talk to **@BotFather**, send `/newbot`, and copy the token.
2. Send any message to your new bot, then open `https://api.telegram.org/bot<TOKEN>/getUpdates` and copy the `chat` `id`.
3. Put both in the Railway variables above.

## Run it locally

```
cd web-sim
npm install
npm start          # http://localhost:3000, preorders need DATABASE_URL
npm test           # runs against an in-memory database
```

## Notes

- Customers can leave out their phone number, but they must give a phone number **or** an email so Ed can reach them.
- The photos are small (512 px) crops of the original product shots, upscaled. Sharper pictures of the pouch and the jerky would make the site even better: drop them into `web-sim/public/img/` using the same file names.
- A customer can send up to 15 preorders an hour from one connection. Invalid forms never count. A hidden spam-trap field catches bots.
