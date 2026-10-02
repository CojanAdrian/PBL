// Ed's preorder list and stats. The key lives only in this tab's sessionStorage.
import { $, $$, esc, toast } from "./ui.js";

const STATUSES = ["new", "confirmed", "fulfilled", "cancelled"];
const LABEL = { new: "New", confirmed: "Confirmed", fulfilled: "Done", cancelled: "Cancelled" };
const CHEVRON = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
const CHECK = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';

let key = "";
try {
  key = sessionStorage.getItem("bed-admin") || "";
} catch {
  /* ignore */
}
let orders = [];
let names = new Map();
let sizes = [50, 100, 150];
let currency = "MDL";
let tab = "orders";
let filter = "new";
let range = "all";

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
  sizes = data.sizes || sizes;
  currency = data.currency || currency;
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

function renderOrders() {
  const counts = Object.fromEntries(STATUSES.map((s) => [s, orders.filter((o) => o.status === s).length]));
  chips($("#filters"), [["all", "All", orders.length], ...STATUSES.map((s) => [s, LABEL[s], counts[s]])], filter, "f");

  // What Ed needs to make: grams per flavour across orders he has not finished or cancelled.
  const todo = orders.filter((o) => o.status === "new" || o.status === "confirmed");
  const grams = new Map();
  for (const o of todo) for (const i of o.items) grams.set(i.flavour, (grams.get(i.flavour) || 0) + i.grams * i.qty);
  $("#prod").hidden = grams.size === 0;
  $("#prod").innerHTML = grams.size ? `<h2>To make (new + confirmed)</h2>` + [...grams].map(([id, g]) => `<span>${esc(names.get(id) || id)}: ${weight(g)}</span>`).join("") : "";

  const shown = filter === "all" ? orders : orders.filter((o) => o.status === filter);
  $("#empty").hidden = shown.length > 0;
  $("#list").innerHTML = shown.map(card).join("");
}

function dropdown(o) {
  return `<div class="dd" data-code="${esc(o.code)}">
    <button class="dd__btn" type="button" aria-haspopup="listbox" aria-expanded="false" aria-label="Status for ${esc(o.code)}: ${LABEL[o.status]}">
      <i class="dot dot--${o.status}"></i><span>${LABEL[o.status]}</span>${CHEVRON}
    </button>
    <ul class="dd__menu" role="listbox" aria-label="Set status for ${esc(o.code)}" hidden>
      ${STATUSES.map((s) => `<li role="option" tabindex="-1" data-v="${s}" aria-selected="${s === o.status}"><i class="dot dot--${s}"></i><span>${LABEL[s]}</span>${s === o.status ? CHECK : ""}</li>`).join("")}
    </ul>
  </div>`;
}

