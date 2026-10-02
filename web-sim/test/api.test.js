const { test, before, after } = require("node:test");
const assert = require("node:assert/strict");
const fs = require("fs");
const path = require("path");
const { newDb } = require("pg-mem");
const { createApp } = require("../src/app");
const { validateOrder } = require("../src/orders");
const { createMailer, buildOrderEmail } = require("../src/email");
const config = require("../config.json");

let server, base, sent, emails;

const goodOrder = (extra = {}) => ({
  name: "Ana Popescu",
  phone: "+373 69 123 456",
  email: "",
  delivery: "pickup",
  items: [{ flavour: "original", grams: 100, qty: 2 }],
  ...extra,
});

before(async () => {
  const db = newDb();
  db.public.none(fs.readFileSync(path.join(__dirname, "..", "src", "schema.sql"), "utf8"));
  const { Pool } = db.adapters.createPg();
  sent = [];
  emails = [];
  const app = createApp({
    pool: new Pool(),
    config,
    adminKey: "secret-admin-key",
    telegram: { token: "t0ken", chatId: "42" },
    mailer: { send: async (m) => emails.push(m) },
    emailTo: "kenny@igtfreight.com",
    fetchImpl: async (url, opts) => {
      sent.push({ url, body: JSON.parse(opts.body) });
      return { ok: true, status: 200 };
    },
  });
  await new Promise((resolve) => {
    server = app.listen(0, "127.0.0.1", resolve);
  });
  base = `http://127.0.0.1:${server.address().port}`;
});

after(() => server.close());

const post = (body, headers = {}) =>
  fetch(`${base}/api/preorders`, { method: "POST", headers: { "Content-Type": "application/json", ...headers }, body: JSON.stringify(body) });

test("validateOrder prices on the server and merges duplicate lines", () => {
  const result = validateOrder(
    goodOrder({
      items: [
        { flavour: "spicy", grams: 50, qty: 1 },
        { flavour: "spicy", grams: 50, qty: 2 },
        { flavour: "garlic", grams: 150, qty: 1 },
      ],
    }),
    config
  );
  assert.equal(result.ok, true);
  assert.equal(result.order.items.length, 2);
  assert.equal(result.order.totalPacks, 4);
  assert.equal(result.order.totalGrams, 50 * 3 + 150);
  // 50 g costs 50 and 150 g costs 110 (table in config.json)
  assert.equal(result.order.totalPrice, 50 * 3 + 110);
  assert.equal(result.order.phone, "+37369123456");
});

test("prices: 50 g = 50, 100 g = 80, 150 g = 110, custom sizes follow base + per gram", () => {
  const price = (grams) => validateOrder(goodOrder({ items: [{ flavour: "bbq", grams, qty: 1 }] }), config).order.totalPrice;
  assert.equal(price(50), 50);
  assert.equal(price(100), 80);
  assert.equal(price(150), 110);
  assert.equal(price(250), 170);
  assert.equal(price(500), 320);
  assert.equal(price(1000), 620);
  assert.equal(price(30), 38);
});

test("phone is optional, but a phone or an email is needed", () => {
  const ok = (extra) => validateOrder(goodOrder(extra), config);
  assert.equal(ok({ phone: "", email: "ana@example.com" }).ok, true);
  assert.equal(ok({ phone: "+373 69 123 456", email: "" }).ok, true);
  const neither = ok({ phone: "", email: "" });
  assert.equal(neither.ok, false);
  assert.match(neither.errors.phone, /phone number or an email/);
  assert.ok(ok({ phone: "abc", email: "ana@example.com" }).errors.phone);
});

test("validateOrder accepts custom sizes only inside the allowed range and step", () => {
  const ok = (grams) => validateOrder(goodOrder({ items: [{ flavour: "bbq", grams, qty: 1 }] }), config).ok;
  assert.equal(ok(250), true);
  assert.equal(ok(30), true);
  assert.equal(ok(1000), true);
  assert.equal(ok(25), false);
  assert.equal(ok(255), false);
  assert.equal(ok(1010), false);
  assert.equal(ok(-100), false);
  assert.equal(ok(100.5), false);
});

