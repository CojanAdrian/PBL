// Express app factory. Everything it depends on is passed in, so tests can run
// it against an in-memory database.
const crypto = require("crypto");
const path = require("path");
const express = require("express");
const compression = require("compression");
const { validateOrder, makeCode, describeOrder } = require("./orders");
const { buildOrderEmail } = require("./email");

const STATUSES = ["new", "confirmed", "fulfilled", "cancelled"];
const PUBLIC_DIR = path.join(__dirname, "..", "public");

// Tiny in-memory rate limiter. `allowed` only looks; `hit` records one event.
// Callers decide what counts, so ordinary use is never penalised.
function createLimiter(max, windowMs) {
  const hits = new Map();
  const recent = (key) => (hits.get(key) || []).filter((t) => Date.now() - t < windowMs);
  return {
    allowed: (key) => recent(key).length < max,
    hit(key) {
      const list = recent(key);
      list.push(Date.now());
      hits.set(key, list);
      if (hits.size > 5000) for (const [k, v] of hits) if (!recent(k).length) hits.delete(k);
    },
    reset: (key) => hits.delete(key),
  };
}

function sameSecret(a, b) {
  const ha = crypto.createHash("sha256").update(String(a)).digest();
  const hb = crypto.createHash("sha256").update(String(b)).digest();
  return crypto.timingSafeEqual(ha, hb);
}

