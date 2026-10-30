// Ed's preorder list and stats (read-only: order status is kept in the Google Sheet). The key lives only in this tab's sessionStorage.
import { $, $$, esc, toast } from "./ui.js";

let key = "";
try {
  key = sessionStorage.getItem("bed-admin") || "";
} catch {
  /* ignore */
}
let orders = [];
let names = new Map();
let boxes = new Set();
let sizes = [100];
let currency = "MDL";
let tab = "orders";
let range = "all";
let email = { enabled: false };
let sheet = { enabled: false };

const money = (n) => Number(n).toLocaleString("en-US");
const weight = (g) => (g >= 1000 ? `${+(g / 1000).toFixed(2)} kg` : `${g} g`);
const packsOf = (o) => o.items.reduce((s, i) => s + i.qty, 0);

async function api(path, options = {}) {
  const res = await fetch(path, { ...options, headers: { "x-admin-key": key, "Content-Type": "application/json", ...(options.headers || {}) } });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw Object.assign(new Error(data.error || "Request failed"), { status: res.status });
  return data;
}

async function load() {
  const data = await api("/api/admin/preorders");
  orders = data.orders;
  names = new Map(data.flavours.map((f) => [f.id, f.name]));
  boxes = new Set(data.flavours.filter((f) => f.box).map((f) => f.id));
  sizes = data.sizes || sizes;
  currency = data.currency || currency;
  email = data.email || email;
  sheet = data.sheet || sheet;
  $("#login").hidden = true;
  $("#app").hidden = false;
  $("#lock").hidden = false;
  render();
}

// ---------------- Rendering ----------------
function render() {
  const isOrders = tab === "orders";
  for (const t of $$(".tab")) {
    const on = t.id === `tab-${tab}`;
    t.setAttribute("aria-selected", String(on));
    t.tabIndex = on ? 0 : -1;
  }
  $("#panel-orders").hidden = !isOrders;
  $("#panel-stats").hidden = isOrders;
  if (isOrders) renderOrders();
  else renderStats();
}

function chips(el, items, current, attr) {
  el.innerHTML = items.map(([v, label, n]) => `<button class="chip" type="button" data-${attr}="${v}" aria-pressed="${current === v}">${esc(label)}${n === undefined ? "" : `<small>${n}</small>`}</button>`).join("");
}

function renderMail() {
  const el = $("#mail");
  if (!email.enabled) {
    el.className = "mail mail--off";
    el.innerHTML = `<div><strong>Email notifications are off.</strong><p>Preorders are saved, but nobody is emailed. Set <code>BREVO_API_KEY</code> (or <code>RESEND_API_KEY</code>) and <code>EMAIL_FROM</code> on Railway to switch them on.</p></div>`;
    return;
  }
  const failed = Boolean(email.lastError);
  el.className = `mail ${failed ? "mail--bad" : "mail--on"}`;
  el.innerHTML = `<div><strong>${failed ? "The last email did not send" : "Email notifications are on"}</strong>
      <p>New preorders are emailed to <b>${esc(email.to)}</b> through ${esc(email.provider)}.${failed ? ` <span class="mail__err">${esc(email.lastError)}</span>` : ""}</p></div>
    <button class="btn btn--ghost btn--sm" type="button" id="mail-test"><span>Send test email</span></button>`;
}

function renderSheet() {
  const el = $("#sheet");
  if (!sheet.enabled) {
    el.className = "mail mail--off";
    el.innerHTML = `<div><strong>Google Sheets is off.</strong><p>Orders are not added to a sheet. Set <code>SHEETS_WEBHOOK_URL</code> and <code>SHEETS_TOKEN</code> on Railway (see the README).</p></div>`;
    return;
  }
  const failed = Boolean(sheet.lastError);
  el.className = `mail ${failed ? "mail--bad" : "mail--on"}`;
  el.innerHTML = `<div><strong>${failed ? "The last update to the sheet failed" : "Google Sheets is on"}</strong>
      <p>Every new order is added to the sheet straight away. Set each order's status there, in the Status column.${failed ? ` <span class="mail__err">${esc(sheet.lastError)}</span>` : ""}</p></div>
    <button class="btn btn--ghost btn--sm" type="button" id="sheet-sync"><span>Sync all orders</span></button>`;
}

// "2 × 100 g Texas BBQ Beef", or "1 × Taster box".
const itemLabel = (i) => `${i.qty} × ${boxes.has(i.flavour) ? "" : `${i.grams} g `}${names.get(i.flavour) || i.flavour}`;

function renderOrders() {
  renderMail();
  renderSheet();
  $("#empty").hidden = orders.length > 0;
  $("#list").innerHTML = orders.map(card).join("");
}