test("validateOrder rejects bad items, contact details and dates", () => {
  const errors = (body) => validateOrder(body, config).errors || {};
  assert.ok(errors(goodOrder({ items: [] })).items);
  assert.ok(errors(goodOrder({ items: [{ flavour: "nope", grams: 100, qty: 1 }] })).items);
  assert.ok(errors(goodOrder({ items: [{ flavour: "bbq", grams: 100, qty: 0 }] })).items);
  assert.ok(errors(goodOrder({ items: [{ flavour: "bbq", grams: 100, qty: 21 }] })).items);
  assert.ok(errors(goodOrder({ name: "A" })).name);
  assert.ok(errors(goodOrder({ phone: "abc" })).phone);
  assert.ok(errors(goodOrder({ phone: "", email: "" })).phone);
  assert.ok(errors(goodOrder({ email: "not-an-email" })).email);
  assert.ok(errors(goodOrder({ delivery: "teleport" })).delivery);
  assert.ok(errors(goodOrder({ delivery: "delivery", address: "" })).address);
  assert.ok(errors(goodOrder({ date: "2001-01-01" })).date);
  assert.ok(errors(goodOrder({ date: "garbage" })).date);
  assert.deepEqual(validateOrder(goodOrder({ delivery: "delivery", address: "Str. Test 1" }), config).ok, true);
});

test("a valid preorder is saved, returns a code and pings Telegram", async () => {
  const res = await post(goodOrder({ note: "Extra crispy please" }));
  assert.equal(res.status, 201);
  const data = await res.json();
  assert.match(data.code, /^BED-[2-9A-HJ-NP-Z]{5}$/);
  assert.equal(data.totalPrice, 160); // 2 x 100 g at 80
  await new Promise((r) => setTimeout(r, 30));
  assert.equal(sent.length, 1);
  assert.match(sent[0].url, /bott0ken\/sendMessage|bot.*\/sendMessage/);
  assert.match(sent[0].body.text, new RegExp(data.code));
  assert.match(sent[0].body.text, /2 x 100 g Original/);
});

test("invalid preorders get field errors back", async () => {
  const res = await post(goodOrder({ phone: "", items: [] }));
  assert.equal(res.status, 400);
  const data = await res.json();
  assert.ok(data.errors.phone);
  assert.ok(data.errors.items);
});

test("the honeypot field gets a fake success and nothing is stored", async () => {
  const before = sent.length;
  const res = await post(goodOrder({ website: "http://spam.example" }));
  assert.equal(res.status, 201);
  const list = await (await fetch(`${base}/api/admin/preorders`, { headers: { "x-admin-key": "secret-admin-key" } })).json();
  assert.ok(!list.orders.some((o) => o.code === "BED-00000"));
  assert.equal(sent.length, before);
});

test("admin needs the key, lists orders and updates status", async () => {
  assert.equal((await fetch(`${base}/api/admin/preorders`)).status, 401);
  assert.equal((await fetch(`${base}/api/admin/preorders`, { headers: { "x-admin-key": "wrong" } })).status, 401);

  const headers = { "x-admin-key": "secret-admin-key", "Content-Type": "application/json" };
  const list = await (await fetch(`${base}/api/admin/preorders`, { headers })).json();
  assert.ok(list.orders.length >= 1);
  const order = list.orders[0];
  assert.equal(order.status, "new");
  assert.equal(order.items[0].flavour, "original");

  const patched = await fetch(`${base}/api/admin/preorders/${order.code}`, { method: "PATCH", headers, body: JSON.stringify({ status: "confirmed" }) });
  assert.equal(patched.status, 200);
  const bad = await fetch(`${base}/api/admin/preorders/${order.code}`, { method: "PATCH", headers, body: JSON.stringify({ status: "lol" }) });
  assert.equal(bad.status, 400);
  const missing = await fetch(`${base}/api/admin/preorders/BED-NOPE1`, { method: "PATCH", headers, body: JSON.stringify({ status: "new" }) });
  assert.equal(missing.status, 404);
});

