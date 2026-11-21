// The redesigned page (/betatest).
// Loader → hero with a live 3D pack → tapes → how to order → one full-screen chapter
// per flavour (the page colour follows the flavour) → 3D inspector → Ed → FAQ.
// Ordering: + on a flavour, then the pull tab (desktop) or the bottom bar (phones)
// opens the order: packs drop into the bag, name, phone, send.
// Products, prices and the order endpoint are the same as the main page.
import { $, $$, esc, store, toast } from "./ui.js";
import { pouchSVG, prepareMenu, fmtGrams } from "./pouch.js";
import { t, packs } from "./i18n.js";

const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const finePointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
const cfg = JSON.parse($("#menu-data").textContent);
const B = JSON.parse($("#beta-texts").textContent);
prepareMenu(cfg);

const STORE_KEY = "bigeds.beta.v1";
const MAX_QTY = cfg.limits.maxPacksPerLine;
const cur = cfg.currency;
const fill = (text, vars = {}) => String(text).replace(/\{(\w+)\}/g, (m, k) => (k in vars ? String(vars[k]) : m));
const b = (key, vars) => fill(B[key] ?? key, vars);
const num = (n) => String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
const money = (n) => `${num(n)} ${cur}`;
const clamp = (v, a = 0, z = 1) => Math.max(a, Math.min(z, v));
const icon = (d, size = 18, sw = 2.2) =>
  `<svg viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="${sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${d}"/></svg>`;
const MINUS = icon("M5 12h14");
const PLUS = icon("M12 5v14M5 12h14");

// ---------- Products ----------
const products = [...cfg.flavours, ...(cfg.boxes || [])];
const byId = new Map(products.map((p) => [p.id, p]));
const groups = [];
for (const f of cfg.flavours) {
  let g = groups.find((x) => x.name === f.name);
  if (!g) groups.push((g = { name: f.name, items: [] }));
  g.items.push(f);
}
const box = (cfg.boxes || [])[0];
const fullName = (p) => p.fullName || p.name;
const slugOf = (id) => (/texas|bbq/i.test(id) ? "texas" : /garlic|teriyaki/i.test(id) ? "garlic" : /honey|sriracha/i.test(id) ? "honey" : "");
// The colour of each flavour's chapter (page background and text).
const MOOD = {
  texas: { bg: "#5e0f0a", ink: "#f6e6cc", accent: "#ff8a3d" },
  garlic: { bg: "#ecdcbf", ink: "#1b1410", accent: "#a65b0c" },
  honey: { bg: "#e97f0e", ink: "#160b04", accent: "#fff1d6" },
  taster: { bg: "#0b0706", ink: "#f6e6cc", accent: "#e97f0e" },
};
const moodOf = (p) => (p.box ? MOOD.taster : MOOD[slugOf(p.id)] || { bg: "#1a0f0c", ink: "#f6e6cc", accent: p.accent || "#e97f0e" });
const packSrc = (p) => `${B._packs}/${p.id}.webp`;
const packImg = (p, cls = "", eager = false) =>
  `<img class="${cls}" src="${packSrc(p)}" alt="${esc(b("pack_alt", { name: fullName(p) }))}" width="1200" height="1500" ${eager ? "" : 'loading="lazy"'} decoding="async" data-pack-img="${esc(p.id)}">`;
// A missing packshot falls back to the flat drawing.
document.addEventListener(
  "error",
  (e) => {
    const img = e.target;
    if (!(img instanceof HTMLImageElement) || !img.dataset.packImg) return;
    const p = byId.get(img.dataset.packImg);
    const span = document.createElement("span");
    span.className = `${img.className} pack-svg`;
    span.innerHTML = pouchSVG(p, { label: img.alt });
    img.replaceWith(span);
  },
  true
);

// ---------- State ----------
const state = { lines: {}, d: { name: "", phone: "", note: "" }, sending: false, error: "", errors: {}, done: null };
try {
  const saved = JSON.parse(store.get(STORE_KEY) || "null");
  if (saved) {
    for (const [id, q] of Object.entries(saved.lines || {})) if (byId.has(id) && q > 0) state.lines[id] = Math.min(MAX_QTY, Math.floor(q));
    Object.assign(state.d, saved.d || {});
  }
} catch {
  /* start fresh */
}
const save = () => store.set(STORE_KEY, JSON.stringify({ lines: state.lines, d: state.d }));
const qty = (id) => state.lines[id] || 0;
function totals() {
  let count = 0;
  let cost = 0;
  for (const [id, q] of Object.entries(state.lines)) {
    count += q;
    cost += byId.get(id).price * q;
  }
  return { count, cost };
}

