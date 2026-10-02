const fs = require("fs");
const path = require("path");
const { Pool } = require("pg");
const { createApp } = require("./app");
const { createMailer } = require("./email");
const config = require("../config.json");

const { DATABASE_URL, ADMIN_KEY, TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID, RESEND_API_KEY, BREVO_API_KEY, EMAIL_FROM } = process.env;
const ORDER_EMAIL_TO = process.env.ORDER_EMAIL_TO || "kenny@igtfreight.com";
const PORT = Number(process.env.PORT) || 3000;

async function main() {
  let pool = null;
  if (DATABASE_URL) {
    // Railway's internal Postgres URL needs no SSL; the public proxy URL does.
    const noSsl = /railway\.internal|localhost|127\.0\.0\.1/.test(DATABASE_URL);
    pool = new Pool({ connectionString: DATABASE_URL, ssl: noSsl ? false : { rejectUnauthorized: false } });
    await pool.query(fs.readFileSync(path.join(__dirname, "schema.sql"), "utf8"));
  } else {
    console.warn("DATABASE_URL is not set: the site works, but preorders are disabled.");
  }
  if (!ADMIN_KEY) console.warn("ADMIN_KEY is not set: the /admin page is disabled.");

  let mailer = null;
  try {
    mailer = createMailer({ resendKey: RESEND_API_KEY, brevoKey: BREVO_API_KEY, from: EMAIL_FROM });
  } catch (err) {
    console.error(`Email is OFF: ${err.message}`);
  }
  if (mailer) console.log(`Order emails go to ${ORDER_EMAIL_TO} via ${mailer.provider}.`);
  else console.warn("No email provider configured (set RESEND_API_KEY or BREVO_API_KEY): preorder emails are off.");

  const app = createApp({
    pool,
    config,
    adminKey: ADMIN_KEY,
    telegram: { token: TELEGRAM_BOT_TOKEN, chatId: TELEGRAM_CHAT_ID },
    mailer,
    emailTo: ORDER_EMAIL_TO,
  });
  app.listen(PORT, "0.0.0.0", () => console.log(`Big Ed's site listening on ${PORT}`));
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
