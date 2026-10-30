const { test, before, after } = require("node:test");
const assert = require("node:assert/strict");
const fs = require("fs");
const path = require("path");
const { newDb } = require("pg-mem");
const { createApp } = require("../src/app");
const { validateOrder } = require("../src/orders");
const { createMailer, buildOrderEmail } = require("../src/email");
const { createSheetSync } = require("../src/sheets");
const config = require("../config.json");

let server, base, sent, emails, sheetPushes, sheetDeletes;

const goodOrder = (extra = {}) => ({
  name: "Ana Popescu",
  phone: "+373 69 123 456",
  email: "",
  delivery: "delivery",
  items: [{ flavour: "beef-texas-bbq", grams: 100, qty: 2 }],
  ...extra,
});

before(async () => {
  const db = newDb();
  db.public.none(fs.readFileSync(path.join(__dirname, "..", "src", "schema.sql"), "utf8"));
  const { Pool } = db.adapters.createPg();
  sent = [];
  emails = [];
  sheetPushes = [];
  sheetDeletes = [];
  const app = createApp({
    pool: new Pool(),
    config,
    adminKey: "secret-admin-key",
    telegram: { token: "t0ken", chatId: "42" },
    mailer: { send: async (m) => emails.push(m) },
    emailTo: "kenny@igtfreight.com",
    sheet: { push: async (orders) => sheetPushes.push(orders), remove: async (codes) => sheetDeletes.push(...codes) },
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
        { flavour: "chicken-honey-sriracha", grams: 100, qty: 1 },
        { flavour: "chicken-honey-sriracha", grams: 100, qty: 2 },
        { flavour: "beef-garlic-teriyaki", grams: 100, qty: 1 },
      ],
    }),
    config
  );
  assert.equal(result.ok, true);
  assert.equal(result.order.items.length, 2);
  assert.equal(result.order.totalPacks, 4);
  assert.equal(result.order.totalGrams, 400);
  // chicken 70 a pack, beef 130 a pack (config.json)
  assert.equal(result.order.totalPrice, 70 * 3 + 130);
  assert.equal(result.order.phone, "+37369123456");
});

test("prices: beef 130 and chicken 70 per 100 g pack, the taster box 285", () => {
  const price = (flavour, grams) => validateOrder(goodOrder({ items: [{ flavour, grams, qty: 1 }] }), config).order.totalPrice;
  assert.equal(price("beef-texas-bbq", 100), 130);
  assert.equal(price("beef-honey-sriracha", 100), 130);
  assert.equal(price("chicken-texas-bbq", 100), 70);
  assert.equal(price("chicken-garlic-teriyaki", 100), 70);
  assert.equal(price("taster-box", 300), 285);
  // a box always weighs what config.json says
  assert.equal(validateOrder(goodOrder({ items: [{ flavour: "taster-box", grams: 100, qty: 1 }] }), config).ok, false);
});

test("name and phone are required, email is optional", () => {
  const ok = (extra) => validateOrder(goodOrder(extra), config);
  assert.equal(ok({ phone: "+373 69 123 456", email: "" }).ok, true);
  assert.equal(ok({ email: "ana@example.com" }).ok, true);
  const noPhone = ok({ phone: "", email: "ana@example.com" });
  assert.equal(noPhone.ok, false);
  assert.match(noPhone.errors.phone, /phone number/);
  assert.ok(ok({ phone: "abc" }).errors.phone);
  assert.ok(ok({ name: "" }).errors.name);
});

test("delivery is the only option for now, with no address", () => {
  const { order } = validateOrder(goodOrder({ delivery: "pickup", address: "Str. Test 1" }), config);
  assert.equal(order.delivery, "delivery");
  assert.equal(order.address, "");
  assert.equal(validateOrder(goodOrder({ delivery: undefined }), config).ok, true);
});