function card(o) {
  const when = new Date(o.createdAt).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
  const who = [o.name && `<strong>${esc(o.name)}</strong>`, o.phone && `<a href="tel:${esc(o.phone)}">${esc(o.phone)}</a>`, o.email && `<a href="mailto:${esc(o.email)}">${esc(o.email)}</a>`].filter(Boolean).join(" · ");
  return `<li class="order" data-status="${o.status}">
    <div class="order__top"><span class="order__code">${esc(o.code)}</span><span class="order__when">${esc(when)}</span></div>
    <p class="order__who">${who}</p>
    <ul class="order__items">${o.items.map((i) => `<li><span>${i.qty} × ${i.grams} g ${esc(names.get(i.flavour) || i.flavour)}</span><span>${money(i.price * i.qty)} ${esc(o.currency)}</span></li>`).join("")}</ul>
    <p class="order__meta">${o.delivery === "delivery" ? `Delivery: ${esc(o.address)}` : "Pickup"}${o.date ? ` · wanted ${esc(o.date)}` : ""}${o.note ? `<br>Note: ${esc(o.note)}` : ""}</p>
    <div class="order__foot">
      <span class="order__total">${money(o.totalPrice)} ${esc(o.currency)} <small class="order__when">${weight(o.totalGrams)}</small></span>
      ${dropdown(o)}
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

  const by = Object.fromEntries(STATUSES.map((s) => [s, { orders: 0, packs: 0, grams: 0, value: 0 }]));
  for (const o of list) {
    const b = by[o.status];
    b.orders++;
    b.packs += packsOf(o);
    b.grams += o.totalGrams;
    b.value += o.totalPrice;
  }
  const live = list.filter((o) => o.status !== "cancelled");
  const open = list.filter((o) => o.status === "new" || o.status === "confirmed");
  const liveValue = live.reduce((s, o) => s + o.totalPrice, 0);
  const openValue = by.new.value + by.confirmed.value;
  const avg = live.length ? Math.round(liveValue / live.length) : 0;
  const cur = esc(currency);

  const kpi = (label, value, sub, mod = "") => `<div class="kpi ${mod}"><p class="kpi__lbl">${label}</p><p class="kpi__val">${value}</p><p class="kpi__sub">${sub}</p></div>`;
  const kpis = [
    kpi("Total value", `${money(liveValue)} <small>${cur}</small>`, `${live.length} ${live.length === 1 ? "order" : "orders"}, cancelled not counted`, "kpi--main"),
    kpi("Still to do", `${money(openValue)} <small>${cur}</small>`, `${by.new.orders} new + ${by.confirmed.orders} confirmed`, "kpi--open"),
    kpi("Done", `${money(by.fulfilled.value)} <small>${cur}</small>`, `${by.fulfilled.orders} ${by.fulfilled.orders === 1 ? "order" : "orders"} delivered`, "kpi--done"),
    kpi("Cancelled", `${money(by.cancelled.value)} <small>${cur}</small>`, `${by.cancelled.orders} ${by.cancelled.orders === 1 ? "order" : "orders"}`, "kpi--cancel"),
    kpi("Average order", `${money(avg)} <small>${cur}</small>`, "per order, cancelled not counted"),
    kpi("To make now", weight(open.reduce((s, o) => s + o.totalGrams, 0)), `${open.reduce((s, o) => s + packsOf(o), 0)} packs in open orders`),
  ].join("");

  const rows = STATUSES.map((s) => `<tr><th scope="row"><i class="dot dot--${s}"></i>${LABEL[s]}</th><td>${by[s].orders}</td><td>${by[s].packs}</td><td class="hide-sm">${weight(by[s].grams)}</td><td>${money(by[s].value)} ${cur}</td></tr>`).join("");
  const total = list.reduce((a, o) => ({ orders: a.orders + 1, packs: a.packs + packsOf(o), grams: a.grams + o.totalGrams, value: a.value + o.totalPrice }), { orders: 0, packs: 0, grams: 0, value: 0 });

  // Flavours (cancelled orders left out)
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
    const k = sizes.includes(i.grams) ? `${i.grams} g` : "Custom sizes";
    const s = sz.get(k) || { packs: 0, value: 0 };
    s.packs += i.qty;
    s.value += i.price * i.qty;
    sz.set(k, s);
  }
  const order = [...sizes.map((g) => `${g} g`), "Custom sizes"];
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
    <section class="panel"><h2>By status</h2>
      <div class="tablewrap"><table class="tbl"><thead><tr><th scope="col">Status</th><th scope="col">Orders</th><th scope="col">Packs</th><th scope="col" class="hide-sm">Weight</th><th scope="col">Value</th></tr></thead>
      <tbody>${rows}</tbody>
      <tfoot><tr><th scope="row">All orders</th><td>${total.orders}</td><td>${total.packs}</td><td class="hide-sm">${weight(total.grams)}</td><td>${money(total.value)} ${cur}</td></tr></tfoot></table></div>
    </section>
    <section class="panel"><h2>Flavours <small>by value, cancelled not counted</small></h2><ul class="hbars">${flavourBars}</ul></section>
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

// ---------------- Status dropdown ----------------
function closeMenus(except) {
  for (const dd of $$(".dd.is-open")) {
    if (dd === except) continue;
    dd.classList.remove("is-open", "is-up");
    $(".dd__btn", dd).setAttribute("aria-expanded", "false");
    $(".dd__menu", dd).hidden = true;
  }
}
function openMenu(dd) {
  closeMenus(dd);
  const btn = $(".dd__btn", dd);
  const menu = $(".dd__menu", dd);
  dd.classList.toggle("is-up", window.innerHeight - btn.getBoundingClientRect().bottom < 230);
  menu.hidden = false;
  dd.classList.add("is-open");
  btn.setAttribute("aria-expanded", "true");
  ($('[aria-selected="true"]', menu) || $("[role=option]", menu)).focus();
}
async function setStatus(code, status) {
  const order = orders.find((o) => o.code === code);
  if (!order || order.status === status) return render();
  const before = order.status;
  order.status = status;
  render();
  try {
    await api(`/api/admin/preorders/${encodeURIComponent(code)}`, { method: "PATCH", body: JSON.stringify({ status }) });
    toast(`${code} is now ${LABEL[status].toLowerCase()}.`);
  } catch {
    order.status = before;
    render();
    toast("Couldn't save that. Try again.");
  }
}

$("#list").addEventListener("click", (e) => {
  const dd = e.target.closest(".dd");
  if (!dd) return;
  const option = e.target.closest("[role=option]");
  if (option) {
    closeMenus();
    setStatus(dd.dataset.code, option.dataset.v);
  } else if (e.target.closest(".dd__btn")) {
    dd.classList.contains("is-open") ? closeMenus() : openMenu(dd);
  }
});
$("#list").addEventListener("keydown", (e) => {
  const dd = e.target.closest(".dd");
  if (!dd) return;
  const btn = $(".dd__btn", dd);
  const open = dd.classList.contains("is-open");
  if (e.target === btn && ["ArrowDown", "ArrowUp"].includes(e.key)) {
    e.preventDefault();
    openMenu(dd);
    return;
  }
  if (!open) return;
  const items = $$("[role=option]", dd);
  const i = items.indexOf(document.activeElement);
  if (e.key === "Escape") {
    e.preventDefault();
    closeMenus();
    btn.focus();
  } else if (e.key === "ArrowDown") {
    e.preventDefault();
    items[(i + 1) % items.length].focus();
  } else if (e.key === "ArrowUp") {
    e.preventDefault();
    items[(i - 1 + items.length) % items.length].focus();
  } else if (e.key === "Home" || e.key === "End") {
    e.preventDefault();
    items[e.key === "Home" ? 0 : items.length - 1].focus();
  } else if ((e.key === "Enter" || e.key === " ") && i >= 0) {
    e.preventDefault();
    closeMenus();
    setStatus(dd.dataset.code, items[i].dataset.v);
  } else if (e.key === "Tab") {
    closeMenus();
  }
});
document.addEventListener("click", (e) => {
  if (!e.target.closest(".dd")) closeMenus();
});

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
$("#filters").addEventListener("click", (e) => {
  const chip = e.target.closest("[data-f]");
  if (chip) {
    filter = chip.dataset.f;
    render();
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
    closeMenus();
    render();
  });
}
$(".tabs").addEventListener("keydown", (e) => {
  if (!["ArrowLeft", "ArrowRight"].includes(e.key)) return;
  tab = tab === "orders" ? "stats" : "orders";
  render();
  $(`#tab-${tab}`).focus();
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
// Pick up new orders without a manual refresh, as long as nothing is open.
setInterval(() => {
  if (!key || document.hidden || $("#app").hidden || $(".dd.is-open")) return;
  load().catch(() => {});
}, 45000);
