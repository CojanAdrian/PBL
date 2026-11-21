// The redesigned page (/betatest).
// Hero window that opens on scroll, flavour cards that stack, a sideways story
// strip, a footer that is uncovered at the end, and an order that prints like a
// receipt. Products, prices and the order endpoint are the same as the main page.
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
const icon = (d, size = 20, sw = 2.4) =>
  `<svg viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="${sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${d}"/></svg>`;
const MINUS = icon("M5 12h14");
const PLUS = icon("M12 5v14M5 12h14");
const img = (name) => `${B._img}/${name}.webp`;

// ---------- Products ----------
const products = [...cfg.flavours, ...(cfg.boxes || [])];
const byId = new Map(products.map((p) => [p.id, p]));
// The same flavour in beef and chicken becomes one card with a choice of meat.
const groups = [];
for (const f of cfg.flavours) {
  let g = groups.find((x) => x.name === f.name);
  if (!g) groups.push((g = { name: f.name, items: [] }));
  g.items.push(f);
}
const box = (cfg.boxes || [])[0];
const fullName = (p) => p.fullName || p.name;
// Which photo, sticker and ingredient chips a flavour gets (by its product key).
const slugOf = (id) => (/texas|bbq/i.test(id) ? "texas" : /garlic|teriyaki/i.test(id) ? "garlic" : /honey|sriracha/i.test(id) ? "honey" : "");
const PHOTO = { texas: "texas", garlic: "garlic", honey: "honey" };

// ---------- State ----------
const state = { lines: {}, sel: {}, d: { name: "", phone: "", note: "" }, sending: false, error: "", errors: {}, done: null };
try {
  const saved = JSON.parse(store.get(STORE_KEY) || "null");
  if (saved) {
    for (const [id, q] of Object.entries(saved.lines || {})) if (byId.has(id) && q > 0) state.lines[id] = Math.min(MAX_QTY, Math.floor(q));
    for (const [name, id] of Object.entries(saved.sel || {})) if (byId.has(id)) state.sel[name] = id;
    Object.assign(state.d, saved.d || {});
  }
} catch {
  /* start fresh */
}
const save = () => store.set(STORE_KEY, JSON.stringify({ lines: state.lines, sel: state.sel, d: state.d }));
const qty = (id) => state.lines[id] || 0;
const selected = (g) => byId.get(state.sel[g.name]) || g.items[0];
function totals() {
  let count = 0;
  let cost = 0;
  for (const [id, q] of Object.entries(state.lines)) {
    count += q;
    cost += byId.get(id).price * q;
  }
  return { count, cost };
}

// ---------- Small drawings ----------
function barcodeSVG(text, { w = 120, h = 30, color = "currentColor" } = {}) {
  const units = [];
  for (const ch of `*${text}*`) {
    for (const bit of ch.charCodeAt(0).toString(2).padStart(8, "0")) units.push(bit === "1" ? 2 : 1);
    units.push(1);
  }
  const total = units.reduce((s, u) => s + u, 0);
  let x = 0;
  const rects = units
    .map((u, i) => {
      const r = i % 2 === 0 ? `<rect x="${((x / total) * w).toFixed(2)}" y="0" width="${((u / total) * w).toFixed(2)}" height="${h}"/>` : "";
      x += u;
      return r;
    })
    .join("");
  return `<g fill="${color}">${rects}</g>`;
}
const chili = `<svg viewBox="0 0 24 24" aria-hidden="true"><path class="stem" d="M15.5 6.5c.3-2 1.6-3.3 3.5-3.5"/><path class="body" d="M10.2 7.4c2.6-1.6 6-1.2 7.4 1.2 1.6 2.8-.2 6.6-3.6 9.4-2.9 2.4-6.6 3.6-9.6 3.4-.9 0-1-.9-.2-1.3 2.7-1.3 4.6-3.3 5.3-6 .5-2.1-.6-3.8.7-6.7z"/></svg>`;
function heatHTML(level) {
  const word = B[`heat_${Math.max(1, Math.min(5, level))}`];
  const chilies = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}">${chili}</i>`).join("");
  return `<span class="chilies" role="img" aria-label="${esc(b("heat_aria", { word }))}">${chilies}</span><span>${esc(word)}</span>`;
}
function actHTML(p, { tone = "fire" } = {}) {
  const q = qty(p.id);
  if (!q) return `<button class="btn btn--${tone} btn--lg btn--wide" type="button" data-add="${esc(p.id)}">${PLUS}<span>${esc(B.add)}</span><span class="btn__price">${esc(money(p.price))}</span></button>`;
  return `<div class="stepper" role="group" aria-label="${esc(fullName(p))}">
      <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
      <output aria-live="polite"><strong>${q}</strong><small>${esc(B.in_order)}</small></output>
      <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
    </div>`;
}

