// The redesigned page (/betatest).
// Flavour chapters with a 3D pack you can turn, a taster crate that opens as you
// scroll, an order that prints like a butcher's receipt, and smoke in the hero.
// Products, prices and the order endpoint are the same as the main page.
import { $, $$, esc, store, toast } from "./ui.js";
import { pouchSVG, prepareMenu, fmtGrams } from "./pouch.js";
import { t, packs } from "./i18n.js";
import { initSmoke } from "./beta-smoke.js";

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
const TURN = icon("M3 12a9 9 0 0 1 15.5-6.2L21 8M21 3v5h-5M21 12a9 9 0 0 1-15.5 6.2L3 16M3 21v-5h5", 18, 2.2);

// ---------- Products ----------
const products = [...cfg.flavours, ...(cfg.boxes || [])];
const byId = new Map(products.map((p) => [p.id, p]));
// The same flavour in beef and chicken becomes one chapter with a choice of meat.
const groups = [];
for (const f of cfg.flavours) {
  let g = groups.find((x) => x.name === f.name);
  if (!g) groups.push((g = { name: f.name, items: [] }));
  g.items.push(f);
}
const box = (cfg.boxes || [])[0];
const fullName = (p) => p.fullName || p.name;

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

// ---------- Drawings ----------
// Ingredient illustrations that float around each pack.
const ART = {
  chili: `<svg viewBox="0 0 64 64"><path d="M38 12c-2-5-7-7-11-5" fill="none" stroke="#4c7a3a" stroke-width="4" stroke-linecap="round"/><path d="M27 10c9-1 16 5 16 14 0 14-12 28-31 32-3 1-4-2-1-3 12-5 17-15 16-27-1-7-4-11 0-16z" fill="#c8281c"/><path d="M30 16c3 3 4 8 3 14" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="2.5" stroke-linecap="round"/></svg>`,
  garlic: `<svg viewBox="0 0 64 64"><path d="M32 6c1 6 4 9 9 13 8 6 13 13 11 23-2 10-10 16-20 16s-18-6-20-16c-2-10 3-17 11-23 5-4 8-7 9-13z" fill="#f4ecdf"/><path d="M32 22v34M24 25c-5 9-5 22 2 31M40 25c5 9 5 22-2 31" fill="none" stroke="#c9b79c" stroke-width="2.2" stroke-linecap="round"/><path d="M29 5c1-2 5-2 6 0" fill="none" stroke="#c9b79c" stroke-width="2.2" stroke-linecap="round"/></svg>`,
  honey: `<svg viewBox="0 0 64 64"><path d="M32 6C26 20 14 30 14 42a18 18 0 0 0 36 0C50 30 38 20 32 6z" fill="#e9a325"/><path d="M24 40c0 6 4 10 9 11" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round"/></svg>`,
  soy: `<svg viewBox="0 0 64 64"><path d="M32 6C26 20 14 30 14 42a18 18 0 0 0 36 0C50 30 38 20 32 6z" fill="#3a1f12"/><path d="M24 40c0 6 4 10 9 11" fill="none" stroke="#fff" stroke-opacity=".3" stroke-width="3" stroke-linecap="round"/></svg>`,
  flame: `<svg viewBox="0 0 64 64"><path d="M32 4c4 12 18 18 18 34a18 18 0 0 1-36 0c0-8 4-13 8-16 0 6 2 10 6 12-2-12 0-22 4-30z" fill="#ff6a2b"/><path d="M32 30c2 6 9 9 9 17a9 9 0 0 1-18 0c0-5 3-8 5-9 0 3 1 5 3 6-1-6 0-10 1-14z" fill="#ffc15a"/></svg>`,
  pepper: `<svg viewBox="0 0 64 64"><g fill="#2a211c"><circle cx="20" cy="24" r="9"/><circle cx="42" cy="20" r="8"/><circle cx="34" cy="42" r="10"/></g><g fill="#fff" fill-opacity=".25"><circle cx="17" cy="21" r="2.5"/><circle cx="39" cy="17" r="2.2"/><circle cx="30" cy="38" r="2.8"/></g></svg>`,
  sesame: `<svg viewBox="0 0 64 64"><g fill="#efe1c4"><ellipse cx="20" cy="22" rx="5" ry="9" transform="rotate(-30 20 22)"/><ellipse cx="42" cy="26" rx="5" ry="9" transform="rotate(25 42 26)"/><ellipse cx="30" cy="44" rx="5" ry="9" transform="rotate(-70 30 44)"/></g></svg>`,
  sugar: `<svg viewBox="0 0 64 64"><rect x="14" y="16" width="32" height="32" rx="5" fill="#a86a32" transform="rotate(-12 30 32)"/><rect x="34" y="34" width="18" height="18" rx="3" fill="#c48646" transform="rotate(18 43 43)"/></svg>`,
};
const artFor = (id) =>
  /garlic|teriyaki/i.test(id) ? ["garlic", "soy", "sesame", "garlic"] : /honey|sriracha/i.test(id) ? ["honey", "chili", "flame", "chili"] : /bbq|texas/i.test(id) ? ["flame", "pepper", "sugar", "pepper"] : ["flame", "pepper", "honey", "garlic"];