test("custom sizes are off for now; switched on, they follow the 100 g price", () => {
  const ok = (grams, cfg = config) => validateOrder(goodOrder({ items: [{ flavour: "chicken-texas-bbq", grams, qty: 1 }] }), cfg).ok;
  assert.equal(ok(100), true);
  assert.equal(ok(50), false);
  assert.equal(ok(250), false);

  const on = { ...config, custom: { ...config.custom, enabled: true } };
  assert.equal(ok(250, on), true);
  assert.equal(ok(30, on), true);
  assert.equal(ok(1000, on), true);
  assert.equal(ok(25, on), false);
  assert.equal(ok(255, on), false);
  assert.equal(ok(1010, on), false);
  assert.equal(ok(-100, on), false);
  assert.equal(ok(100.5, on), false);
  const price = (flavour, grams) => validateOrder(goodOrder({ items: [{ flavour, grams, qty: 1 }] }), on).order.totalPrice;
  assert.equal(price("chicken-texas-bbq", 250), 175);
  assert.equal(price("beef-texas-bbq", 250), 325);
});

test("validateOrder rejects bad items, contact details and dates", () => {
  const errors = (body) => validateOrder(body, config).errors || {};
  assert.ok(errors(goodOrder({ items: [] })).items);
  assert.ok(errors(goodOrder({ items: [{ flavour: "nope", grams: 100, qty: 1 }] })).items);
  assert.ok(errors(goodOrder({ items: [{ flavour: "chicken-texas-bbq", grams: 100, qty: 0 }] })).items);
  assert.ok(errors(goodOrder({ items: [{ flavour: "chicken-texas-bbq", grams: 100, qty: 21 }] })).items);
  assert.ok(errors(goodOrder({ name: "A" })).name);
  assert.ok(errors(goodOrder({ phone: "abc" })).phone);
  assert.ok(errors(goodOrder({ phone: "", email: "" })).phone);
  assert.ok(errors(goodOrder({ email: "not-an-email" })).email);
  assert.ok(errors(goodOrder({ date: "2001-01-01" })).date);
  assert.ok(errors(goodOrder({ date: "garbage" })).date);
});