// ---------- Menu cards ----------
const cardsEl = $("[data-cards]");
const tint = (hex, k) => `color-mix(in srgb, ${hex} ${k}%, #110e0c)`;

function renderCards() {
  cardsEl.innerHTML = groups
    .map((g, gi) => {
      const p = selected(g);
      const slug = slugOf(p.id);
      const chips = (B[`flavour_${slug}_chips`] || "").split("|").filter(Boolean);
      const sticker = B[`flavour_${slug}_sticker`];
      const photo = PHOTO[slug] || "dry";
      return `<article class="card" data-card="${gi}" style="--acc:${esc(p.accent)};--bg:${tint(p.accent, 26)};--i:${gi}" aria-labelledby="card-${gi}">
        <div class="card__media">
          <div class="card__photo"><img src="${img(photo)}" srcset="${img(photo + "-sm")} 720w, ${img(photo)} 1600w" sizes="(min-width: 900px) 46vw, 92vw" alt="" loading="lazy" width="1600" height="2133"></div>
          <span class="card__note">${esc(B.photo_note)}</span>
          ${sticker ? `<span class="sticker" aria-hidden="true">${esc(sticker)}</span>` : ""}
          <div class="card__pack" data-pack>${pouchSVG(p, { label: t("pouch", { name: fullName(p) }) })}</div>
        </div>
        <div class="card__info">
          <p class="card__idx">0${gi + 1} <span>/ 0${groups.length}</span></p>
          <h3 class="card__name" id="card-${gi}">${esc(g.name)}</h3>
          ${p.tagline ? `<p class="card__tag">${esc(p.tagline)}</p>` : ""}
          <p class="card__notes" data-notes>${esc(p.notes)}</p>
          ${chips.length ? `<ul class="chips">${chips.map((c) => `<li>${esc(c)}</li>`).join("")}</ul>` : ""}
          <p class="heat">${heatHTML(p.heat)}</p>
          ${g.items.length > 1 ? `<div class="meat" role="radiogroup" aria-label="${esc(b("meat_aria", { name: g.name }))}" data-meats></div>` : ""}
          <div class="card__act" data-act></div>
        </div>
      </article>`;
    })
    .join("");
  groups.forEach((g, gi) => updateCard(gi, { pack: false }));
}

function updateCard(gi, { pack = true } = {}) {
  const g = groups[gi];
  const el = $(`[data-card="${gi}"]`, cardsEl);
  if (!el) return;
  const p = selected(g);
  if (pack) {
    $("[data-pack]", el).innerHTML = pouchSVG(p, { label: t("pouch", { name: fullName(p) }) });
    $("[data-notes]", el).textContent = p.notes;
  }
  const meats = $("[data-meats]", el);
  if (meats) {
    meats.innerHTML = g.items
      .map((item) => {
        const on = item.id === p.id;
        const n = qty(item.id);
        return `<button type="button" role="radio" aria-checked="${on}" tabindex="${on ? 0 : -1}" data-pick="${esc(item.id)}" data-group="${gi}">
            <strong>${esc(item.meatName)}</strong><span>${esc(money(item.price))}</span>${n ? `<b aria-label="${n} ${esc(B.in_order)}">${n}</b>` : ""}
          </button>`;
      })
      .join("");
  }
  $("[data-act]", el).innerHTML = actHTML(p);
}