test("public config is served without the readme note", async () => {
  const data = await (await fetch(`${base}/api/config`)).json();
  assert.equal(data.flavours.length, 5);
  assert.equal(data._readme, undefined);
  assert.equal((await fetch(`${base}/health`)).status, 200);
});

test("an order triggers an email to Kenny with everything in it", async () => {
  emails.length = 0;
  const res = await post(goodOrder({ phone: "", email: "ana@example.com", note: "Call before 6" }));
  assert.equal(res.status, 201);
  const { code } = await res.json();
  await new Promise((r) => setTimeout(r, 30));
  assert.equal(emails.length, 1);
  const mail = emails[0];
  assert.equal(mail.to, "kenny@igtfreight.com");
  assert.ok(mail.subject.includes(code));
  assert.ok(mail.subject.includes("160 MDL"));
  assert.match(mail.text, /2 x 100 g Original/);
  assert.match(mail.text, /Email: ana@example.com/);
  assert.doesNotMatch(mail.text, /Phone:/);
  assert.match(mail.text, /Call before 6/);
  assert.equal(mail.replyTo, "ana@example.com");
  assert.match(mail.html, new RegExp(code));
});

test("customer text is escaped in the email", () => {
  const result = validateOrder(goodOrder({ name: "<b>Eve</b>", note: "<script>x</script>" }), config);
  const mail = buildOrderEmail({ code: "BED-TEST1", order: result.order, config, adminUrl: "https://x.test/admin" });
  assert.ok(!mail.html.includes("<script>"));
  assert.ok(!mail.html.includes("<b>Eve</b>"));
  assert.ok(mail.html.includes("&lt;b&gt;Eve&lt;/b&gt;"));
});

test("a broken mail service never stops an order from being saved", async () => {
  const db = newDb();
  db.public.none(fs.readFileSync(path.join(__dirname, "..", "src", "schema.sql"), "utf8"));
  const { Pool } = db.adapters.createPg();
  const app = createApp({ pool: new Pool(), config, adminKey: "k", mailer: { send: async () => { throw new Error("provider down"); } }, emailTo: "kenny@igtfreight.com" });
  const s = await new Promise((resolve) => { const srv = app.listen(0, "127.0.0.1", () => resolve(srv)); });
  try {
    const res = await fetch(`http://127.0.0.1:${s.address().port}/api/preorders`, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(goodOrder()) });
    assert.equal(res.status, 201);
  } finally {
    s.close();
  }
});

test("mailer: Resend and Brevo get the right request, no keys means no mailer", async () => {
  const calls = [];
  const fetchImpl = async (url, opts) => { calls.push({ url, headers: opts.headers, body: JSON.parse(opts.body) }); return { ok: true, status: 200 }; };
  const msg = { to: "kenny@igtfreight.com", subject: "S", text: "T", html: "<p>H</p>", replyTo: "a@b.co" };

  await createMailer({ resendKey: "re_123", from: "Big Eds Jerky <orders@example.com>", fetchImpl }).send(msg);
  assert.equal(calls[0].url, "https://api.resend.com/emails");
  assert.equal(calls[0].headers.Authorization, "Bearer re_123");
  assert.deepEqual(calls[0].body.to, ["kenny@igtfreight.com"]);
  assert.equal(calls[0].body.reply_to, "a@b.co");

  await createMailer({ brevoKey: "xkeysib-1", from: "Big Eds Jerky <orders@example.com>", fetchImpl }).send(msg);
  assert.equal(calls[1].url, "https://api.brevo.com/v3/smtp/email");
  assert.equal(calls[1].headers["api-key"], "xkeysib-1");
  assert.equal(calls[1].body.sender.email, "orders@example.com");
  assert.equal(calls[1].body.to[0].email, "kenny@igtfreight.com");

  assert.equal(createMailer({}), null);
  assert.throws(() => createMailer({ brevoKey: "k" }), /EMAIL_FROM/);

  const failing = createMailer({ resendKey: "k", fetchImpl: async () => ({ ok: false, status: 403, text: async () => "domain not verified" }) });
  await assert.rejects(failing.send(msg), /403/);
});

