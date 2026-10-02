const { test, before, after } = require("node:test");
const assert = require("node:assert/strict");
const fs = require("fs");
const path = require("path");
const { newDb } = require("pg-mem");
const { createApp } = require("../src/app");
const { validateOrder } = require("../src/orders");
const config = require("../config.json");

let server, base, sent;

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
  const app = createApp({
    pool: new Pool(),
    config,
    adminKey: "secret-admin-key",
    telegram: { token: "t0ken", chatId: "42" },
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
  // 50 g = 60, 150 g = 180 at 120 per 100 g
  assert.equal(result.order.totalPrice, 60 * 3 + 180);
  assert.equal(result.order.phone, "+37369123456");
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
  assert.equal(data.totalPrice, 240);
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

test("preorders are rate limited per connection", async () => {
  let last;
  for (let i = 0; i < 8; i++) last = await post(goodOrder());
  assert.equal(last.status, 429);
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
