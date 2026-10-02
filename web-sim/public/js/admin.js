// Ed's preorder list. The key lives only in this tab's sessionStorage.
import { $, esc, toast } from "./ui.js";

const STATUSES = ["new", "confirmed", "fulfilled", "cancelled"];
const LABEL = { new: "New", confirmed: "Confirmed", fulfilled: "Done", cancelled: "Cancelled" };
let key = "";
try {
  key = sessionStorage.getItem("bed-admin") || "";
} catch {
  /* ignore */
}
let orders = [];
let names = new Map();
let filter = "new";

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
  $("#login").hidden = true;
  $("#app").hidden = false;
  $("#lock").hidden = false;
  render();
}

function render() {
  const counts = Object.fromEntries(STATUSES.map((s) => [s, orders.filter((o) => o.status === s).length]));
  $("#filters").innerHTML = ["all", ...STATUSES]
    .map((s) => `<button class="chip" type="button" data-f="${s}" aria-pressed="${filter === s}">${s === "all" ? "All" : LABEL[s]}<small>${s === "all" ? orders.length : counts[s]}</small></button>`)
    .join("");

  // What Ed needs to make: grams per flavour across orders he hasn't cancelled or finished.
  const todo = orders.filter((o) => o.status === "new" || o.status === "confirmed");
  const grams = new Map();
  for (const o of todo) for (const i of o.items) grams.set(i.flavour, (grams.get(i.flavour) || 0) + i.grams * i.qty);
  $("#prod").hidden = grams.size === 0;
  $("#prod").innerHTML = grams.size
    ? `<h2>To make (new + confirmed)</h2>` + [...grams].map(([id, g]) => `<span>${esc(names.get(id) || id)}: ${g >= 1000 ? `${+(g / 1000).toFixed(2)} kg` : `${g} g`}</span>`).join("")
    : "";

  const shown = filter === "all" ? orders : orders.filter((o) => o.status === filter);
  $("#empty").hidden = shown.length > 0;
  $("#list").innerHTML = shown.map(card).join("");
}

function card(o) {
  const when = new Date(o.createdAt).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
  const phone = esc(o.phone);
  return `<li class="order" data-status="${o.status}">
    <div class="order__top"><span class="order__code">${esc(o.code)}</span><span class="order__when">${esc(when)}</span></div>
    <p class="order__who">${esc(o.name)} · <a href="tel:${phone}">${phone}</a>${o.email ? ` · <a href="mailto:${esc(o.email)}">${esc(o.email)}</a>` : ""}</p>
    <ul class="order__items">${o.items.map((i) => `<li><span>${i.qty} × ${i.grams} g ${esc(names.get(i.flavour) || i.flavour)}</span><span>${i.price * i.qty} ${esc(o.currency)}</span></li>`).join("")}</ul>
    <p class="order__meta">${o.delivery === "delivery" ? `Delivery: ${esc(o.address)}` : "Pickup"}${o.date ? ` · wanted ${esc(o.date)}` : ""}${o.note ? `<br>Note: ${esc(o.note)}` : ""}</p>
    <div class="order__foot">
      <span class="order__total">${o.totalPrice} ${esc(o.currency)} <small class="order__when">${o.totalGrams} g</small></span>
      <label><span class="sr-only">Status for ${esc(o.code)}</span>
        <select data-code="${esc(o.code)}">${STATUSES.map((s) => `<option value="${s}"${s === o.status ? " selected" : ""}>${LABEL[s]}</option>`).join("")}</select>
      </label>
    </div>
  </li>`;
}

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
    $("#login-err").textContent = err.status === 404 ? "The admin page isn't switched on (ADMIN_KEY is not set)." : err.status === 429 ? "Too many tries. Wait a few minutes." : "That key doesn't work.";
  }
});
$("#filters").addEventListener("click", (e) => {
  const chip = e.target.closest("[data-f]");
  if (!chip) return;
  filter = chip.dataset.f;
  render();
});
$("#list").addEventListener("change", async (e) => {
  const select = e.target.closest("select[data-code]");
  if (!select) return;
  try {
    await api(`/api/admin/preorders/${encodeURIComponent(select.dataset.code)}`, { method: "PATCH", body: JSON.stringify({ status: select.value }) });
    const order = orders.find((o) => o.code === select.dataset.code);
    if (order) order.status = select.value;
    toast(`${select.dataset.code} marked ${LABEL[select.value].toLowerCase()}.`);
    render();
  } catch {
    toast("Couldn't save that. Try again.");
    load().catch(() => {});
  }
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