test("invalid orders never use up the order limit, saved orders do (15 an hour)", async () => {
  for (let i = 0; i < 40; i++) {
    const res = await post(goodOrder({ items: [] }));
    assert.equal(res.status, 400);
  }
  let saved = 0;
  let last;
  for (let i = 0; i < 25; i++) {
    last = await post(goodOrder());
    if (last.status === 201) saved++;
    else break;
  }
  assert.equal(last.status, 429);
  assert.ok(saved >= 10 && saved <= 15, `saved ${saved}`);
});

test("using the admin page never locks you out, only wrong keys do", async () => {
  const good = { headers: { "x-admin-key": "secret-admin-key" } };
  for (let i = 0; i < 40; i++) assert.equal((await fetch(`${base}/api/admin/preorders`, good)).status, 200);
  for (let i = 0; i < 10; i++) assert.equal((await fetch(`${base}/api/admin/preorders`, { headers: { "x-admin-key": "nope" } })).status, 401);
  assert.equal((await fetch(`${base}/api/admin/preorders`, { headers: { "x-admin-key": "nope" } })).status, 429);
});

test("the test-email button reports success, and the exact error when the provider refuses", async () => {
  const mk = async (mailer) => {
    const db = newDb();
    db.public.none(fs.readFileSync(path.join(__dirname, "..", "src", "schema.sql"), "utf8"));
    const { Pool } = db.adapters.createPg();
    const app = createApp({ pool: new Pool(), config, adminKey: "k", mailer, emailTo: mailer ? "kenny@igtfreight.com" : undefined });
    const srv = await new Promise((resolve) => { const x = app.listen(0, "127.0.0.1", () => resolve(x)); });
    return { srv, url: `http://127.0.0.1:${srv.address().port}` };
  };
  const hit = (url, path_, method = "POST") => fetch(url + path_, { method, headers: { "x-admin-key": "k", "Content-Type": "application/json" }, body: method === "POST" ? "{}" : undefined });

  const sentMail = [];
  let ok = await mk({ provider: "brevo", send: async (m) => sentMail.push(m) });
  try {
    assert.equal((await fetch(ok.url + "/api/admin/email-test", { method: "POST" })).status, 401);
    const res = await hit(ok.url, "/api/admin/email-test");
    assert.equal(res.status, 200);
    assert.equal(sentMail[0].to, "kenny@igtfreight.com");
    const list = await (await hit(ok.url, "/api/admin/preorders", "GET")).json();
    assert.deepEqual({ enabled: list.email.enabled, provider: list.email.provider, to: list.email.to, lastError: list.email.lastError }, { enabled: true, provider: "brevo", to: "kenny@igtfreight.com", lastError: null });
  } finally { ok.srv.close(); }

  const bad = await mk({ provider: "resend", send: async () => { throw new Error("Resend responded 403 domain not verified"); } });
  try {
    const res = await hit(bad.url, "/api/admin/email-test");
    assert.equal(res.status, 502);
    assert.match((await res.json()).error, /403 domain not verified/);
    const list = await (await hit(bad.url, "/api/admin/preorders", "GET")).json();
    assert.match(list.email.lastError, /domain not verified/);
  } finally { bad.srv.close(); }

  const off = await mk(null);
  try {
    const res = await hit(off.url, "/api/admin/email-test");
    assert.equal(res.status, 400);
    assert.match((await res.json()).error, /Email is OFF/);
    const list = await (await hit(off.url, "/api/admin/preorders", "GET")).json();
    assert.equal(list.email.enabled, false);
  } finally { off.srv.close(); }
});

test("without a database preorders say so instead of pretending", async () => {
  const app = createApp({ pool: null, config, adminKey: "" });
  const s = await new Promise((resolve) => {
    const srv = app.listen(0, "127.0.0.1", () => resolve(srv));
  });
  try {
    const res = await fetch(`http://127.0.0.1:${s.address().port}/api/preorders`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(goodOrder()),
    });
    assert.equal(res.status, 503);
    assert.equal((await fetch(`http://127.0.0.1:${s.address().port}/api/admin/preorders`)).status, 404);
  } finally {
    s.close();
  }
});
