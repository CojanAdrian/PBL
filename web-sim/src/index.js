const fs = require("fs");
const path = require("path");
const { Pool } = require("pg");
const { createApp } = require("./app");
const config = require("../config.json");

const { DATABASE_URL, ADMIN_KEY, TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID } = process.env;
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

  const app = createApp({
    pool,
    config,
    adminKey: ADMIN_KEY,
    telegram: { token: TELEGRAM_BOT_TOKEN, chatId: TELEGRAM_CHAT_ID },
  });
  app.listen(PORT, "0.0.0.0", () => console.log(`Big Ed's site listening on ${PORT}`));
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