// ---------- Taster box ----------
const tasterEl = $("[data-taster]");
function renderTaster() {
  if (!box) {
    tasterEl.hidden = true;
    return;
  }
  const fan = cfg.flavours.slice(0, 6).map((f, i) => `<span class="fan__p" style="--i:${i}">${pouchSVG(f, { mini: true })}</span>`).join("");
  tasterEl.innerHTML = `<div class="wrap taster__grid">
      <div class="taster__info">
        <p class="kicker">${esc(B.taster_kicker)}</p>
        <h2 id="taster-title"><span data-line>${esc(B.taster_title)}</span></h2>
        <p class="taster__name">${esc(box.name)} · ${esc(fmtGrams(box.grams))}</p>
        <p class="taster__notes">${esc(box.notes || box.tagline)}</p>
        <div class="taster__act" data-taster-act></div>
      </div>
      <div class="taster__art" aria-hidden="true">
        <div class="taster__photo"><img src="${img("taster")}" srcset="${img("taster-sm")} 720w, ${img("taster")} 1600w" sizes="(min-width: 900px) 34vw, 70vw" alt="" loading="lazy" width="1600" height="2133"></div>
        <div class="fan">${fan}</div>
        <div class="taster__box" data-box>${pouchSVG(box, { label: box.name })}</div>
      </div>
    </div>`;
  updateTaster();
}
function updateTaster() {
  const act = $("[data-taster-act]", tasterEl);
  if (act) act.innerHTML = actHTML(box, { tone: "cream" });
}

// Three packs for the "brought to you" panel.
const packsEl = $("[data-packs]");
if (packsEl) {
  const pick = groups.map((g, i) => g.items[i % g.items.length]).slice(0, 3);
  packsEl.innerHTML = pick.map((p, i) => `<span class="trio__p" style="--i:${i}">${pouchSVG(p)}</span>`).join("");
}

// ---------- Ticket (floating) ----------
const ticket = $("[data-ticket]");
let lastCount = 0;
function renderTicket() {
  const { count, cost } = totals();
  ticket.hidden = count === 0;
  document.body.classList.toggle("has-ticket", count > 0);
  $("[data-ticket-packs]").textContent = packs(count);
  $("[data-ticket-total]").textContent = money(cost);
  const badge = $("[data-count]");
  badge.hidden = count === 0;
  badge.textContent = String(count);
  if (count > lastCount && lastCount > 0 && !reduce) {
    ticket.classList.remove("is-bump");
    void ticket.offsetWidth;
    ticket.classList.add("is-bump");
  }
  lastCount = count;
}

// The pack drops into the order ticket.
function fly(fromEl) {
  if (reduce || !fromEl || ticket.hidden) return;
  const from = fromEl.getBoundingClientRect();
  const to = ticket.getBoundingClientRect();
  if (!from.width || from.bottom < 0 || from.top > innerHeight) return;
  const node = document.createElement("div");
  node.className = "fly";
  node.innerHTML = fromEl.innerHTML;
  Object.assign(node.style, { left: `${from.left}px`, top: `${from.top}px`, width: `${from.width}px` });
  document.body.appendChild(node);
  const dx = to.left + 40 - from.left - from.width / 2;
  const dy = to.top + to.height / 2 - from.top - from.height / 2;
  node
    .animate(
      [
        { transform: "translate(0,0) scale(1) rotate(0deg)", opacity: 1 },
        { transform: `translate(${dx * 0.45}px, ${dy * 0.45 - 120}px) scale(0.55) rotate(-18deg)`, opacity: 1, offset: 0.5 },
        { transform: `translate(${dx}px, ${dy}px) scale(0.12) rotate(-40deg)`, opacity: 0.3 },
      ],
      { duration: 650, easing: "cubic-bezier(0.45, 0, 0.55, 1)" }
    )
    .finished.finally(() => node.remove());
}

// ---------- Order receipt ----------
const sheet = $("[data-sheet]");
const sheetBody = $("[data-sheet-body]");
let printed = new Set();

function lineHTML(id, q) {
  const p = byId.get(id);
  const sub = p.box ? fmtGrams(p.grams) : `${p.meatName} · ${fmtGrams(p.grams)}`;
  return `<li class="r-line${printed.has(id) ? "" : " is-new"}">
      <p class="r-line__top"><span class="r-name">${esc(p.name)}</span><span class="r-dots" aria-hidden="true"></span><span class="r-sum">${esc(money(p.price * q))}</span></p>
      <div class="r-line__sub">
        <span>${esc(sub)} · ${esc(money(p.price))}</span>
        <div class="stepper stepper--sm" role="group" aria-label="${esc(fullName(p))}">
          <button type="button" data-dec="${esc(id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
          <output aria-live="polite">${q}</output>
          <button type="button" data-inc="${esc(id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
        </div>
        <button class="r-remove" type="button" data-remove="${esc(id)}" aria-label="${esc(b("remove", { name: fullName(p) }))}">${icon("M6 6l12 12M18 6L6 18", 16)}</button>
      </div>
    </li>`;
}