// ======================================================================
// Flavour chapters
// ======================================================================
const chaptersEl = $("[data-chapters]");
const chili = `<svg viewBox="0 0 24 24" aria-hidden="true"><path class="stem" d="M15.5 6.5c.3-2 1.6-3.3 3.5-3.5"/><path class="body" d="M10.2 7.4c2.6-1.6 6-1.2 7.4 1.2 1.6 2.8-.2 6.6-3.6 9.4-2.9 2.4-6.6 3.6-9.6 3.4-.9 0-1-.9-.2-1.3 2.7-1.3 4.6-3.3 5.3-6 .5-2.1-.6-3.8.7-6.7z"/></svg>`;
function heatHTML(level) {
  const word = B[`heat_${clamp(level, 1, 5)}`];
  const chilies = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}">${chili}</i>`).join("");
  return `<p class="heat"><span class="heat__c" role="img" aria-label="${esc(B.heat)}: ${esc(word)}">${chilies}</span><span>${esc(word)}</span></p>`;
}
function buyRow(p, label) {
  const q = qty(p.id);
  const right = q
    ? `<span class="buy__step" role="group" aria-label="${esc(fullName(p))}">
        <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
        <output aria-live="polite">${q}</output>
        <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
      </span>`
    : `<button class="buy__add" type="button" data-add="${esc(p.id)}" aria-label="${esc(b("add_meat", { meat: label }))} · ${esc(money(p.price))}">${PLUS}</button>`;
  return `<div class="buy${q ? " is-in" : ""}" data-buy="${esc(p.id)}"><span class="buy__label"><strong>${esc(label)}</strong><span>${esc(money(p.price))}${q ? ` · ${q} ${esc(B.in_order)}` : ""}</span></span>${right}</div>`;
}
function renderChapters() {
  const items = [...groups.map((g) => ({ group: g, p: g.items[0] })), ...(box ? [{ box, p: box }] : [])];
  chaptersEl.innerHTML = items
    .map((it, i) => {
      const p = it.p;
      const m = moodOf(p);
      const word = it.box ? B.taster_word : B[`flavour_${slugOf(p.id)}_word`] || it.group.name;
      const packsHTML = it.box
        ? `<div class="chap__packs chap__packs--box">${packImg(box, "chap__pack chap__pack--a")}${cfg.flavours
            .slice(0, 6)
            .map((f, k) => `<span class="chap__mini" style="--k:${k}">${packImg(f, "")}</span>`)
            .join("")}</div>`
        : `<div class="chap__packs">${it.group.items.map((x, k) => packImg(x, `chap__pack chap__pack--${k ? "b" : "a"}`)).join("")}</div>`;
      const buys = it.box ? buyRow(box, box.name) : it.group.items.map((x) => buyRow(x, x.meatName)).join("");
      return `<article class="chap" data-chap="${i}" data-bg="${m.bg}" data-ink="${m.ink}" data-accent="${m.accent}" aria-labelledby="chap-${i}">
          <p class="chap__word" aria-hidden="true"><span>${esc(word)}</span></p>
          ${packsHTML}
          <div class="chap__info">
            <p class="chap__idx">0${i + 1} <span>/ 0${items.length}</span></p>
            <h3 class="chap__name" id="chap-${i}">${esc(it.box ? box.name : it.group.name)}</h3>
            <p class="chap__tag">${esc(it.box ? B.taster_lead : p.tagline || "")}</p>
            ${it.box ? "" : `<p class="chap__notes">${esc(p.notes || "")}</p>${heatHTML(p.heat)}`}
            <div class="chap__buy" data-buys>${buys}</div>
          </div>
        </article>`;
    })
    .join("");
}
function updateBuys() {
  for (const el of $$("[data-buy]", chaptersEl)) {
    const p = byId.get(el.dataset.buy);
    const label = p.box ? p.name : p.meatName;
    const focused = document.activeElement && el.contains(document.activeElement) ? document.activeElement.dataset : null;
    el.outerHTML = buyRow(p, label);
    if (focused) {
      const id = p.id;
      ($(`[data-inc="${CSS.escape(id)}"]`, chaptersEl) || $(`[data-add="${CSS.escape(id)}"]`, chaptersEl))?.focus({ preventScroll: true });
    }
  }
}