function barcodeSVG(text, { w = 120, h = 30, color = "currentColor" } = {}) {
  const units = [];
  for (const ch of `*${text}*`) {
    const bits = ch.charCodeAt(0).toString(2).padStart(8, "0");
    for (const bit of bits) units.push(bit === "1" ? 2 : 1);
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

// The back of a pack: ingredients, storage, weight, a barcode.
function packBackSVG(p) {
  const list = (B._ingredients || []).join(", ");
  // Wrap the ingredient list onto short lines.
  const words = `${list}.`.split(" ");
  const lines = [""];
  for (const wd of words) {
    if ((lines[lines.length - 1] + " " + wd).trim().length > 24) lines.push(wd);
    else lines[lines.length - 1] = (lines[lines.length - 1] + " " + wd).trim();
  }
  const body = "M16 30Q16 16 30 16H170Q184 16 184 30V264Q184 280 168 280H32Q16 280 16 264Z";
  return `<svg class="pouch" viewBox="0 0 200 296" aria-hidden="true" focusable="false">
    <path d="${body}" fill="${p.pouchFill || "url(#pouchBody)"}" stroke="rgba(255,255,255,.1)" stroke-width="1.5"/>
    <path d="M16 30Q16 16 30 16H170Q184 16 184 30V48H16Z" fill="${p.pouchCap || "#0a0908"}"/>
    <path d="M24 30H176M24 38H176" stroke="rgba(255,255,255,.1)" stroke-width="1.5" stroke-linecap="round"/>
    <rect x="30" y="62" width="140" height="150" rx="6" fill="#f4ecdf"/>
    <text x="42" y="84" class="pouch__flavour" font-size="15" fill="#1c1814" letter-spacing="1">${esc(B.pack_ingredients.toUpperCase())}</text>
    ${lines.slice(0, 6).map((l, i) => `<text x="42" y="${104 + i * 13}" font-size="9.5" font-weight="600" fill="#3a322b">${esc(l)}</text>`).join("")}
    <text x="42" y="${116 + Math.min(lines.length, 6) * 13}" font-size="9.5" font-weight="800" fill="#b3261b">${esc(B.contains_soy)}</text>
    <text x="42" y="200" font-size="8" font-weight="700" fill="#5a4f45">${esc(B.pack_store)}</text>
    <g transform="translate(40 224)">${barcodeSVG(p.id.toUpperCase().slice(0, 10), { w: 78, h: 30, color: "#f4ecdf" })}</g>
    <text x="160" y="240" text-anchor="end" font-size="9" font-weight="800" fill="rgba(255,255,255,.7)" letter-spacing="1">NET WT.</text>
    <text x="160" y="254" text-anchor="end" font-size="11" font-weight="800" fill="#fff" letter-spacing="1">${esc(fmtGrams(p.grams).toUpperCase())}</text>
    <path d="${body}" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

const chiliSmall = `<svg viewBox="0 0 24 24" aria-hidden="true"><path class="stem" d="M15.5 6.5c.3-2 1.6-3.3 3.5-3.5"/><path class="body" d="M10.2 7.4c2.6-1.6 6-1.2 7.4 1.2 1.6 2.8-.2 6.6-3.6 9.4-2.9 2.4-6.6 3.6-9.6 3.4-.9 0-1-.9-.2-1.3 2.7-1.3 4.6-3.3 5.3-6 .5-2.1-.6-3.8.7-6.7z"/></svg>`;
function heatHTML(level) {
  const word = B[`heat_${Math.max(1, Math.min(5, level))}`];
  const chilies = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}">${chiliSmall}</i>`).join("");
  return `<span class="chilies" role="img" aria-label="${esc(b("heat_aria", { word }))}">${chilies}</span><span class="heat__word" aria-hidden="true">${esc(word)}</span>`;
}

function actHTML(p, { light = false } = {}) {
  const q = qty(p.id);
  if (!q) return `<button class="btn ${light ? "btn--cream" : "btn--red"} btn--lg btn--wide" type="button" data-add="${esc(p.id)}">${PLUS}<span>${esc(B.add)}</span><span class="btn__price">${esc(money(p.price))}</span></button>`;
  return `<div class="stepper" role="group" aria-label="${esc(fullName(p))}">
      <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
      <output aria-live="polite"><strong>${q}</strong><small>${esc(B.in_order)}</small></output>
      <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
    </div>`;
}

// ---------- Flavour chapters ----------
const chaptersEl = $("[data-chapters]");
const tint = (hex, k) => `color-mix(in srgb, ${hex} ${k}%, #110d0b)`;

function renderChapters() {
  chaptersEl.innerHTML = groups
    .map((g, gi) => {
      const p = selected(g);
      const art = artFor(p.id);
      return `<article class="chap" data-chap="${gi}" style="--acc:${esc(p.accent)};--bg:${tint(p.accent, 30)};--bg2:${tint(p.accent, 12)}" aria-labelledby="chap-${gi}">
        <div class="chap__bg" aria-hidden="true"></div>
        <p class="chap__word" aria-hidden="true"><span>${esc(g.name)}</span></p>
        <div class="chap__in">
          <div class="chap__stage">
            <div class="chap__art" aria-hidden="true">${art.map((a, i) => `<span class="art art--${i}" data-depth="${[0.6, 1.2, 0.9, 1.6][i]}">${ART[a]}</span>`).join("")}</div>
            <div class="pack" data-pack>
              <div class="pack__swing">
                <div class="pack__flip" data-flip>
                  <div class="pack__face pack__front" data-front>${pouchSVG(p, { label: t("pouch", { name: fullName(p) }) })}</div>
                  <div class="pack__face pack__back" data-back>${packBackSVG(p)}</div>
                </div>
              </div>
              <div class="pack__shadow"></div>
            </div>
            <button class="chap__turn" type="button" data-turn aria-pressed="false">${TURN}<span>${esc(B.turn)}</span></button>
          </div>
          <div class="chap__info">
            <p class="chap__idx"><span>0${gi + 1}</span> / 0${groups.length}</p>
            <h3 class="chap__name" id="chap-${gi}">${esc(g.name)}</h3>
            <p class="chap__notes" data-notes>${esc(p.notes || p.tagline)}</p>
            <p class="chap__heat" data-heat>${heatHTML(p.heat)}</p>
            ${g.items.length > 1 ? `<div class="meat" role="radiogroup" aria-label="${esc(b("meat_aria", { name: g.name }))}" data-meats></div>` : ""}
            <div class="chap__act" data-act></div>
          </div>
        </div>
      </article>`;
    })
    .join("");
  groups.forEach((g, gi) => updateChapter(gi, { pack: false }));
}

function updateChapter(gi, { pack = true } = {}) {
  const g = groups[gi];
  const el = $(`[data-chap="${gi}"]`, chaptersEl);
  if (!el) return;
  const p = selected(g);
  el.style.setProperty("--acc", p.accent);
  if (pack) {
    $("[data-front]", el).innerHTML = pouchSVG(p, { label: t("pouch", { name: fullName(p) }) });
    $("[data-back]", el).innerHTML = packBackSVG(p);
    $("[data-notes]", el).textContent = p.notes || p.tagline;
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

// ---------- Taster crate ----------
const crateEl = $("[data-crate]");
function renderCrate() {
  if (!box) {
    crateEl.hidden = true;
    return;
  }
  const minis = cfg.flavours.slice(0, 6).map((f, i) => `<span class="crate__p" style="--i:${i}">${pouchSVG(f, { mini: true })}</span>`).join("");
  const stripes = (box.stripes || []).map((c) => `<i style="background:${esc(c)}"></i>`).join("");
  crateEl.innerHTML = `<div class="wrap crate__grid">
      <div class="crate__stage" aria-hidden="true">
        <div class="crate__packs">${minis}</div>
        <div class="crate__lid"><span>Big Ed's</span></div>
        <div class="crate__box">
          <img src="${esc(cfg.logo)}" alt="" width="120" height="120">
          <p>${esc(box.name)}</p>
          <span class="crate__stripes">${stripes}</span>
        </div>
      </div>
      <div class="crate__info">
        <p class="kicker">${esc(B.crate_kicker)}</p>
        <h2 id="crate-title"><span data-reveal-line>${esc(B.crate_title)}</span></h2>
        <p class="crate__name">${esc(box.name)} · ${esc(fmtGrams(box.grams))}</p>
        <p class="crate__notes">${esc(box.notes || box.tagline)}</p>
        <div class="crate__act" data-crate-act></div>
      </div>
    </div>`;
  updateCrate();
}
function updateCrate() {
  const act = $("[data-crate-act]", crateEl);
  if (act) act.innerHTML = actHTML(box, { light: true });
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
  node.innerHTML = fromEl.outerHTML;
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
  const isNew = !printed.has(id);
  return `<li class="r-line${isNew ? " is-new" : ""}">
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
        <button class="btn btn--red btn--lg btn--wide" type="button" data-close>${esc(B.done_close)}</button>
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
      <button class="btn btn--red btn--lg btn--wide" type="submit" ${state.sending ? "disabled" : ""}>${state.sending ? `<span class="spin" aria-hidden="true"></span>` : ""}<span>${esc(state.sending ? B.sending : B.send)} · ${esc(money(cost))}</span></button>
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
function closeSheet() {
  sheet.close();
}
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
  if (gi >= 0) updateChapter(gi, { pack: false });
  if (box && id === box.id) updateCrate();
  renderTicket();
  renderSheet();
}
function renderAll() {
  groups.forEach((g, gi) => updateChapter(gi, { pack: false }));
  if (box) updateCrate();
  renderTicket();
  renderSheet();
}

const restoreFocus = (sel, root) => $(sel, root)?.focus({ preventScroll: true });
document.addEventListener("click", (e) => {
  const turn = e.target.closest("[data-turn]");
  if (turn) {
    const flip = $("[data-flip]", turn.closest(".chap"));
    const on = !flip.classList.contains("is-turned");
    flip.classList.toggle("is-turned", on);
    turn.setAttribute("aria-pressed", String(on));
    $("span", turn).textContent = on ? B.turn_back : B.turn;
    return;
  }
  const el = e.target.closest("[data-add], [data-inc], [data-dec], [data-remove], [data-pick], [data-close]");
  if (!el) return;
  if (el.dataset.close !== undefined) return closeSheet();
  if (el.dataset.pick) {
    const gi = Number(el.dataset.group);
    const g = groups[gi];
    if (state.sel[g.name] === el.dataset.pick) return;
    state.sel[g.name] = el.dataset.pick;
    save();
    const chap = $(`[data-chap="${gi}"]`, chaptersEl);
    const front = $("[data-front]", chap);
    // Swap the pack with a quick spin.
    if (!reduce) front.closest(".pack__swing").animate([{ transform: "rotateY(0deg)" }, { transform: "rotateY(360deg)" }], { duration: 600, easing: "cubic-bezier(.3,.7,.2,1)", composite: "add" });
    updateChapter(gi);
    restoreFocus(`[data-pick="${CSS.escape(el.dataset.pick)}"]`, chap);
    return;
  }
  const id = el.dataset.add || el.dataset.inc || el.dataset.dec || el.dataset.remove;
  const inSheet = Boolean(el.closest("[data-sheet]"));
  const scope = inSheet ? sheetBody : el.closest(".chap, .crate") || document;
  if (el.dataset.add || el.dataset.inc) {
    setQty(id, qty(id) + 1);
    if (!inSheet) {
      fly(el.closest(".chap")?.querySelector("[data-front] .pouch") || el.closest(".crate")?.querySelector(".crate__box"));
      if (reduce) toast(fullName(byId.get(id)));
    }
    restoreFocus(`[data-inc="${CSS.escape(id)}"]`, scope);
  } else if (el.dataset.dec) {
    setQty(id, qty(id) - 1);
    ($(`[data-dec="${CSS.escape(id)}"]`, scope) || $(`[data-add="${CSS.escape(id)}"]`, scope))?.focus({ preventScroll: true });
  } else if (el.dataset.remove) {
    setQty(id, 0);
  }
});

// Arrow keys move between beef and chicken.
chaptersEl.addEventListener("keydown", (e) => {
  const radio = e.target.closest("[data-pick]");
  const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
  if (!radio || !step) return;
  e.preventDefault();
  const all = $$("[data-pick]", radio.parentElement);
  all[(all.indexOf(radio) + step + all.length) % all.length].click();
});

// ---------- First render ----------
renderChapters();
renderCrate();
renderTicket();
$$("[data-barcode]").forEach((svg) => (svg.innerHTML = barcodeSVG(svg.dataset.barcode, { w: 120, h: 30 })));

// ---------- Smoke ----------
const smokeCanvas = $("[data-smoke]");
const hint = $("[data-hint]");
if (!window.matchMedia("(hover: hover)").matches) hint.textContent = hint.dataset.touch;
const smoke = smokeCanvas && initSmoke(smokeCanvas, { reduce, onReady: () => !reduce && hint.classList.add("is-on") });
if (!smoke) hint.remove();
else
  $(".hero").addEventListener(
    "pointermove",
    () => {
      setTimeout(() => hint.classList.remove("is-on"), 1800);
    },
    { once: true, passive: true }
  );

// ---------- Nav ----------
const nav = $("[data-nav]");
let lastY = window.scrollY;
const onScroll = () => {
  const y = window.scrollY;
  nav.classList.toggle("is-solid", y > window.innerHeight * 0.6);
  if (!sheet.open) nav.classList.toggle("is-hidden", y > 300 && y > lastY + 4 && !nav.contains(document.activeElement));
  if (y < lastY - 4) nav.classList.remove("is-hidden");
  lastY = y;
};
window.addEventListener("scroll", () => requestAnimationFrame(onScroll), { passive: true });

// ---------- Motion ----------
// Splits a line into letters (screen readers still get the whole text).
function splitChars(el) {
  const text = el.textContent.trim();
  const words = text.split(/\s+/).map((w) => `<span class="w">${[...w].map((c) => `<span class="c">${esc(c)}</span>`).join("")}</span>`);
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${words.join(" ")}</span>`;
}
$$("[data-split], [data-split-foot]").forEach(splitChars);
$$("[data-reveal-line]").forEach((el) => {
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
    lenis = new window.Lenis({ lerp: 0.11 });
    lenis.on("scroll", ST.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
  }
  // Anchor links glide with Lenis (without it, CSS smooth scrolling handles them).
  document.addEventListener("click", (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a || !lenis) return;
    const target = a.getAttribute("href") === "#top" ? 0 : $(a.getAttribute("href"));
    if (target === null) return;
    e.preventDefault();
    lenis.scrollTo(target, { offset: -20, duration: 1.4 });
  });

  // Hero: letters rise out of the smoke.
  const intro = gsap.timeline({ defaults: { ease: "power4.out" }, delay: 0.15 });
  intro
    .from(".hero__line .c", { yPercent: 115, rotate: 6, duration: 1.3, stagger: 0.028 }, 0)
    .from(".hero [data-rise]", { y: 30, opacity: 0, duration: 1, stagger: 0.1 }, 0.5)
    .from(".nav__in", { y: -24, opacity: 0, duration: 0.9, clearProps: "transform,opacity" }, 0.4);
  root.classList.add("is-intro");

  gsap.to(".hero__content", { yPercent: -14, opacity: 0.15, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
  gsap.to(".hero__media", { scale: 1.12, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });

  // Big headings slide up from behind a mask.
  $$("[data-reveal-line] .mask__in").forEach((el) =>
    gsap.from(el, { yPercent: 110, duration: 1.1, ease: "power4.out", scrollTrigger: { trigger: el.closest("[data-reveal-line]"), start: "top 88%", once: true } })
  );
  ST.batch($$("main [data-rise]").filter((el) => !el.closest(".hero")), {
    start: "top 90%",
    once: true,
    onEnter: (els) => gsap.from(els, { y: 40, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.08 }),
  });

  // Flavour chapters.
  $$(".chap", chaptersEl).forEach((chap) => {
    const range = { trigger: chap, start: "top bottom", end: "bottom top", scrub: true };
    gsap.fromTo($(".chap__bg", chap), { clipPath: "inset(9% 5% 9% 5% round 28px)" }, { clipPath: "inset(0% 0% 0% 0% round 0px)", ease: "none", scrollTrigger: { trigger: chap, start: "top bottom", end: "top 15%", scrub: true } });
    gsap.fromTo($(".chap__word span", chap), { xPercent: 12 }, { xPercent: -48, ease: "none", scrollTrigger: range });
    gsap.fromTo($(".pack__swing", chap), { rotateY: -32, rotateZ: -5, y: 80 }, { rotateY: 32, rotateZ: 4, y: -80, ease: "none", scrollTrigger: range });
    $$(".art", chap).forEach((art) => {
      const depth = Number(art.dataset.depth);
      gsap.fromTo(art, { y: 140 * depth, rotate: -25 * depth }, { y: -160 * depth, rotate: 30 * depth, ease: "none", scrollTrigger: range });
    });
    gsap.from($$(".chap__info > *", chap), { y: 50, opacity: 0, duration: 1, stagger: 0.07, ease: "power3.out", scrollTrigger: { trigger: chap, start: "top 55%", once: true } });
    ST.create({ trigger: chap, start: "top 50%", once: true, onEnter: () => chap.classList.add("is-hot") });
  });

  // Taster crate opens.
  if (box) {
    const tl = gsap.timeline({ scrollTrigger: { trigger: ".crate__stage", start: "top 85%", end: "center 40%", scrub: 0.6 } });
    tl.to(".crate__lid", { yPercent: -150, rotate: -14, xPercent: -18, ease: "power2.in", duration: 0.5 }, 0)
      .fromTo(".crate__p", { yPercent: 60 }, { yPercent: (i) => -78 - [0, 30, 12, 34, 6, 22][i % 6], rotate: (i) => (i - 2.5) * 9, xPercent: (i) => (i - 2.5) * 14, ease: "back.out(1.6)", duration: 0.7, stagger: 0.05 }, 0.2);
  }

  // The stamp thumps down.
  gsap.fromTo("[data-stamp]", { scale: 2.6, opacity: 0, rotate: -24 }, { scale: 1, opacity: 1, rotate: -7, duration: 0.55, ease: "back.out(2.2)", scrollTrigger: { trigger: "[data-stamp]", start: "top 85%", once: true } });

  // Footer wordmark.
  gsap.from(".foot__word .c", { yPercent: 100, duration: 1.1, ease: "power4.out", stagger: 0.04, scrollTrigger: { trigger: ".foot__word", start: "top 95%", once: true } });

  // The pack leans towards the pointer.
  if (finePointer) {
    $$(".chap__stage", chaptersEl).forEach((stage) => {
      const pack = $("[data-pack]", stage);
      const rx = gsap.quickTo(pack, "rotateX", { duration: 0.8, ease: "power3.out" });
      const ry = gsap.quickTo(pack, "rotateY", { duration: 0.8, ease: "power3.out" });
      stage.addEventListener("pointermove", (e) => {
        const r = stage.getBoundingClientRect();
        ry(((e.clientX - r.left) / r.width - 0.5) * 30);
        rx(((e.clientY - r.top) / r.height - 0.5) * -20);
      });
      stage.addEventListener("pointerleave", () => {
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
