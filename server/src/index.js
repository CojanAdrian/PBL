const fs = require("fs");
const path = require("path");
const { Pool } = require("pg");
const { createApp } = require("./app");

const { DATABASE_URL, JWT_SECRET, STAFF_INVITE_CODE } = process.env;
// Railway mounts a Volume at RAILWAY_VOLUME_MOUNT_PATH; fall back to ./uploads locally.
const UPLOAD_DIR = process.env.UPLOAD_DIR || process.env.RAILWAY_VOLUME_MOUNT_PATH || path.join(__dirname, "..", "uploads");
const PORT = Number(process.env.PORT) || 3000;

if (!DATABASE_URL) throw new Error("DATABASE_URL is not set");
if (!JWT_SECRET || JWT_SECRET.length < 16) throw new Error("JWT_SECRET must be set (16+ characters)");

// Railway's internal Postgres URL needs no SSL; the public proxy URL does.
const noSsl = /railway\.internal|localhost|127\.0\.0\.1/.test(DATABASE_URL);
const pool = new Pool({ connectionString: DATABASE_URL, ssl: noSsl ? false : { rejectUnauthorized: false } });

async function main() {
  await pool.query(fs.readFileSync(path.join(__dirname, "schema.sql"), "utf8"));
  const app = createApp({ pool, jwtSecret: JWT_SECRET, staffCode: STAFF_INVITE_CODE, uploadDir: UPLOAD_DIR });
  app.listen(PORT, "0.0.0.0", () => console.log(`RoadFix API listening on ${PORT}`));
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