// ======================================================================
// Order: pull tab, bottom bar, drawer
// ======================================================================
const pull = $("[data-pull]");
const bagbar = $("[data-bagbar]");
let lastCount = -1;
function renderCounters() {
  const { count, cost } = totals();
  $("[data-count]").textContent = String(count);
  $("[data-count]").classList.toggle("is-on", count > 0);
  $("[data-pull-label]").hidden = count > 0;
  const sum = $("[data-pull-sum]");
  sum.hidden = count === 0;
  sum.innerHTML = `<b>${count}</b>${esc(money(cost))}`;
  bagbar.hidden = count === 0;
  document.body.classList.toggle("has-bag", count > 0);
  $("[data-bagbar-count]").textContent = String(count);
  $("[data-bagbar-sum]").textContent = `${packs(count)} · ${money(cost)}`;
  if (count > lastCount && lastCount >= 0 && !reduce) {
    pull.animate([{ transform: "translateY(0)" }, { transform: "translateY(14px)" }, { transform: "translateY(0)" }], { duration: 520, easing: "cubic-bezier(.3,1.6,.5,1)" });
  }
  lastCount = count;
}
// The added pack flies to the tab (or the bar on phones).
function fly(fromEl, p) {
  if (reduce || !fromEl) return;
  const target = getComputedStyle(pull).display !== "none" ? pull : bagbar.hidden ? $(".nav__order") : bagbar;
  const a = fromEl.getBoundingClientRect();
  const z = target.getBoundingClientRect();
  if (!a.width || a.bottom < 0 || a.top > innerHeight) return;
  const node = document.createElement("div");
  node.className = "fly";
  node.innerHTML = packImg(p, "");
  const w = Math.min(a.width, 220);
  const x0 = a.left + a.width / 2 - w / 2;
  const y0 = a.top + a.height / 2 - w * 0.625;
  Object.assign(node.style, { left: `${x0}px`, top: `${y0}px`, width: `${w}px` });
  document.body.appendChild(node);
  const dx = z.left + z.width / 2 - (x0 + w / 2);
  const dy = z.top + z.height / 2 - (y0 + w * 0.625);
  node
    .animate(
      [
        { transform: "translate(0,0) scale(1) rotate(-6deg)", opacity: 1 },
        { transform: `translate(${dx * 0.4}px, ${dy * 0.4 - 160}px) scale(.6) rotate(-24deg)`, opacity: 1, offset: 0.45 },
        { transform: `translate(${dx}px, ${dy}px) scale(.12) rotate(-50deg)`, opacity: 0.4 },
      ],
      { duration: 850, easing: "cubic-bezier(.5,0,.4,1)" }
    )
    .finished.finally(() => node.remove());
}

// Pull the tab down (or click it) to open the order.
if (pull) {
  let start = null;
  pull.addEventListener("pointerdown", (e) => {
    start = { y: e.clientY, moved: 0 };
    pull.setPointerCapture(e.pointerId);
  });
  pull.addEventListener("pointermove", (e) => {
    if (!start) return;
    const d = clamp(e.clientY - start.y, 0, 120);
    start.moved = d;
    pull.style.transform = `translateY(${d * 0.6}px)`;
  });
  const end = () => {
    if (!start) return;
    pull.style.transform = "";
    start = null;
  };
  pull.addEventListener("pointerup", end);
  pull.addEventListener("pointercancel", end);
}