function card(o) {
  const when = new Date(o.createdAt).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
  const who = [o.name && `<strong>${esc(o.name)}</strong>`, o.phone && `<a href="tel:${esc(o.phone)}">${esc(o.phone)}</a>`, o.email && `<a href="mailto:${esc(o.email)}">${esc(o.email)}</a>`].filter(Boolean).join(" · ");
  return `<li class="order">
    <div class="order__top"><span class="order__code">${esc(o.code)}</span><span class="order__when">${esc(when)}</span></div>
    <p class="order__who">${who}</p>
    <ul class="order__items">${o.items.map((i) => `<li><span>${esc(itemLabel(i))}</span><span>${money(i.price * i.qty)} ${esc(o.currency)}</span></li>`).join("")}</ul>
    <p class="order__meta">${o.delivery === "delivery" ? `Delivery: ${esc(o.address)}` : "Pickup"}${o.date ? ` · wanted ${esc(o.date)}` : ""}${o.note ? `<br>Note: ${esc(o.note)}` : ""}</p>
    <div class="order__foot">
      <span class="order__total">${money(o.totalPrice)} ${esc(o.currency)} <small class="order__when">${weight(o.totalGrams)}</small></span>
    </div>
  </li>`;
}

// ---------------- Stats ----------------
function inRange(o) {
  if (range === "all") return true;
  return Date.now() - new Date(o.createdAt).getTime() <= Number(range) * 86400000;
}

function renderStats() {
  chips($("#ranges"), [["all", "All time"], ["30", "Last 30 days"], ["7", "Last 7 days"]], range, "r");
  const list = orders.filter(inRange);
  const el = $("#stats");
  if (!list.length) {
    el.innerHTML = `<p class="adm__empty">No preorders in this period yet.</p>`;
    return;
  }

  const live = list;
  const liveValue = live.reduce((s, o) => s + o.totalPrice, 0);
  const avg = live.length ? Math.round(liveValue / live.length) : 0;
  const cur = esc(currency);

  const kpi = (label, value, sub, mod = "") => `<div class="kpi ${mod}"><p class="kpi__lbl">${label}</p><p class="kpi__val">${value}</p><p class="kpi__sub">${sub}</p></div>`;
  const kpis = [
    kpi("Total value", `${money(liveValue)} <small>${cur}</small>`, `${live.length} ${live.length === 1 ? "order" : "orders"}`, "kpi--main"),
    kpi("Average order", `${money(avg)} <small>${cur}</small>`, "per order"),
    kpi("Packs", money(live.reduce((s, o) => s + packsOf(o), 0)), "boxes count as one"),
    kpi("Weight", weight(live.reduce((s, o) => s + o.totalGrams, 0)), "all orders together"),
  ].join("");

  // Flavours
  const fl = new Map();
  for (const o of live) for (const i of o.items) {
    const f = fl.get(i.flavour) || { packs: 0, grams: 0, value: 0 };
    f.packs += i.qty;
    f.grams += i.grams * i.qty;
    f.value += i.price * i.qty;
    fl.set(i.flavour, f);
  }
  const flRows = [...fl].sort((a, b) => b[1].value - a[1].value);
  const maxFl = Math.max(1, ...flRows.map(([, f]) => f.value));
  const flavourBars = flRows.length
    ? flRows.map(([id, f]) => `<li class="hbar"><div class="hbar__top"><strong>${esc(names.get(id) || id)}</strong><span>${money(f.value)} ${cur}</span></div><div class="hbar__track"><span style="width:${Math.max(3, (f.value / maxFl) * 100)}%"></span></div><p>${f.packs} ${f.packs === 1 ? "pack" : "packs"} · ${weight(f.grams)}</p></li>`).join("")
    : `<li class="adm__empty">Nothing yet.</li>`;

  // Pack sizes
  const sz = new Map();
  for (const o of live) for (const i of o.items) {
    const k = boxes.has(i.flavour) ? names.get(i.flavour) || i.flavour : sizes.includes(i.grams) ? `${i.grams} g` : "Custom sizes";
    const s = sz.get(k) || { packs: 0, value: 0 };
    s.packs += i.qty;
    s.value += i.price * i.qty;
    sz.set(k, s);
  }
  const order = [...sizes.map((g) => `${g} g`), "Custom sizes", ...[...boxes].map((id) => names.get(id) || id)];
  const sizeRows = order.filter((k) => sz.has(k)).map((k) => `<tr><th scope="row">${k}</th><td>${sz.get(k).packs}</td><td>${money(sz.get(k).value)} ${cur}</td></tr>`).join("") || `<tr><td colspan="3">Nothing yet.</td></tr>`;

  const pick = live.filter((o) => o.delivery === "pickup").length;
  const del = live.length - pick;

  // Last 14 days
  const days = [];
  for (let i = 13; i >= 0; i--) {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    d.setDate(d.getDate() - i);
    days.push({ d, key: d.toDateString(), n: 0, value: 0 });
  }
  const dayMap = new Map(days.map((x) => [x.key, x]));
  for (const o of live) {
    const slot = dayMap.get(new Date(o.createdAt).toDateString());
    if (slot) {
      slot.n++;
      slot.value += o.totalPrice;
    }
  }
  const maxDay = Math.max(1, ...days.map((x) => x.value));
  const dayCols = days
    .map((x) => `<li title="${x.d.toLocaleDateString(undefined, { day: "numeric", month: "short" })}: ${x.n} orders, ${money(x.value)} ${esc(currency)}"><span class="col__val">${x.n ? money(x.value) : ""}</span><span class="col__bar" style="height:${x.value ? Math.max(6, (x.value / maxDay) * 100) : 2}%"></span><span class="col__lbl">${x.d.getDate()}/${x.d.getMonth() + 1}</span></li>`)
    .join("");
  const daySummary = days.map((x) => `${x.d.getDate()}/${x.d.getMonth() + 1}: ${x.n} orders, ${money(x.value)} ${currency}`).join("; ");

  el.innerHTML = `
    <div class="kpis">${kpis}</div>
    <section class="panel"><h2>Flavours <small>by value</small></h2><ul class="hbars">${flavourBars}</ul></section>
    <div class="two">
      <section class="panel"><h2>Pack sizes</h2>
        <div class="tablewrap"><table class="tbl"><thead><tr><th scope="col">Size</th><th scope="col">Packs</th><th scope="col">Value</th></tr></thead><tbody>${sizeRows}</tbody></table></div>
      </section>
      <section class="panel"><h2>Pickup or delivery</h2>
        <div class="tablewrap"><table class="tbl"><thead><tr><th scope="col">How</th><th scope="col">Orders</th></tr></thead><tbody><tr><th scope="row">Pickup</th><td>${pick}</td></tr><tr><th scope="row">Delivery</th><td>${del}</td></tr></tbody></table></div>
      </section>
    </div>
    <section class="panel"><h2>Last 14 days <small>value per day</small></h2>
      <ul class="cols" role="img" aria-label="Order value per day for the last 14 days. ${esc(daySummary)}">${dayCols}</ul>
    </section>`;
}

