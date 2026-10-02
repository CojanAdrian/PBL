# Big Ed's Jerky

Landing page and preorder system for Big Ed's Jerky, made in Moldova.

Everything lives in [web-sim/](web-sim/): a small Node server that serves the website and takes preorders.

## What's where

| Path | What it is |
| --- | --- |
| `web-sim/config.json` | **Flavours, sizes and prices.** Edit this to change the menu. |
| `web-sim/public/` | The website (HTML, CSS, JS, images, fonts) |
| `web-sim/src/` | The server: preorder API, Ed's admin API, database setup |
| `web-sim/test/` | Server tests (`npm test`) |

## Change the menu

Open `web-sim/config.json`:

- `pricePer100g`: the price of 100 g. Pack prices are worked out from it. **The current number (120 MDL) is a placeholder, set the real one.**
- `sizes`: the preset pack sizes (50, 100, 150 g).
- `flavours`: name, heat level (1 to 5), colours, and the text shown on the site.
- `contact`: phone, email, Instagram and Telegram shown in the footer. Empty ones are hidden.
- `paymentNote`: the "how do I pay" line shown while ordering.

The server checks and prices every preorder from this same file, so what people see is what they are charged.

## Deploy on Railway

1. Service **Root Directory**: `/web-sim`. Railway runs `npm start`.
2. Add a **PostgreSQL** database to the project.
3. On the website service, set these **variables**:

   | Variable | Value |
   | --- | --- |
   | `DATABASE_URL` | `${{Postgres.DATABASE_URL}}` (reference to the Postgres service) |
   | `ADMIN_KEY` | A long secret password for Ed's admin page |
   | `TELEGRAM_BOT_TOKEN` | *(optional)* a bot token from @BotFather |
   | `TELEGRAM_CHAT_ID` | *(optional)* the chat to notify, so Ed gets a message for every preorder |

4. **Settings → Networking → Generate Domain**.

The table is created automatically on the first start. Without `DATABASE_URL` the site still works, but preorders are switched off.

## Ed's preorder list

Open `/admin` on the site and enter the `ADMIN_KEY`. Ed sees every preorder, can mark them New / Confirmed / Done / Cancelled, and gets a "to make" total per flavour.

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

- The photos are small (512 px) crops of the original product shots, upscaled. Sharper pictures of the pouch and the jerky would make the site even better: drop them into `web-sim/public/img/` using the same file names.
- Preorders are limited to 6 per connection every 10 minutes, and there is a hidden spam trap field.