const sheet = $("[data-sheet]");
const sheetBody = $("[data-sheet-body]");
const bagEl = $("[data-bag]");
function renderBag({ drop = false } = {}) {
  const items = [];
  for (const [id, q] of Object.entries(state.lines)) for (let i = 0; i < Math.min(q, 6); i++) items.push(byId.get(id));
  const shown = items.slice(0, 14);
  bagEl.innerHTML = shown
    .map((p, i) => `<span class="bag__p${drop ? " is-drop" : ""}" style="--i:${i};--n:${shown.length};--r:${(((i * 37) % 9) - 4) * 3}deg">${packImg(p, "", true)}</span>`)
    .join("");
  bagEl.hidden = shown.length === 0;
}
function lineHTML(id, q) {
  const p = byId.get(id);
  const sub = p.box ? fmtGrams(p.grams) : `${p.meatName} · ${fmtGrams(p.grams)}`;
  return `<li class="line">
      <span class="line__img">${packImg(p, "", true)}</span>
      <div class="line__main">
        <p class="line__name">${esc(p.name)}</p>
        <p class="line__sub">${esc(sub)}</p>
        <div class="line__row">
          <span class="buy__step buy__step--sm" role="group" aria-label="${esc(fullName(p))}">
            <button type="button" data-dec="${esc(id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
            <output aria-live="polite">${q}</output>
            <button type="button" data-inc="${esc(id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
          </span>
          <button class="line__remove" type="button" data-remove="${esc(id)}">${esc(B.remove)}</button>
        </div>
      </div>
      <p class="line__price">${esc(money(p.price * q))}</p>
    </li>`;
}
function renderSheet() {
  if (!sheet.open) return;
  if (state.done) {
    bagEl.hidden = true;
    const d = state.done;
    sheetBody.innerHTML = `<div class="done">
        <img class="done__seal" src="${B._logo.replace("logo.png", "skull.webp")}" width="466" height="315" alt="">
        <h3 tabindex="-1">${esc(b("done_title", { name: d.name }))}</h3>
        <p>${esc(b("done_lead", { phone: d.phone }))}</p>
        <p class="done__code"><small>${esc(B.done_code)}</small><strong>${esc(d.code)}</strong></p>
        <button class="btn btn--ember btn--wide" type="button" data-close>${esc(B.done_close)}</button>
      </div>`;
    return;
  }
  const ids = Object.keys(state.lines);
  if (!ids.length) {
    bagEl.hidden = true;
    sheetBody.innerHTML = `<div class="empty"><p>${esc(B.sheet_empty)}</p><a class="btn btn--ember" href="#flavours" data-close>${esc(B.sheet_browse)}</a></div>`;
    return;
  }
  const { cost } = totals();
  const err = (k) => esc(state.errors[k] || "");
  const focusedName = document.activeElement?.closest?.("[data-sheet]") && document.activeElement.name;
  sheetBody.innerHTML = `
    <ul class="lines">${ids.map((id) => lineHTML(id, state.lines[id])).join("")}</ul>
    <p class="total"><span>${esc(B.total)}</span><strong>${esc(money(cost))}</strong></p>
    ${state.errors.items ? `<p class="form__err" role="alert">${err("items")}</p>` : ""}
    <form class="form" novalidate data-form>
      <h3>${esc(B.details)}</h3>
      <div class="field">
        <input class="input" id="b-name" name="name" autocomplete="name" maxlength="80" required placeholder=" " value="${esc(state.d.name)}" ${state.errors.name ? 'aria-invalid="true"' : ""} aria-describedby="e-name">
        <label for="b-name">${esc(B.name)}</label>
        <p class="err" id="e-name">${err("name")}</p>
      </div>
      <div class="field">
        <input class="input" id="b-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="30" required placeholder=" " value="${esc(state.d.phone)}" ${state.errors.phone ? 'aria-invalid="true"' : ""} aria-describedby="e-phone">
        <label for="b-phone">${esc(B.phone)}</label>
        <p class="err" id="e-phone">${err("phone")}</p>
      </div>
      <div class="field">
        <textarea class="input" id="b-note" name="note" rows="2" maxlength="500" placeholder=" ">${esc(state.d.note)}</textarea>
        <label for="b-note">${esc(B.note)}</label>
      </div>
      <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
      ${state.error ? `<p class="form__err" role="alert">${esc(state.error)}</p>` : ""}
      <button class="btn btn--ember btn--wide" type="submit" ${state.sending ? "disabled" : ""}>${state.sending ? `<span class="spin" aria-hidden="true"></span>${esc(B.sending)}` : `<span>${esc(B.send)}</span><span class="btn__price">${esc(money(cost))}</span>`}</button>
      <p class="form__note">${esc(B.sheet_pay)}</p>
    </form>`;
  if (focusedName) $(`[name="${focusedName}"]`, sheetBody)?.focus();
}
let lenis = null;
function openSheet() {
  if (!sheet.open) {
    sheet.showModal();
    lenis?.stop();
    renderBag({ drop: true });
  }
  renderSheet();
  (state.done ? $(".done h3", sheetBody) : $(".drawer__close", sheet))?.focus();
}
const closeSheet = () => sheet.close();
sheet.addEventListener("close", () => {
  lenis?.start();
  if (state.done) {
    state.done = null;
    renderAll();
  }
});
sheet.addEventListener("click", (e) => {
  if (e.target === sheet) closeSheet();
});
$$("[data-open-order]").forEach((btn) =>
  btn.addEventListener("click", () => {
    openSheet();
  })
);