function createApp({ pool, config, adminKey, telegram, mailer, emailTo, fetchImpl = fetch, randomInt = crypto.randomInt }) {
  const app = express();
  app.set("trust proxy", 1);
  app.disable("x-powered-by");
  app.use(compression());

  app.use((_req, res, next) => {
    res.set({
      "X-Content-Type-Options": "nosniff",
      "Referrer-Policy": "strict-origin-when-cross-origin",
      "X-Frame-Options": "DENY",
      "Permissions-Policy": "camera=(), microphone=(), geolocation=()",
      "Content-Security-Policy":
        "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; " +
        "connect-src 'self'; font-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'",
    });
    next();
  });

  // Only orders that were actually saved count (15 an hour per connection), and
  // only WRONG admin keys count (10 per 15 minutes). Normal use never hits these.
  const orderLimiter = createLimiter(15, 60 * 60 * 1000);
  const adminLimiter = createLimiter(10, 15 * 60 * 1000);
  const publicConfig = (({ _readme, ...rest }) => rest)(config);

  app.get("/health", (_req, res) => res.json({ ok: true, db: Boolean(pool) }));

  app.get("/api/config", (_req, res) => {
    res.set("Cache-Control", "no-cache");
    res.json(publicConfig);
  });

  // ---- preorders ----
  app.post("/api/preorders", express.json({ limit: "20kb" }), async (req, res, next) => {
    try {
      if (!orderLimiter.allowed(req.ip)) {
        return res.status(429).json({ error: "You've sent a lot of preorders from this connection. Please try again later." });
      }
      // Hidden field only bots fill in. Pretend it worked, store nothing.
      if (req.body && req.body.website) return res.status(201).json({ code: "BED-00000", ok: true });

      const result = validateOrder(req.body, config);
      if (!result.ok) return res.status(400).json({ error: "Please fix the highlighted fields.", errors: result.errors });
      if (!pool) {
        console.error("Preorder rejected: DATABASE_URL is not set, nowhere to save it.");
        return res.status(503).json({ error: "Preorders are temporarily unavailable. Please try again soon." });
      }

      const { order } = result;
      let code;
      for (let attempt = 0; attempt < 5 && !code; attempt++) {
        const candidate = makeCode(randomInt);
        try {
          await pool.query(
            `INSERT INTO preorders (code, name, phone, email, delivery, address, wanted_date, note, items,
                                    total_grams, total_price, currency, status)
             VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,'new')`,
            [candidate, order.name, order.phone, order.email, order.delivery, order.address, order.date,
             order.note, JSON.stringify(order.items), order.totalGrams, order.totalPrice, order.currency]
          );
          code = candidate;
        } catch (err) {
          if (err.code !== "23505") throw err;
        }
      }
      if (!code) throw new Error("Could not generate a unique order code");

      orderLimiter.hit(req.ip);
      notifyTelegram(code, order).catch((err) => console.error("Telegram notify failed:", err.message));
      notifyEmail(code, order, `${req.protocol}://${req.get("host")}/admin`).catch((err) => console.error("Email notify failed:", err.message));
      res.status(201).json({ ok: true, code, totalPrice: order.totalPrice, totalGrams: order.totalGrams, currency: order.currency });
    } catch (err) {
      next(err);
    }
  });

  // What the admin page shows about email: is it on, and did the last one work?
  const emailState = { lastError: null, lastOkAt: null };
  const emailInfo = () => ({
    enabled: Boolean(mailer && emailTo),
    provider: mailer ? mailer.provider : null,
    to: emailTo || null,
    lastError: emailState.lastError,
    lastOkAt: emailState.lastOkAt,
  });

  async function sendMail(message) {
    if (!mailer || !emailTo) throw new Error("Email is not set up on the server.");
    try {
      await mailer.send({ to: emailTo, ...message });
      emailState.lastError = null;
      emailState.lastOkAt = new Date().toISOString();
    } catch (err) {
      emailState.lastError = err.message;
      throw err;
    }
  }

  async function notifyEmail(code, order, adminUrl) {
    if (!mailer || !emailTo) return;
    await sendMail(buildOrderEmail({ code, order, config, adminUrl }));
  }

  async function notifyTelegram(code, order) {
    if (!telegram || !telegram.token || !telegram.chatId) return;
    const lines = [
      `New preorder ${code}`,
      ...describeOrder(order, config),
      `Total: ${order.totalPrice} ${order.currency} (${order.totalGrams} g)`,
      [order.name, order.phone, order.email].filter(Boolean).join(" - "),
      order.delivery === "delivery" ? `Delivery: ${order.address}` : "Pickup",
    ];
    if (order.date) lines.push(`Wanted: ${order.date}`);
    if (order.note) lines.push(`Note: ${order.note}`);
    const res = await fetchImpl(`https://api.telegram.org/bot${telegram.token}/sendMessage`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ chat_id: telegram.chatId, text: lines.join("\n") }),
      signal: AbortSignal.timeout(5000),
    });
    if (!res.ok) throw new Error(`Telegram responded ${res.status}`);
  }

  // ---- admin (Ed's list of preorders) ----
  function requireAdmin(req, res, next) {
    if (!adminKey) return res.status(404).json({ error: "Not found." });
    if (!adminLimiter.allowed(req.ip)) return res.status(429).json({ error: "Too many wrong keys. Try again in a few minutes." });
    if (!sameSecret(req.get("x-admin-key") || "", adminKey)) {
      adminLimiter.hit(req.ip);
      return res.status(401).json({ error: "Wrong key." });
    }
    adminLimiter.reset(req.ip);
    next();
  }

  const rowToOrder = (r) => ({
    code: r.code,
    name: r.name,
    phone: r.phone,
    email: r.email,
    delivery: r.delivery,
    address: r.address,
    date: r.wanted_date,
    note: r.note,
    items: JSON.parse(r.items),
    totalGrams: r.total_grams,
    totalPrice: r.total_price,
    currency: r.currency,
    status: r.status,
    createdAt: new Date(r.created_at).toISOString(),
  });

  app.get("/api/admin/preorders", requireAdmin, async (_req, res, next) => {
    try {
      if (!pool) return res.status(503).json({ error: "No database." });
      const { rows } = await pool.query("SELECT * FROM preorders ORDER BY created_at DESC LIMIT 2000");
      res.set("Cache-Control", "no-store");
      res.json({
        orders: rows.map(rowToOrder),
        flavours: config.flavours.map(({ id, name }) => ({ id, name })),
        sizes: config.sizes,
        currency: config.currency,
        email: emailInfo(),
      });
    } catch (err) {
      next(err);
    }
  });

  // "Send test email" button: tells you straight away whether email works, and if not, why.
  app.post("/api/admin/email-test", requireAdmin, async (_req, res) => {
    if (!mailer || !emailTo) {
      return res.status(400).json({ error: "Email is OFF. Set BREVO_API_KEY (or RESEND_API_KEY) and EMAIL_FROM on the server." });
    }
    try {
      await sendMail({
        subject: "Test email from Big Ed's Jerky",
        text: "If you can read this, preorder emails are working. New preorders will arrive in this inbox.",
        html: "<p style=\"font-family:Arial,sans-serif;font-size:16px\">If you can read this, <strong>preorder emails are working</strong>. New preorders will arrive in this inbox.</p>",
      });
      res.json({ ok: true, to: emailTo });
    } catch (err) {
      res.status(502).json({ error: err.message });
    }
  });

  app.patch("/api/admin/preorders/:code", requireAdmin, express.json({ limit: "2kb" }), async (req, res, next) => {
    try {
      if (!STATUSES.includes(req.body?.status)) return res.status(400).json({ error: "Invalid status." });
      if (!pool) return res.status(503).json({ error: "No database." });
      const result = await pool.query("UPDATE preorders SET status = $1 WHERE code = $2", [req.body.status, req.params.code]);
      if (result.rowCount === 0) return res.status(404).json({ error: "Order not found." });
      res.json({ ok: true });
    } catch (err) {
      next(err);
    }
  });

  app.get("/admin", (_req, res) => {
    res.set("X-Robots-Tag", "noindex, nofollow");
    res.sendFile(path.join(PUBLIC_DIR, "admin.html"));
  });

  // ---- the website ----
  app.use(
    express.static(PUBLIC_DIR, {
      extensions: ["html"],
      setHeaders(res, file) {
        if (/[\\/](img|fonts|vendor)[\\/]/.test(file)) res.set("Cache-Control", "public, max-age=31536000, immutable");
        else res.set("Cache-Control", "no-cache");
      },
    })
  );

  app.use((_req, res) => res.status(404).type("text").send("Not found"));

  app.use((err, _req, res, _next) => {
    if (err.type === "entity.parse.failed") return res.status(400).json({ error: "Invalid request." });
    if (err.type === "entity.too.large") return res.status(413).json({ error: "Request too large." });
    console.error(err);
    res.status(500).json({ error: "Something went wrong. Please try again." });
  });

  return app;
}

module.exports = { createApp, createLimiter };