function renderSheet() {
  if (!sheet.open) return;
  const now = new Date();
  $("[data-receipt-date]").textContent = `${now.toLocaleDateString(cfg.locale)} · ${now.toLocaleTimeString(cfg.locale, { hour: "2-digit", minute: "2-digit" })}`;
  if (state.done) {
    const d = state.done;
    sheetBody.innerHTML = `<div class="r-done">
        <p class="r-done__kicker">${esc(B.done_kicker)}</p>
        <h3 tabindex="-1">${esc(b("done_title", { name: d.name }))}</h3>
        <p>${esc(b("done_lead", { phone: d.phone }))}</p>
        <div class="r-code">
          <small>${esc(B.done_code)}</small>
          <strong>${esc(d.code)}</strong>
          <svg viewBox="0 0 220 44" aria-hidden="true">${barcodeSVG(d.code, { w: 220, h: 44 })}</svg>
        </div>
        <button class="btn btn--fire btn--lg btn--wide" type="button" data-close>${esc(B.done_close)}</button>
      </div>`;
    return;
  }
  const ids = Object.keys(state.lines);
  if (!ids.length) {
    sheetBody.innerHTML = `<p class="r-empty">${esc(B.sheet_empty)}</p>`;
    printed = new Set();
    return;
  }
  const { cost } = totals();
  const err = (k) => esc(state.errors[k] || "");
  const active = document.activeElement;
  const keepFocus = active?.closest?.("[data-sheet]") && active.name;
  sheetBody.innerHTML = `
    <ul class="r-lines">${ids.map((id) => lineHTML(id, state.lines[id])).join("")}</ul>
    <p class="r-total"><span>${esc(B.total)}</span><strong>${esc(money(cost))}</strong></p>
    ${state.errors.items ? `<p class="r-err" role="alert">${err("items")}</p>` : ""}
    <form class="r-form" novalidate data-form>
      <h3>${esc(B.details)}</h3>
      <div class="field">
        <label for="b-name">${esc(B.name)}</label>
        <input class="input" id="b-name" name="name" autocomplete="name" maxlength="80" required value="${esc(state.d.name)}" ${state.errors.name ? 'aria-invalid="true"' : ""} aria-describedby="e-name">
        <p class="err" id="e-name">${err("name")}</p>
      </div>
      <div class="field">
        <label for="b-phone">${esc(B.phone)}</label>
        <input class="input" id="b-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+373 …" maxlength="30" required value="${esc(state.d.phone)}" ${state.errors.phone ? 'aria-invalid="true"' : ""} aria-describedby="e-phone">
        <p class="err" id="e-phone">${err("phone")}</p>
      </div>
      <div class="field">
        <label for="b-note">${esc(B.note)}</label>
        <textarea class="input" id="b-note" name="note" rows="2" maxlength="500" placeholder="${esc(B.note_ph)}">${esc(state.d.note)}</textarea>
      </div>
      <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
      ${state.error ? `<p class="r-err" role="alert">${esc(state.error)}</p>` : ""}
      <button class="btn btn--fire btn--lg btn--wide" type="submit" ${state.sending ? "disabled" : ""}>${state.sending ? `<span class="spin" aria-hidden="true"></span>` : ""}<span>${esc(state.sending ? B.sending : B.send)} · ${esc(money(cost))}</span></button>
      <p class="r-pay">${esc(B.sheet_pay)}</p>
    </form>`;
  printed = new Set(ids);
  if (keepFocus) $(`[name="${keepFocus}"]`, sheetBody)?.focus();
}