const checks = {
  name: (v) => (v.trim().length < 2 ? t("err_name") : ""),
  phone: (v) => (!v.trim() ? t("err_phone_missing") : !/^\+?\d{7,15}$/.test(v.replace(/[\s().-]/g, "")) ? t("err_phone") : ""),
};
async function send(form) {
  if (state.sending) return;
  state.errors = {};
  for (const [k, check] of Object.entries(checks)) {
    const msg = check(state.d[k] || "");
    if (msg) state.errors[k] = msg;
  }
  if (Object.keys(state.errors).length) {
    renderSheet();
    $(`[name="${Object.keys(state.errors)[0]}"]`, sheetBody)?.focus();
    return;
  }
  state.sending = true;
  state.error = "";
  renderSheet();
  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), 20000);
  try {
    const res = await fetch(cfg.endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": $('meta[name="csrf-token"]')?.content || "" },
      body: JSON.stringify({
        name: state.d.name,
        phone: state.d.phone,
        note: state.d.note,
        email: "",
        date: "",
        website: form.elements.website?.value || "",
        items: Object.entries(state.lines).map(([id, q]) => ({ flavour: id, grams: byId.get(id).grams, qty: q })),
      }),
      signal: ctrl.signal,
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
      state.done = { code: data.code, name: state.d.name.trim().split(" ")[0] || t("friend"), phone: state.d.phone };
      state.lines = {};
      state.d.note = "";
      save();
    } else if (res.status === 400 && data.errors) {
      state.errors = data.errors;
      if (data.errors.email || data.errors.date) state.error = t("err_generic");
    } else {
      state.error = data.error || t("err_generic");
    }
  } catch (err) {
    state.error = err.name === "AbortError" ? t("err_timeout") : t("err_network");
  } finally {
    clearTimeout(timer);
    state.sending = false;
  }
  renderAll();
  if (state.done) $(".done h3", sheetBody)?.focus();
}
sheetBody.addEventListener("input", (e) => {
  const name = e.target.name;
  if (!(name in state.d)) return;
  state.d[name] = e.target.value;
  if (state.errors[name]) {
    state.errors[name] = checks[name]?.(e.target.value) || "";
    const errEl = $(`#e-${name}`, sheetBody);
    if (errEl) errEl.textContent = state.errors[name];
    if (!state.errors[name]) e.target.removeAttribute("aria-invalid");
  }
  save();
});
sheetBody.addEventListener("submit", (e) => {
  e.preventDefault();
  send(e.target);
});

// ---------- Editing ----------
function setQty(id, n) {
  const before = qty(id);
  const q = clamp(n, 0, MAX_QTY);
  if (q) state.lines[id] = q;
  else delete state.lines[id];
  state.error = "";
  save();
  updateBuys();
  renderCounters();
  if (sheet.open) {
    renderBag({ drop: false });
    if (q > before) {
      const last = $$(".bag__p", bagEl).pop();
      last?.classList.add("is-drop");
    }
    renderSheet();
  }
}
function renderAll() {
  updateBuys();
  renderCounters();
  renderSheet();
}
document.addEventListener("click", (e) => {
  const el = e.target.closest("[data-add], [data-inc], [data-dec], [data-remove], [data-close]");
  if (!el) return;
  if (el.dataset.close !== undefined) {
    closeSheet();
    return;
  }
  const id = el.dataset.add || el.dataset.inc || el.dataset.dec || el.dataset.remove;
  const inSheet = Boolean(el.closest("[data-sheet]"));
  if (el.dataset.add || el.dataset.inc) {
    const chap = el.closest(".chap");
    const p = byId.get(id);
    const src = chap ? $(`[data-pack-img="${CSS.escape(id)}"]`, chap) || $(".chap__pack", chap) : null;
    setQty(id, qty(id) + 1);
    if (!inSheet) {
      fly(src, p);
      if (reduce) toast(fullName(p));
    }
    $(`[data-inc="${CSS.escape(id)}"]`, inSheet ? sheetBody : chaptersEl)?.focus({ preventScroll: true });
  } else if (el.dataset.dec) {
    setQty(id, qty(id) - 1);
    const scope = inSheet ? sheetBody : chaptersEl;
    ($(`[data-dec="${CSS.escape(id)}"]`, scope) || $(`[data-add="${CSS.escape(id)}"]`, scope))?.focus({ preventScroll: true });
  } else if (el.dataset.remove) {
    setQty(id, 0);
  }
});

// ---------- Live clock (Ed's local time) ----------
function tickClock() {
  let text;
  try {
    text = new Intl.DateTimeFormat(cfg.locale || "en", { hour: "2-digit", minute: "2-digit", hour12: false, timeZone: "Europe/Chisinau" }).format(new Date());
  } catch {
    text = new Date().toTimeString().slice(0, 5);
  }
  $$("[data-clock]").forEach((el) => (el.textContent = `${text} EET`));
}
tickClock();
setInterval(tickClock, 15000);

// ---------- Nav ----------
const nav = $("[data-nav]");
let lastY = window.scrollY;
window.addEventListener(
  "scroll",
  () => {
    const y = window.scrollY;
    nav.classList.toggle("is-solid", y > 40);
    if (y > 500 && y > lastY + 6 && !sheet.open) nav.classList.add("is-hidden");
    else if (y < lastY - 6 || y < 500) nav.classList.remove("is-hidden");
    lastY = y;
  },
  { passive: true }
);