test("a valid preorder is saved, returns a code and pings Telegram", async () => {
  const res = await post(goodOrder({ note: "Extra crispy please" }));
  assert.equal(res.status, 201);
  const data = await res.json();
  assert.match(data.code, /^BED-[2-9A-HJ-NP-Z]{5}$/);
  assert.equal(data.totalPrice, 260); // 2 x 100 g beef at 130
  await new Promise((r) => setTimeout(r, 30));
  assert.equal(sent.length, 1);
  assert.match(sent[0].url, /bott0ken\/sendMessage|bot.*\/sendMessage/);
  assert.match(sent[0].body.text, new RegExp(data.code));
  assert.match(sent[0].body.text, /2 x 100 g Texas BBQ Beef/);
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

test("admin needs the key and lists orders (read-only)", async () => {
  assert.equal((await fetch(`${base}/api/admin/preorders`)).status, 401);
  assert.equal((await fetch(`${base}/api/admin/preorders`, { headers: { "x-admin-key": "wrong" } })).status, 401);

  const headers = { "x-admin-key": "secret-admin-key", "Content-Type": "application/json" };
  const list = await (await fetch(`${base}/api/admin/preorders`, { headers })).json();
  assert.ok(list.orders.length >= 1);
  const order = list.orders[0];
  assert.equal(order.status, "new");
  assert.equal(order.items[0].flavour, "beef-texas-bbq");

  // status is kept in the Google Sheet, so there is no way to change it here
  const patched = await fetch(`${base}/api/admin/preorders/${order.code}`, { method: "PATCH", headers, body: JSON.stringify({ status: "confirmed" }) });
  assert.equal(patched.status, 404);
});

test("public config is served without the readme note", async () => {
  const data = await (await fetch(`${base}/api/config`)).json();
  assert.equal(data.flavours.length, 6);
  assert.deepEqual(data.meats.map((m) => m.id), ["beef", "chicken"]);
  for (const f of data.flavours) assert.ok(data.meats.some((m) => m.id === f.meat), `${f.id} has a known meat`);
  assert.equal(data._readme, undefined);
  assert.equal((await fetch(`${base}/health`)).status, 200);
});

test("an order triggers an email to Kenny with everything in it", async () => {
  emails.length = 0;
  const res = await post(goodOrder({ email: "ana@example.com", note: "Call before 6" }));
  assert.equal(res.status, 201);
  const { code } = await res.json();
  await new Promise((r) => setTimeout(r, 30));
  assert.equal(emails.length, 1);
  const mail = emails[0];
  assert.equal(mail.to, "kenny@igtfreight.com");
  assert.ok(mail.subject.includes(code));
  assert.ok(mail.subject.includes("260 lei"));
  assert.match(mail.text, /2 x 100 g Texas BBQ Beef/);
  assert.match(mail.text, /Email: ana@example.com/);
  assert.match(mail.text, /Phone: \+37369123456/);
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

test("every new order is added to the Google Sheet straight away", async () => {
  sheetPushes.length = 0;
  const res = await post(goodOrder({ items: [{ flavour: "taster-box", grams: 300, qty: 1 }, { flavour: "chicken-texas-bbq", grams: 100, qty: 2 }] }));
  assert.equal(res.status, 201);
  const { code } = await res.json();
  await new Promise((r) => setTimeout(r, 30));
  assert.equal(sheetPushes.length, 1);
  const [created] = sheetPushes[0];
  assert.equal(created.code, code);
  assert.equal(created.status, "new");
  assert.equal(created.totalPrice, 285 + 140);

  // "Sync all orders" sends everything; the sheet adds only the missing ones
  const headers = { "x-admin-key": "secret-admin-key", "Content-Type": "application/json" };
  const all = await fetch(`${base}/api/admin/sheet-sync`, { method: "POST", headers, body: "{}" });
  assert.equal(all.status, 200);
  const { count } = await all.json();
  assert.equal(sheetPushes.at(-1).length, count);
  assert.ok(count >= 2);
});

test("sheet rows are ready to write, and a bad token or deployment gives a clear error", async () => {
  let request;
  const ok = createSheetSync({
    url: "https://script.test/exec",
    token: "tok",
    config,
    fetchImpl: async (url, opts) => {
      request = { url, body: JSON.parse(opts.body) };
      return { ok: true, status: 200, text: async () => JSON.stringify({ ok: true, count: 1 }) };
    },
  });
  const order = {
    code: "BED-TEST1", createdAt: "2026-10-30T10:00:00.000Z", status: "confirmed", name: "Ana", phone: "+37369123456", email: "",
    delivery: "delivery", address: "", date: "", note: "", currency: "lei", totalGrams: 500, totalPrice: 425,
    items: [{ flavour: "taster-box", grams: 300, qty: 1, price: 285 }, { flavour: "chicken-texas-bbq", grams: 100, qty: 2, price: 70 }],
  };
  await ok.push([order]);
  assert.equal(request.body.token, "tok");
  const [row] = request.body.orders;
  assert.equal(row.status, "Confirmed");
  assert.equal(row.items, "1 x Taster box\n2 x 100 g Texas BBQ Chicken");
  assert.equal(row.packs, 3);
  assert.equal(row.total, 425);

  const wrongToken = createSheetSync({ url: "https://script.test/exec", config, fetchImpl: async () => ({ ok: true, status: 200, text: async () => JSON.stringify({ ok: false, error: "Wrong token." }) }) });
  await assert.rejects(wrongToken.push([order]), /Wrong token/);
  const loginPage = createSheetSync({ url: "https://script.test/exec", config, fetchImpl: async () => ({ ok: true, status: 200, text: async () => "<html>Sign in</html>" }) });
  await assert.rejects(loginPage.push([order]), /Anyone/);
  assert.equal(createSheetSync({ config }), null);
});

test("deleting an order on the admin page removes it here and from the sheet", async () => {
  const res = await post(goodOrder());
  const { code } = await res.json();
  const headers = { "x-admin-key": "secret-admin-key" };
  assert.equal((await fetch(`${base}/api/admin/preorders/${code}`, { method: "DELETE" })).status, 401);

  const del = await fetch(`${base}/api/admin/preorders/${code}`, { method: "DELETE", headers });
  assert.equal(del.status, 200);
  assert.equal((await del.json()).sheet, "removed");
  assert.ok(sheetDeletes.includes(code));
  const list = await (await fetch(`${base}/api/admin/preorders`, { headers })).json();
  assert.ok(!list.orders.some((o) => o.code === code));
  assert.equal((await fetch(`${base}/api/admin/preorders/${code}`, { method: "DELETE", headers })).status, 404);
});