// ---------------- Wiring ----------------
$("#login").addEventListener("submit", async (e) => {
  e.preventDefault();
  key = $("#key").value.trim();
  $("#login-err").textContent = "";
  try {
    await load();
    try {
      sessionStorage.setItem("bed-admin", key);
    } catch {
      /* ignore */
    }
  } catch (err) {
    $("#login-err").textContent = err.status === 404 ? "The admin page isn't switched on (ADMIN_KEY is not set)." : err.status === 429 ? "Too many wrong keys. Wait a few minutes and try again." : "That key doesn't work.";
  }
});
$("#ranges").addEventListener("click", (e) => {
  const chip = e.target.closest("[data-r]");
  if (chip) {
    range = chip.dataset.r;
    render();
  }
});
for (const t of $$(".tab")) {
  t.addEventListener("click", () => {
    tab = t.id === "tab-stats" ? "stats" : "orders";
    render();
  });
}
$(".tabs").addEventListener("keydown", (e) => {
  if (!["ArrowLeft", "ArrowRight"].includes(e.key)) return;
  tab = tab === "orders" ? "stats" : "orders";
  render();
  $(`#tab-${tab}`).focus();
});
$("#mail").addEventListener("click", async (e) => {
  const btn = e.target.closest("#mail-test");
  if (!btn) return;
  btn.disabled = true;
  btn.querySelector("span").textContent = "Sending…";
  try {
    const r = await api("/api/admin/email-test", { method: "POST", body: "{}" });
    toast(`Test email sent to ${r.to}. Check the inbox, and the spam folder too.`);
    email = { ...email, lastError: null };
  } catch (err) {
    email = { ...email, lastError: err.message };
    toast("The test email failed. The reason is shown on the page.");
  }
  renderMail();
});
$("#sheet").addEventListener("click", async (e) => {
  const btn = e.target.closest("#sheet-sync");
  if (!btn) return;
  btn.disabled = true;
  btn.querySelector("span").textContent = "Syncing…";
  try {
    const r = await api("/api/admin/sheet-sync", { method: "POST", body: "{}" });
    toast(r.added === null ? `${r.count} orders sent to the sheet.` : `${r.added} missing ${r.added === 1 ? "order" : "orders"} added. All ${r.count} are in the sheet now.`);
    sheet = { ...sheet, lastError: null };
  } catch (err) {
    sheet = { ...sheet, lastError: err.message };
    toast("The sync failed. The reason is shown on the page.");
  }
  renderSheet();
});
$("#refresh").addEventListener("click", () => load().then(() => toast("Updated.")).catch(() => toast("Couldn't refresh.")));
$("#lock").addEventListener("click", () => {
  try {
    sessionStorage.removeItem("bed-admin");
  } catch {
    /* ignore */
  }
  location.reload();
});

if (key) load().catch(() => {});
// Pick up new orders without a manual refresh.
setInterval(() => {
  if (!key || document.hidden || $("#app").hidden) return;
  load().catch(() => {});
}, 45000);