// ---------- First render ----------
renderChapters();
renderCounters();
$$("[data-split]").forEach((el) => {
  const text = el.textContent.trim();
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${text
    .split(/\s+/)
    .map((w) => `<span class="w">${[...w].map((c) => `<span class="c">${esc(c)}</span>`).join("")}</span>`)
    .join(" ")}</span>`;
});

// The page colour follows the chapter on screen.
const root = document.documentElement;
function setMood(el) {
  root.style.setProperty("--page", el?.dataset.bg || "#0b0706");
  root.style.setProperty("--ink", el?.dataset.ink || "#f6e6cc");
  root.style.setProperty("--accent", el?.dataset.accent || "#e97f0e");
  root.classList.toggle("is-light", Boolean(el) && el.dataset.ink !== "#f6e6cc");
}

// ======================================================================
// 3D: hero pack, inspector, and (for the packshot images) still renders
// ======================================================================
const texts3d = { ingredients: B.ingredients_title, list: (B._ingredients || []).join(", "), soy: B.contains_soy, store: B.store, made: B.made };
const stripes = cfg.flavours.map((f) => f.band);
const heroProducts = [...groups.map((g) => g.items[0]), ...groups.map((g) => g.items[1]).filter(Boolean)];
let heroViewer = null;
let inspectViewer = null;
let inspectProduct = groups[0]?.items[0] || products[0];

const inspectProductsEl = $("[data-inspect-products]");
inspectProductsEl.innerHTML = products
  .map((p) => `<button type="button" data-inspect="${esc(p.id)}" aria-pressed="${p === inspectProduct}">${esc(p.box ? p.name : fullName(p))}</button>`)
  .join("");
inspectProductsEl.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-inspect]");
  if (!btn) return;
  inspectProduct = byId.get(btn.dataset.inspect);
  $$("[data-inspect]", inspectProductsEl).forEach((x) => x.setAttribute("aria-pressed", String(x === btn)));
  inspectViewer?.setProduct(inspectProduct);
  const fb = $("[data-inspect-fallback]");
  if (!inspectViewer) fb.src = packSrc(inspectProduct);
});
function markFace(n) {
  $$("[data-face]").forEach((x) => x.setAttribute("aria-pressed", String(Number(x.dataset.face) === n)));
}
$$("[data-face]").forEach((btn) =>
  btn.addEventListener("click", () => {
    inspectViewer?.face(Number(btn.dataset.face));
    markFace(Number(btn.dataset.face));
  })
);
$("[data-reset]").addEventListener("click", () => {
  inspectViewer?.reset();
  markFace(0);
});

const threeReady = import("./beta3d.js")
  .then(async (mod) => {
    const logo = await mod.loadAssets(B._logo);
    // Developer hook: /en/betatest?render-packs renders the packshots for public/img/packs.
    if (location.search.includes("render-packs")) {
      window.__packs = mod.renderShots(products, { meats: cfg.meats, logo, texts: texts3d, stripes });
      return;
    }
    const opts = { meats: cfg.meats, logo, texts: texts3d, stripes, reduce };
    heroViewer = mod.createViewer($("[data-hero-canvas]"), { ...opts, mode: "hero", fill: 0.68 });
    if (heroViewer) {
      heroViewer.setProduct(heroProducts[0], { animate: false });
      root.classList.add("has-3d");
      // Click the hero pack: it spins to the next flavour.
      let h = 0;
      $("[data-hero-canvas]").addEventListener("click", () => {
        h = (h + 1) % heroProducts.length;
        heroViewer.setProduct(heroProducts[h]);
      });
    }
    inspectViewer = mod.createViewer($("[data-inspect-canvas]"), { ...opts, mode: "inspect", fill: 0.7 });
    if (inspectViewer) {
      inspectViewer.setProduct(inspectProduct, { animate: false });
      inspectViewer.onTurn(() => markFace(inspectViewer.faceIndex === 3 ? 1 : inspectViewer.faceIndex));
    }
  })
  .catch(() => {});
// Without WebGL: still images.
threeReady.then(() => {
  if (!heroViewer) {
    const img = $("[data-hero-fallback]");
    img.src = packSrc(heroProducts[0]);
    img.hidden = false;
  }
  if (!inspectViewer) {
    const img = $("[data-inspect-fallback]");
    img.src = packSrc(inspectProduct);
    img.hidden = false;
  }
});

// ======================================================================
// Loader + motion
// ======================================================================
const loader = $("[data-loader]");
function runLoader() {
  return new Promise((resolve) => {
    if (reduce || sessionStorage.getItem("bigeds-seen")) {
      loader.remove();
      resolve();
      return;
    }
    const n = $("[data-loader-n]");
    const bar = $("[data-loader-bar]");
    const skullFill = $(".loader__fill");
    const t0 = performance.now();
    let real = 0;
    const done = Promise.all([document.fonts?.ready, threeReady, new Promise((r) => (document.readyState === "complete" ? r() : addEventListener("load", r, { once: true })))]);
    done.then(() => (real = 1));
    setTimeout(() => (real = 1), 4000);
    (function step(now) {
      const time = clamp((now - t0) / 1600);
      const p = Math.min(time, real ? 1 : 0.9);
      n.textContent = `${String(Math.round(p * 100)).padStart(3, "0")}%`;
      bar.style.transform = `scaleX(${p})`;
      // The skull fills with colour from the bottom up.
      skullFill.style.clipPath = `inset(${(1 - p) * 100}% 0 0 0)`;
      if (p >= 1) {
        try {
          sessionStorage.setItem("bigeds-seen", "1");
        } catch {
          /* ignore */
        }
        loader.classList.add("is-out");
        setTimeout(() => {
          loader.remove();
        }, 1100);
        setTimeout(resolve, 350);
        return;
      }
      requestAnimationFrame(step);
    })(t0);
  });
}

function initMotion() {
  const gsap = window.gsap;
  const ST = window.ScrollTrigger;
  // Chapter colours work even without GSAP.
  const chaps = $$(".chap", chaptersEl);
  const io = new IntersectionObserver(
    (entries) => {
      for (const en of entries) if (en.isIntersecting) setMood(en.target);
    },
    { rootMargin: "-45% 0px -45% 0px" }
  );
  chaps.forEach((c) => io.observe(c));
  new IntersectionObserver(
    (entries) => {
      for (const en of entries) if (en.isIntersecting) setMood(null);
    },
    { rootMargin: "-45% 0px -45% 0px" }
  ).observe($(".flavours__head"));
  $$(".hero, .how, .inspect, .ed, .faq, .foot").forEach((s) =>
    new IntersectionObserver(
      (entries) => {
        for (const en of entries) if (en.isIntersecting) setMood(null);
      },
      { rootMargin: "-45% 0px -45% 0px" }
    ).observe(s)
  );

  if (!gsap || !ST || reduce) {
    root.classList.add("is-static");
    return;
  }
  root.classList.add("is-motion");
  gsap.registerPlugin(ST);
  if (window.Lenis) {
    lenis = new window.Lenis({ lerp: 0.085, wheelMultiplier: 0.95 });
    lenis.on("scroll", ST.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    document.addEventListener("click", (e) => {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const target = a.getAttribute("href") === "#top" ? 0 : $(a.getAttribute("href"));
      if (target === null) return;
      e.preventDefault();
      if (sheet.open) sheet.close();
      lenis.scrollTo(target, { duration: 1.6, easing: (x) => 1 - Math.pow(1 - x, 4) });
    });
  }

  // Hero: the blurred field follows the pointer, slowly.
  if (finePointer) {
    const fx = gsap.quickTo(".hero__field", "--fx", { duration: 2.4, ease: "power3" });
    const fy = gsap.quickTo(".hero__field", "--fy", { duration: 2.4, ease: "power3" });
    window.addEventListener("pointermove", (e) => {
      fx((e.clientX / innerWidth - 0.5) * 30);
      fy((e.clientY / innerHeight - 0.5) * 20);
    });
  }
  gsap.to(".hero__copy", { yPercent: -10, opacity: 0.1, ease: "none", scrollTrigger: { trigger: ".hero", start: "20% top", end: "bottom top", scrub: true } });
  gsap.to(".hero__stage", { yPercent: -16, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });

  // Tapes drift and speed up with scroll.
  $$("[data-tape]").forEach((track) => {
    const dir = Number(track.dataset.dir || 1);
    const loop = gsap.fromTo(track, { xPercent: dir > 0 ? 0 : -50 }, { xPercent: dir > 0 ? -50 : 0, ease: "none", duration: 34, repeat: -1 });
    ST.create({ trigger: ".tapes", start: "top bottom", end: "bottom top", onUpdate: (self) => loop.timeScale(1 + Math.min(Math.abs(self.getVelocity()) / 260, 6)) });
  });

  // How to order: each giant word slides in from the side as you scroll.
  $$("[data-how]").forEach((row, i) => {
    gsap.fromTo($(".how__word", row), { xPercent: i % 2 ? 40 : -40, opacity: 0.15 }, { xPercent: 0, opacity: 1, ease: "power2.out", scrollTrigger: { trigger: row, start: "top 95%", end: "top 45%", scrub: 0.8 } });
    gsap.from([$(".how__n", row), $(".how__d", row)], { opacity: 0, y: 24, duration: 0.9, ease: "power3.out", stagger: 0.1, scrollTrigger: { trigger: row, start: "top 70%", once: true } });
  });

  // Split headings rise letter by letter.
  $$("[data-split]").forEach((el) =>
    gsap.from($$(".c", el), { yPercent: 110, rotate: 6, duration: 1.1, ease: "expo.out", stagger: 0.018, scrollTrigger: { trigger: el, start: "top 88%", once: true } })
  );

  // Chapters: the giant word slides across, the packs swing in, the info rises.
  chaps.forEach((chap) => {
    const range = { trigger: chap, start: "top bottom", end: "bottom top", scrub: true };
    gsap.fromTo($(".chap__word span", chap), { xPercent: 18 }, { xPercent: -42, ease: "none", scrollTrigger: range });
    const a = $(".chap__pack--a", chap);
    const b2 = $(".chap__pack--b", chap);
    if (a) gsap.fromTo(a, { yPercent: 30, rotate: -22, xPercent: -10 }, { yPercent: -14, rotate: -6, xPercent: 0, ease: "none", scrollTrigger: range });
    if (b2) gsap.fromTo(b2, { yPercent: 50, rotate: 26, xPercent: 12 }, { yPercent: -4, rotate: 10, xPercent: 0, ease: "none", scrollTrigger: range });
    $$(".chap__mini", chap).forEach((m, k) =>
      gsap.fromTo(m, { yPercent: 120, rotate: 0, opacity: 0 }, { yPercent: -10 - (k % 3) * 25, rotate: (k - 2.5) * 12, opacity: 1, ease: "back.out(1.4)", scrollTrigger: { trigger: chap, start: "top 75%", end: "top 15%", scrub: 0.6 } })
    );
    gsap.from($$(".chap__info > *", chap), { y: 40, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.07, scrollTrigger: { trigger: chap, start: "top 55%", once: true } });
    ST.create({ trigger: chap, start: "top 55%", once: true, onEnter: () => chap.classList.add("is-hot") });
  });

  // The stamp thumps down.
  gsap.fromTo("[data-stamp]", { scale: 2.4, opacity: 0, rotate: -18 }, { scale: 1, opacity: 1, rotate: -4, duration: 0.55, ease: "back.out(2.4)", scrollTrigger: { trigger: "[data-stamp]", start: "top 85%", once: true } });

  // Blocks rise in.
  ST.batch($$("main [data-in], .foot [data-in]").filter((el) => !el.closest(".hero")), {
    start: "top 92%",
    once: true,
    onEnter: (els) => gsap.from(els, { y: 36, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.08 }),
  });

  // Footer wordmark grows from below.
  gsap.fromTo(".foot__word", { yPercent: 40, scale: 0.92 }, { yPercent: 0, scale: 1, ease: "none", scrollTrigger: { trigger: ".foot", start: "top bottom", end: "bottom bottom", scrub: true } });
  gsap.fromTo(".foot__skull", { yPercent: 60, rotate: -8, opacity: 0 }, { yPercent: 0, rotate: 0, opacity: 1, ease: "none", scrollTrigger: { trigger: ".foot", start: "top bottom", end: "bottom bottom", scrub: true } });

  window.addEventListener("load", () => ST.refresh());
  document.fonts?.ready.then(() => ST.refresh());
}

function intro() {
  const gsap = window.gsap;
  if (!gsap || reduce) {
    root.classList.add("is-in");
    return;
  }
  gsap
    .timeline({ defaults: { ease: "expo.out" } })
    .from(".hero__field i", { scale: 0.4, opacity: 0, duration: 2.4, stagger: 0.15 }, 0)
    .from("[data-hero-skull]", { yPercent: -30, scale: 1.4, opacity: 0, filter: "blur(20px)", duration: 1.8 }, 0.1)
    .from("[data-hero-wordmark]", { yPercent: 25, opacity: 0, filter: "blur(16px)", duration: 1.8 }, 0.3)
    .from(".hero__stage", { yPercent: 18, opacity: 0, duration: 1.9 }, 0.45)
    .from(".hero [data-in]", { y: 30, opacity: 0, duration: 1.2, stagger: 0.12 }, 0.9)
    .from(".nav, .pull", { y: -30, opacity: 0, duration: 1.2, clearProps: "transform,opacity" }, 0.7);
  root.classList.add("is-in");
}

// Start: motion needs the deferred scripts (GSAP, Lenis), which have run by now.
const start = () => {
  initMotion();
  runLoader().then(intro);
};
if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", start, { once: true });
else start();