let lenis = null;
function openSheet() {
  if (!sheet.open) {
    printed = new Set(Object.keys(state.lines));
    sheet.showModal();
    lenis?.stop();
  }
  renderSheet();
  (state.done ? $(".r-done h3", sheetBody) : $(".receipt__close", sheet))?.focus();
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
$$("[data-open-order]").forEach((btn) => btn.addEventListener("click", openSheet));

// ---------- Sending ----------
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
  if (state.done) {
    $(".r-done", sheetBody)?.classList.add("is-printing");
    $(".r-done h3", sheetBody)?.focus();
  }
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

// ---------- Editing the order ----------
function setQty(id, n) {
  const q = Math.max(0, Math.min(MAX_QTY, n));
  if (q) state.lines[id] = q;
  else delete state.lines[id];
  state.error = "";
  save();
  const gi = groups.findIndex((g) => g.items.some((i) => i.id === id));
  if (gi >= 0) updateCard(gi, { pack: false });
  if (box && id === box.id) updateTaster();
  renderTicket();
  renderSheet();
}
function renderAll() {
  groups.forEach((g, gi) => updateCard(gi, { pack: false }));
  if (box) updateTaster();
  renderTicket();
  renderSheet();
}

document.addEventListener("click", (e) => {
  const el = e.target.closest("[data-add], [data-inc], [data-dec], [data-remove], [data-pick], [data-close]");
  if (!el) return;
  if (el.dataset.close !== undefined) return closeSheet();
  if (el.dataset.pick) {
    const gi = Number(el.dataset.group);
    const g = groups[gi];
    if (state.sel[g.name] === el.dataset.pick) return;
    state.sel[g.name] = el.dataset.pick;
    save();
    const card = $(`[data-card="${gi}"]`, cardsEl);
    const pack = $("[data-pack]", card);
    updateCard(gi);
    // The new pack drops into place.
    if (!reduce) pack.animate([{ transform: "translateY(-30px) rotate(-14deg)", opacity: 0 }, { transform: "none", opacity: 1 }], { duration: 500, easing: "cubic-bezier(.3,1.4,.5,1)", composite: "add" });
    $(`[data-pick="${CSS.escape(el.dataset.pick)}"]`, card)?.focus({ preventScroll: true });
    return;
  }
  const id = el.dataset.add || el.dataset.inc || el.dataset.dec || el.dataset.remove;
  const inSheet = Boolean(el.closest("[data-sheet]"));
  const scope = inSheet ? sheetBody : el.closest(".card, .taster") || document;
  if (el.dataset.add || el.dataset.inc) {
    setQty(id, qty(id) + 1);
    if (!inSheet) {
      fly(el.closest(".card")?.querySelector("[data-pack]") || el.closest(".taster")?.querySelector("[data-box]"));
      if (reduce) toast(fullName(byId.get(id)));
    }
    $(`[data-inc="${CSS.escape(id)}"]`, scope)?.focus({ preventScroll: true });
  } else if (el.dataset.dec) {
    setQty(id, qty(id) - 1);
    ($(`[data-dec="${CSS.escape(id)}"]`, scope) || $(`[data-add="${CSS.escape(id)}"]`, scope))?.focus({ preventScroll: true });
  } else if (el.dataset.remove) {
    setQty(id, 0);
  }
});
// Arrow keys move between beef and chicken.
cardsEl.addEventListener("keydown", (e) => {
  const radio = e.target.closest("[data-pick]");
  const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
  if (!radio || !step) return;
  e.preventDefault();
  const all = $$("[data-pick]", radio.parentElement);
  all[(all.indexOf(radio) + step + all.length) % all.length].click();
});

// ---------- First render ----------
renderCards();
renderTaster();
renderTicket();
$$("[data-barcode]").forEach((svg) => (svg.innerHTML = barcodeSVG(svg.dataset.barcode)));

// ---------- Nav ----------
const nav = $("[data-nav]");
let lastY = window.scrollY;
window.addEventListener(
  "scroll",
  () =>
    requestAnimationFrame(() => {
      const y = window.scrollY;
      nav.classList.toggle("is-solid", y > 40);
      if (y > 400 && y > lastY + 4 && !nav.contains(document.activeElement)) nav.classList.add("is-hidden");
      else if (y < lastY - 4 || y <= 400) nav.classList.remove("is-hidden");
      lastY = y;
    }),
  { passive: true }
);

// ---------- Motion ----------
function splitChars(el) {
  const text = el.textContent.trim();
  const words = text.split(/\s+/).map((w) => `<span class="w">${[...w].map((c) => `<span class="c">${esc(c)}</span>`).join("")}</span>`);
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${words.join(" ")}</span>`;
}
$$("[data-split]").forEach(splitChars);
$$("[data-line]").forEach((el) => {
  el.innerHTML = `<span class="mask"><span class="mask__in">${el.innerHTML}</span></span>`;
});

function initMotion() {
  const gsap = window.gsap;
  const ST = window.ScrollTrigger;
  const root = document.documentElement;
  if (!gsap || !ST || reduce) {
    root.classList.add("is-static");
    return;
  }
  root.classList.add("is-motion");
  gsap.registerPlugin(ST);

  if (window.Lenis) {
    lenis = new window.Lenis({ lerp: 0.1 });
    lenis.on("scroll", ST.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    document.addEventListener("click", (e) => {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const target = a.getAttribute("href") === "#top" ? 0 : $(a.getAttribute("href"));
      if (target === null) return;
      e.preventDefault();
      lenis.scrollTo(target, { offset: 0, duration: 1.4 });
    });
  }

  // Hero intro: letters rise, then the rest fades in.
  const intro = gsap.timeline({ defaults: { ease: "power4.out" }, delay: 0.1 });
  intro
    .from(".hero__t .c", { yPercent: 115, duration: 1.2, stagger: 0.025 }, 0)
    .from("[data-window]", { clipPath: "inset(50% 50% 50% 50% round 6px)", duration: 1.4, ease: "expo.inOut" }, 0.05)
    .from("[data-window] img", { scale: 1.5, duration: 1.8, ease: "expo.out" }, 0.1)
    .from(".hero [data-in]", { y: 24, opacity: 0, duration: 0.9, stagger: 0.12 }, 0.6)
    .from(".nav__in", { y: -20, opacity: 0, duration: 0.8, clearProps: "transform,opacity" }, 0.5);
  root.classList.add("is-intro");

  const mm = gsap.matchMedia();
  // Large screens: the hero window opens to full screen and the two lines part.
  mm.add("(min-width: 900px)", () => {
    const tl = gsap.timeline({ scrollTrigger: { trigger: "[data-hero]", start: "top top", end: "bottom bottom", scrub: 0.8 } });
    tl.to("[data-window]", { clipPath: "inset(0% 0% 0% 0% round 0px)", ease: "power2.inOut", duration: 1 }, 0)
      .to("[data-window] img", { scale: 1, ease: "none", duration: 1 }, 0)
      .to(".hero__t--1", { xPercent: -60, opacity: 0, ease: "power2.in", duration: 0.7 }, 0)
      .to(".hero__t--2", { xPercent: 60, opacity: 0, ease: "power2.in", duration: 0.7 }, 0)
      .to(".hero__kicker, .hero__bottom, .hero__scroll", { opacity: 0, y: -20, duration: 0.3 }, 0)
      .fromTo("[data-after]", { opacity: 0, y: 60 }, { opacity: 1, y: 0, duration: 0.35 }, 0.65);

    // Menu cards: the one underneath shrinks and darkens as the next slides over it.
    // The cards are sticky, so their positions are worked out from the (non-sticky)
    // stack: where each card would sit if it scrolled normally.
    const cards = $$(".card", cardsEl);
    const naturalTop = (i) => {
      const gap = parseFloat(getComputedStyle(cardsEl).rowGap) || 0;
      let y = cardsEl.getBoundingClientRect().top + window.scrollY;
      for (let k = 0; k < i; k++) y += cards[k].offsetHeight + gap;
      return y;
    };
    cards.forEach((card, i) => {
      if (i === cards.length - 1) return;
      gsap.fromTo(card, { scale: 1, filter: "brightness(1)" }, {
        scale: 0.92,
        filter: "brightness(0.45)",
        ease: "none",
        scrollTrigger: { start: () => naturalTop(i + 1) - window.innerHeight, end: () => naturalTop(i + 1) - window.innerHeight * 0.25, scrub: true, invalidateOnRefresh: true },
      });
    });

    // Ed's kitchen: the strip moves sideways while the section is pinned.
    const track = $("[data-track]");
    const distance = () => Math.max(0, track.scrollWidth - window.innerWidth);
    gsap.to(track, {
      x: () => -distance(),
      ease: "none",
      scrollTrigger: { trigger: "[data-story]", pin: ".story__pin", start: "top top", end: () => `+=${distance()}`, scrub: 0.8, invalidateOnRefresh: true, anticipatePin: 1 },
    });
  });
  mm.add("(max-width: 899px)", () => {
    gsap.fromTo("[data-window] img", { scale: 1.15 }, { scale: 1, ease: "none", scrollTrigger: { trigger: "[data-window]", start: "top bottom", end: "bottom top", scrub: true } });
  });

  // The flavour band speeds up and leans with scroll speed.
  const band = $("[data-band]");
  if (band) {
    const loop = gsap.to(band, { xPercent: -50, ease: "none", duration: 40, repeat: -1 });
    const skew = gsap.quickTo(band, "skewX", { duration: 0.4, ease: "power3" });
    ST.create({
      trigger: ".band",
      start: "top bottom",
      end: "bottom top",
      onUpdate: (self) => {
        const v = self.getVelocity();
        loop.timeScale(1 + Math.min(Math.abs(v) / 300, 6));
        skew(gsap.utils.clamp(-8, 8, v / -250));
      },
    });
  }

  // Headings slide up from behind a mask; blocks rise in.
  $$("[data-line] .mask__in").forEach((el) =>
    gsap.from(el, { yPercent: 110, duration: 1.1, ease: "power4.out", scrollTrigger: { trigger: el.closest("[data-line]"), start: "top 88%", once: true } })
  );
  ST.batch($$("main [data-rise]"), {
    start: "top 90%",
    once: true,
    onEnter: (els) => gsap.from(els, { y: 40, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.08 }),
  });

  // Cards: the photo drifts inside its frame, the sticker slaps on, the chillies light up.
  $$(".card", cardsEl).forEach((card) => {
    gsap.fromTo($(".card__photo img", card), { yPercent: -6, scale: 1.12 }, { yPercent: 6, scale: 1.12, ease: "none", scrollTrigger: { trigger: card, start: "top bottom", end: "bottom top", scrub: true } });
    const sticker = $(".sticker", card);
    if (sticker) gsap.from(sticker, { scale: 2.2, opacity: 0, rotate: -40, duration: 0.6, ease: "back.out(2)", scrollTrigger: { trigger: card, start: "top 60%", once: true } });
    gsap.from($("[data-pack]", card), { y: 80, rotate: 18, opacity: 0, duration: 1, ease: "power3.out", scrollTrigger: { trigger: card, start: "top 65%", once: true } });
    ST.create({ trigger: card, start: "top 60%", once: true, onEnter: () => card.classList.add("is-hot") });
  });

  // Taster: the packs fan out of the box.
  if (box) {
    gsap.fromTo(".fan__p", { xPercent: 0, yPercent: 40, rotate: 0, opacity: 0 }, {
      xPercent: (i) => (i - 2.5) * 62,
      yPercent: (i) => -30 - Math.abs(i - 2.5) * -8,
      rotate: (i) => (i - 2.5) * 11,
      opacity: 1,
      ease: "back.out(1.4)",
      stagger: 0.04,
      scrollTrigger: { trigger: ".taster__art", start: "top 85%", end: "center 55%", scrub: 0.6 },
    });
  }

  // Story photos drift a little inside their frames.
  $$("[data-parallax]").forEach((el) => gsap.fromTo(el, { scale: 1.15 }, { scale: 1.02, ease: "none", scrollTrigger: { trigger: "[data-story]", start: "top bottom", end: "bottom top", scrub: true } }));

  // Footer wordmark grows as it is uncovered.
  gsap.fromTo(".foot__word", { yPercent: 30, scale: 0.9 }, { yPercent: 0, scale: 1, ease: "none", scrollTrigger: { trigger: ".foot", start: "top bottom", end: "bottom bottom", scrub: true } });

  // Packs on the cards lean towards the pointer.
  if (finePointer) {
    $$(".card__media", cardsEl).forEach((media) => {
      const pack = $("[data-pack]", media);
      const rx = gsap.quickTo(pack, "rotateX", { duration: 0.8, ease: "power3.out" });
      const ry = gsap.quickTo(pack, "rotateY", { duration: 0.8, ease: "power3.out" });
      media.addEventListener("pointermove", (e) => {
        const r = media.getBoundingClientRect();
        ry(((e.clientX - r.left) / r.width - 0.5) * 36);
        rx(((e.clientY - r.top) / r.height - 0.5) * -24);
      });
      media.addEventListener("pointerleave", () => {
        rx(0);
        ry(0);
      });
    });
  }

  window.addEventListener("load", () => ST.refresh());
  document.fonts?.ready.then(() => ST.refresh());
}
initMotion();
if (location.hash.length > 1) window.addEventListener("load", () => setTimeout(() => (lenis ? lenis.scrollTo(location.hash, { immediate: true }) : $(location.hash)?.scrollIntoView()), 100), { once: true });
