// The redesigned page (/betatest).
// Loader → hero (logo + live 3D pack) → tapes → how to order → THE LINEUP (order
// here: one meat switch, mood chips, a card per flavour) → band → flavour chapters
// (the page colour blends from one flavour to the next) → 3D inspector → Ed → band
// → FAQ. The cart is a meat tag hanging from the bar (bottom bar on phones).
// Products, prices and the order endpoint are the same as the main page.
import { $, $$, esc, store, toast } from "./ui.js";
import { pouchSVG, prepareMenu, fmtGrams } from "./pouch.js";
import { t, packs } from "./i18n.js";

const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const finePointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
// Phones and tablets: native scrolling, fewer scroll-linked effects, cheaper 3D.
const touch = window.matchMedia("(pointer: coarse)").matches || window.matchMedia("(max-width: 899px)").matches;
if (touch) document.documentElement.classList.add("is-touch");
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
const lerp = (a, z, k) => a + (z - a) * k;
const icon = (d, size = 18, sw = 2.4) =>
  `<svg viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="${sw}" stroke-linecap="square" aria-hidden="true"><path d="${d}"/></svg>`;
const MINUS = icon("M5 12h14");
const PLUS = icon("M12 5v14M5 12h14");
const ARROW = icon("M4 12h15M13 5l7 7-7 7", 20, 2.6);
const roll = (text) => `<span class="roll" data-text="${esc(text)}"><span>${esc(text)}</span></span>`;

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
const meats = cfg.meats || [];
const fullName = (p) => p.fullName || p.name;
const slugOf = (id) => (/texas|bbq/i.test(id) ? "texas" : /garlic|teriyaki/i.test(id) ? "garlic" : /honey|sriracha/i.test(id) ? "honey" : "");
// How each flavour tastes (0-5), for the cards; heat comes from the product itself.
const TASTE = {
  texas: { sweet: 3, smoky: 5, garlic: 1 },
  garlic: { sweet: 2, smoky: 1, garlic: 5 },
  honey: { sweet: 4, smoky: 1, garlic: 1 },
};
// Which flavours each mood chip points at.
const MOODS = { sweet: ["honey", "texas"], smoky: ["texas"], savoury: ["garlic"], hot: ["honey"], any: ["taster"] };
// The colour each flavour chapter gives the page.
const MOOD_COLOURS = {
  texas: { bg: "#5e0f0a", ink: "#f6e6cc", accent: "#ff8a3d" },
  garlic: { bg: "#ecdcbf", ink: "#1b1410", accent: "#a65b0c" },
  honey: { bg: "#e97f0e", ink: "#160b04", accent: "#fff1d6" },
  taster: { bg: "#120c09", ink: "#f6e6cc", accent: "#e97f0e" },
};
const moodOf = (p) => (p.box ? MOOD_COLOURS.taster : MOOD_COLOURS[slugOf(p.id)] || { bg: "#1a0f0c", ink: "#f6e6cc", accent: "#e97f0e" });
const packSrc = (p) => `${B._packs}/${p.id}.webp`;
const packImg = (p, cls = "", eager = false) =>
  `<img class="${cls}" src="${packSrc(p)}" alt="${esc(b("pack_alt", { name: fullName(p) }))}" width="1200" height="1500" ${eager ? "" : 'loading="lazy"'} decoding="async" data-pack-img="${esc(p.id)}">`;
// A missing packshot falls back to the flat drawing.
document.addEventListener(
  "error",
  (e) => {
    const img = e.target;
    if (!(img instanceof HTMLImageElement) || !img.dataset.packImg) return;
    const span = document.createElement("span");
    span.className = `${img.className} pack-svg`;
    span.innerHTML = pouchSVG(byId.get(img.dataset.packImg), { label: img.alt });
    img.replaceWith(span);
  },
  true
);

// ---------- State ----------
const state = { lines: {}, meat: meats[0]?.id || "beef", mood: null, d: { name: "", phone: "", note: "" }, sending: false, error: "", errors: {}, done: null };
try {
  const saved = JSON.parse(store.get(STORE_KEY) || "null");
  if (saved) {
    for (const [id, q] of Object.entries(saved.lines || {})) if (byId.has(id) && q > 0) state.lines[id] = Math.min(MAX_QTY, Math.floor(q));
    if (meats.some((m) => m.id === saved.meat)) state.meat = saved.meat;
    Object.assign(state.d, saved.d || {});
  }
} catch {
  /* start fresh */
}
const save = () => store.set(STORE_KEY, JSON.stringify({ lines: state.lines, meat: state.meat, d: state.d }));
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
const pick = (g) => g.items.find((x) => x.meat === state.meat) || g.items[0];

// ======================================================================
// The lineup (ordering)
// ======================================================================
const lineupEl = $("[data-lineup]");
const switchEl = $("[data-meat-switch]");

function pips(n) {
  return Array.from({ length: 5 }, (_, i) => `<i class="${i < n ? "on" : ""}"></i>`).join("");
}
function stepperHTML(p, cls = "") {
  const q = qty(p.id);
  return `<span class="step ${cls}" role="group" aria-label="${esc(fullName(p))}">
      <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
      <output aria-live="polite"><b>${q}</b><small>${esc(B.in_cart)}</small></output>
      <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
    </span>`;
}
function cardAct(p) {
  return qty(p.id)
    ? stepperHTML(p)
    : `<button class="btn btn--ember btn--wide" type="button" data-add="${esc(p.id)}">${roll(B.add_cart)}<span class="btn__price">${esc(money(p.price))}</span></button>`;
}
function renderSwitch() {
  switchEl.innerHTML =
    meats
      .map((m) => {
        const from = Math.min(...cfg.flavours.filter((f) => f.meat === m.id).map((f) => f.price));
        const on = m.id === state.meat;
        return `<button type="button" role="radio" aria-checked="${on}" tabindex="${on ? 0 : -1}" data-meat="${esc(m.id)}"><b>${esc(m.name)}</b><span>${esc(money(from))}</span></button>`;
      })
      .join("") + `<i class="meat-switch__thumb" aria-hidden="true"></i>`;
  switchEl.style.setProperty("--n", String(meats.length));
  switchEl.style.setProperty("--k", String(Math.max(0, meats.findIndex((m) => m.id === state.meat))));
}
function renderLineup() {
  const meatIndex = Math.max(0, meats.findIndex((m) => m.id === state.meat));
  lineupEl.innerHTML =
    groups
      .map((g, i) => {
        const slug = slugOf(g.items[0].id);
        const taste = TASTE[slug] || { sweet: 2, smoky: 2, garlic: 2 };
        const p = pick(g);
        // One face per meat; the card turns to the chosen meat.
        const faces = g.items.map((x, k) => `<div class="card__face" data-face-meat="${esc(x.meat)}"${x === p ? "" : " hidden"}>${packImg(x, "card__img")}</div>`).join("");
        return `<article class="card" data-card="${i}" data-slug="${slug}" style="--i:${i};--band:${esc(p.band)}" aria-labelledby="card-${i}">
            <div class="card__stage" data-tilt>
              <div class="card__spot" aria-hidden="true"></div>
              <div class="card__tilt"><div class="card__flip">${faces}</div></div>
            </div>
            <div class="card__body">
              <p class="card__meat" data-card-meat>${esc(p.meatName)} · ${esc(fmtGrams(p.grams))}</p>
              <h3 class="card__name" id="card-${i}">${esc(g.name)}</h3>
              <p class="card__tag">${esc(p.tagline || "")}</p>
              <dl class="profile">
                <div><dt>${esc(B.taste_sweet)}</dt><dd>${pips(taste.sweet)}</dd></div>
                <div><dt>${esc(B.taste_smoky)}</dt><dd>${pips(taste.smoky)}</dd></div>
                <div><dt>${esc(B.taste_garlic)}</dt><dd>${pips(taste.garlic)}</dd></div>
                <div class="profile__heat"><dt>${esc(B.taste_heat)}</dt><dd>${pips(Math.max(0, p.heat - 1))}</dd></div>
              </dl>
              <div class="card__act" data-card-act>${cardAct(p)}</div>
            </div>
          </article>`;
      })
      .join("") +
    (box
      ? `<article class="card card--box" data-card="box" data-slug="taster" style="--i:${groups.length}" aria-labelledby="card-box">
          <div class="card__stage" data-tilt>
            <div class="card__spot" aria-hidden="true"></div>
            <div class="card__tilt"><div class="card__flip"><div class="card__face">${packImg(box, "card__img")}</div></div></div>
          </div>
          <div class="card__body">
            <p class="card__meat">${esc(B.taster_meta)}</p>
            <h3 class="card__name" id="card-box">${esc(box.name)}</h3>
            <p class="card__tag">${esc(B.taster_lead)}</p>
            <div class="card__act" data-card-act>${cardAct(box)}</div>
          </div>
        </article>`
      : "");
  applyMood();
}
// Update prices, meat line and buttons in place (keeps the flip animation running).
function updateLineup() {
  const meatIndex = Math.max(0, meats.findIndex((m) => m.id === state.meat));
  groups.forEach((g, i) => {
    const card = $(`[data-card="${i}"]`, lineupEl);
    if (!card) return;
    const p = pick(g);
    turnCard($(".card__flip", card), i);
    $("[data-card-meat]", card).textContent = `${p.meatName} · ${fmtGrams(p.grams)}`;
    swapAct($("[data-card-act]", card), p);
  });
  if (box) swapAct($('[data-card="box"] [data-card-act]', lineupEl), box);
  const { count, cost } = totals();
  const row = $("[data-shop-checkout]");
  row.hidden = count === 0;
  $("[data-shop-sum]").textContent = `${packs(count)} · ${money(cost)}`;
}
// Turn the pack edge-on, swap to the chosen meat, turn back.
function turnCard(flip, i) {
  const show = () => $$("[data-face-meat]", flip).forEach((f) => (f.hidden = f.dataset.faceMeat !== state.meat));
  const target = $(`[data-face-meat="${CSS.escape(state.meat)}"]`, flip);
  if (!target || !target.hidden) return;
  if (reduce || !flip.animate) return show();
  flip.getAnimations().forEach((a) => a.cancel());
  const out = flip.animate([{ transform: "rotateY(0deg)" }, { transform: "rotateY(90deg)" }], { duration: 230, delay: i * 70, easing: "cubic-bezier(.5,0,.75,0)", fill: "forwards" });
  out.finished
    .then(() => {
      show();
      flip.animate([{ transform: "rotateY(-90deg)" }, { transform: "rotateY(0deg)" }], { duration: 560, easing: "cubic-bezier(.2,.9,.3,1.25)" });
      out.cancel();
    })
    .catch(show);
}
function swapAct(el, p) {
  if (!el) return;
  const focused = el.contains(document.activeElement);
  el.innerHTML = cardAct(p);
  if (focused) ($(`[data-inc]`, el) || $(`[data-add]`, el))?.focus({ preventScroll: true });
}
function applyMood() {
  const targets = state.mood ? MOODS[state.mood] : null;
  $$(".card", lineupEl).forEach((card) => {
    const hit = !targets || targets.includes(card.dataset.slug);
    card.classList.toggle("is-dim", Boolean(targets) && !hit);
    card.classList.toggle("is-match", Boolean(targets) && hit);
  });
  $$("[data-mood]").forEach((btn) => btn.setAttribute("aria-pressed", String(btn.dataset.mood === state.mood)));
}
switchEl.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-meat]");
  if (!btn || btn.dataset.meat === state.meat) return;
  state.meat = btn.dataset.meat;
  save();
  renderSwitch();
  updateLineup();
  $(`[data-meat="${CSS.escape(state.meat)}"]`, switchEl)?.focus({ preventScroll: true });
});
switchEl.addEventListener("keydown", (e) => {
  const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
  if (!step) return;
  e.preventDefault();
  const i = meats.findIndex((m) => m.id === state.meat);
  $$("[data-meat]", switchEl)[(i + step + meats.length) % meats.length].click();
});
$$("[data-mood]").forEach((btn) =>
  btn.addEventListener("click", () => {
    state.mood = state.mood === btn.dataset.mood ? null : btn.dataset.mood;
    applyMood();
  })
);
// Cards lean towards the pointer, with a light that follows it.
if (finePointer && !reduce) {
  lineupEl.addEventListener("pointermove", (e) => {
    const stage = e.target.closest("[data-tilt]");
    if (!stage) return;
    const r = stage.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width;
    const y = (e.clientY - r.top) / r.height;
    stage.style.setProperty("--rx", `${(0.5 - y) * 10}deg`);
    stage.style.setProperty("--ry", `${(x - 0.5) * 14}deg`);
    stage.style.setProperty("--mx", `${x * 100}%`);
    stage.style.setProperty("--my", `${y * 100}%`);
  });
  lineupEl.addEventListener(
    "pointerleave",
    (e) => {
      for (const s of $$("[data-tilt]", lineupEl)) {
        s.style.setProperty("--rx", "0deg");
        s.style.setProperty("--ry", "0deg");
      }
    },
    true
  );
}

// ======================================================================
// Flavour chapters (up close)
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
    ? stepperHTML(p, "step--chap")
    : `<button class="buy__add" type="button" data-add="${esc(p.id)}" aria-label="${esc(b("add_meat", { meat: label }))} · ${esc(money(p.price))}">${PLUS}<span>${esc(B.add)}</span></button>`;
  return `<div class="buy${q ? " is-in" : ""}" data-buy="${esc(p.id)}"><span class="buy__label"><strong>${esc(label)}</strong><span>${esc(money(p.price))}</span></span>${right}</div>`;
}
const chapterItems = [...groups.map((g) => ({ group: g, p: g.items[0] })), ...(box ? [{ box, p: box }] : [])];
function renderChapters() {
  chaptersEl.innerHTML = chapterItems
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
      // The colour blend into the next chapter is painted once into the background
      // (no work while scrolling): own colour, then a long fade into the next one.
      const next = chapterItems[i + 1] ? moodOf(chapterItems[i + 1].p) : { bg: "#0b0706" };
      const top = i === 0 ? "#0b0706" : m.bg;
      return `<article class="chap" data-chap="${i}" style="--top:${top};--bg:${m.bg};--page:${m.bg};--next:${next.bg};--ink:${m.ink};--accent:${m.accent}" aria-labelledby="chap-${i}">
          <p class="chap__word" aria-hidden="true"><span>${esc(word)}</span></p>
          ${packsHTML}
          <div class="chap__info">
            <p class="chap__idx">0${i + 1} <span>/ 0${chapterItems.length}</span></p>
            <h3 class="chap__name" id="chap-${i}">${esc(it.box ? box.name : it.group.name)}</h3>
            <p class="chap__tag">${esc(it.box ? B.taster_lead : p.tagline || "")}</p>
            ${it.box ? "" : `<p class="chap__notes">${esc(p.notes || "")}</p>${heatHTML(p.heat)}`}
            <div class="chap__buy">${buys}</div>
          </div>
        </article>`;
    })
    .join("");
}
function updateBuys() {
  for (const el of $$("[data-buy]", chaptersEl)) {
    const p = byId.get(el.dataset.buy);
    const focused = el.contains(document.activeElement);
    el.outerHTML = buyRow(p, p.box ? p.name : p.meatName);
    if (focused) ($(`[data-inc="${CSS.escape(p.id)}"]`, chaptersEl) || $(`[data-add="${CSS.escape(p.id)}"]`, chaptersEl))?.focus({ preventScroll: true });
  }
}

// ======================================================================
// Cart: the hanging tag, the phone bar, the drawer
// ======================================================================
const cartBtn = $("[data-cart]");
const cartTag = $("[data-cart-tag]");
const bagbar = $("[data-bagbar]");
let lastCount = -1;

// The tag swings like it hangs on a string: a small spring simulation.
const swing = { a: 0, v: 0, pull: 0, pv: 0, raf: 0, drag: null };
function swingTick() {
  swing.raf = 0;
  if (!swing.drag) {
    swing.v += (-swing.a * 60 - swing.v * 4.2) / 60;
    swing.a += swing.v / 60;
    swing.pv += (-swing.pull * 140 - swing.pv * 11) / 60;
    swing.pull += swing.pv / 60;
  }
  cartBtn.style.setProperty("--swing", `${swing.a.toFixed(2)}deg`);
  cartBtn.style.setProperty("--pull", `${Math.max(0, swing.pull).toFixed(1)}px`);
  if (swing.drag || Math.abs(swing.a) > 0.05 || Math.abs(swing.v) > 0.05 || Math.abs(swing.pull) > 0.3 || Math.abs(swing.pv) > 0.3) swing.raf = requestAnimationFrame(swingTick);
}
function kick(strength) {
  if (reduce) return;
  swing.v += strength;
  if (!swing.raf) swing.raf = requestAnimationFrame(swingTick);
}
if (!reduce) {
  cartBtn.addEventListener("pointerenter", () => kick(18));
  // Pull the tag down: it stretches on its string; let go past the line and the cart opens.
  cartBtn.addEventListener("pointerdown", (e) => {
    if (getComputedStyle(cartBtn).getPropertyValue("--hang").trim() !== "1") return;
    swing.drag = { y: e.clientY, x: e.clientX, moved: 0 };
    cartBtn.setPointerCapture(e.pointerId);
    if (!swing.raf) swing.raf = requestAnimationFrame(swingTick);
  });
  cartBtn.addEventListener("pointermove", (e) => {
    if (!swing.drag) return;
    const dy = e.clientY - swing.drag.y;
    const dx = e.clientX - swing.drag.x;
    swing.drag.moved = Math.max(swing.drag.moved, Math.abs(dy) + Math.abs(dx));
    swing.pull = clamp(dy, 0, 140) * 0.7;
    swing.a = clamp(dx * -0.25, -20, 20);
  });
  const release = () => {
    if (!swing.drag) return;
    const far = swing.pull > 45;
    swing.drag.wasDrag = swing.drag.moved > 6;
    cartBtn.dataset.dragged = swing.drag.wasDrag ? "1" : "";
    swing.drag = null;
    if (far) openSheet();
    if (!swing.raf) swing.raf = requestAnimationFrame(swingTick);
  };
  cartBtn.addEventListener("pointerup", release);
  cartBtn.addEventListener("pointercancel", release);
}
function renderCart() {
  const { count, cost } = totals();
  $("[data-cart-count]").textContent = String(count);
  $("[data-cart-sum]").textContent = count ? money(cost) : B.cart_empty;
  cartBtn.classList.toggle("is-full", count > 0);
  cartBtn.setAttribute("aria-label", `${B.cart_open}: ${packs(count)}, ${money(cost)}`);
  bagbar.hidden = count === 0;
  document.body.classList.toggle("has-bag", count > 0);
  $("[data-bagbar-count]").textContent = String(count);
  $("[data-bagbar-sum]").textContent = `${packs(count)} · ${money(cost)}`;
  if (count > lastCount && lastCount >= 0) kick(55);
  lastCount = count;
}
// The added pack flies to the tag (or the bar on phones).
function fly(fromEl, p) {
  if (reduce || !fromEl) return;
  const target = !bagbar.hidden && getComputedStyle(bagbar).display !== "none" ? bagbar : cartTag;
  const a = fromEl.getBoundingClientRect();
  const z = target.getBoundingClientRect();
  if (!a.width || a.bottom < 0 || a.top > innerHeight) return;
  const node = document.createElement("div");
  node.className = "fly";
  node.innerHTML = packImg(p, "", true);
  const w = Math.min(a.width, 200);
  const x0 = a.left + a.width / 2 - w / 2;
  const y0 = a.top + a.height / 2 - w * 0.625;
  Object.assign(node.style, { left: `${x0}px`, top: `${y0}px`, width: `${w}px` });
  document.body.appendChild(node);
  const dx = z.left + z.width / 2 - (x0 + w / 2);
  const dy = z.top + z.height / 2 - (y0 + w * 0.625);
  node
    .animate(
      [
        { transform: "translate(0,0) scale(1) rotate(-4deg)", opacity: 1 },
        { transform: `translate(${dx * 0.35}px, ${dy * 0.35 - 140}px) scale(.55) rotate(-20deg)`, opacity: 1, offset: 0.45 },
        { transform: `translate(${dx}px, ${dy}px) scale(.1) rotate(-40deg)`, opacity: 0.3 },
      ],
      { duration: 820, easing: "cubic-bezier(.5,0,.4,1)" }
    )
    .finished.finally(() => node.remove());
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
        <p class="line__sub">${esc(sub)} · ${esc(money(p.price))}</p>
        <div class="line__row">
          <span class="step step--sm" role="group" aria-label="${esc(fullName(p))}">
            <button type="button" data-dec="${esc(id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
            <output aria-live="polite"><b>${q}</b></output>
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
        <img class="done__seal" src="${B._brand}/skull.webp" width="466" height="315" alt="">
        <h3 tabindex="-1">${esc(b("done_title", { name: d.name }))}</h3>
        <p>${esc(b("done_lead", { phone: d.phone }))}</p>
        <p class="done__code"><small>${esc(B.done_code)}</small><strong>${esc(d.code)}</strong></p>
        <button class="btn btn--ember btn--wide" type="button" data-close>${roll(B.done_close)}</button>
      </div>`;
    return;
  }
  const ids = Object.keys(state.lines);
  if (!ids.length) {
    bagEl.hidden = true;
    sheetBody.innerHTML = `<div class="empty"><p>${esc(B.sheet_empty)}</p><a class="btn btn--ember" href="#shop" data-close>${roll(B.sheet_browse)}<span class="btn__ico">${ARROW}</span></a></div>`;
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
      <button class="btn btn--ember btn--wide btn--lg" type="submit" ${state.sending ? "disabled" : ""}>${state.sending ? `<span class="spin" aria-hidden="true"></span><span>${esc(B.sending)}</span>` : `${roll(B.send)}<span class="btn__price">${esc(money(cost))}</span>`}</button>
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
    // After a drag on the tag the release already decided.
    if (btn.dataset.dragged) {
      btn.dataset.dragged = "";
      return;
    }
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
  updateLineup();
  updateBuys();
  renderCart();
  if (sheet.open) {
    renderBag();
    if (q > before) $$(".bag__p", bagEl).pop()?.classList.add("is-drop");
    renderSheet();
  }
}
function renderAll() {
  updateLineup();
  updateBuys();
  renderCart();
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
    const p = byId.get(id);
    const host = el.closest(".card, .chap");
    const src = host ? $(`[data-pack-img="${CSS.escape(id)}"]`, host) || $(".card__img, .chap__pack", host) : null;
    setQty(id, qty(id) + 1);
    if (!inSheet) {
      fly(src, p);
      if (reduce) toast(fullName(p));
    }
  } else if (el.dataset.dec) {
    setQty(id, qty(id) - 1);
  } else if (el.dataset.remove) {
    setQty(id, 0);
  }
});

// ======================================================================
// Language menu, phone menu, nav
// ======================================================================
const lang = $("[data-lang]");
if (lang) {
  document.addEventListener("click", (e) => {
    if (lang.open && !lang.contains(e.target)) lang.open = false;
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && lang.open) {
      lang.open = false;
      $("summary", lang).focus();
    }
  });
}
const menu = $("[data-menu]");
const menuBtn = $("[data-menu-open]");
function setMenu(open) {
  menuBtn.setAttribute("aria-expanded", String(open));
  if (open) {
    menu.hidden = false;
    requestAnimationFrame(() => menu.classList.add("is-open"));
    lenis?.stop();
    $("a", menu)?.focus({ preventScroll: true });
  } else {
    menu.classList.remove("is-open");
    lenis?.start();
    setTimeout(() => {
      if (!menu.classList.contains("is-open")) menu.hidden = true;
    }, 500);
  }
}
menuBtn.addEventListener("click", () => setMenu(true));
$("[data-menu-close]").addEventListener("click", () => {
  setMenu(false);
  menuBtn.focus();
});
menu.addEventListener("click", (e) => {
  if (e.target.closest("a[href^='#']")) setMenu(false);
});
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !menu.hidden) setMenu(false);
});
const nav = $("[data-nav]");
window.addEventListener("scroll", () => nav.classList.toggle("is-solid", window.scrollY > 40), { passive: true });

// Buttons: the fill grows from where the pointer came in.
document.addEventListener(
  "pointerover",
  (e) => {
    const btn = e.target.closest?.(".btn");
    if (!btn || btn.contains(e.relatedTarget)) return;
    const r = btn.getBoundingClientRect();
    btn.style.setProperty("--x", `${e.clientX - r.left}px`);
    btn.style.setProperty("--y", `${e.clientY - r.top}px`);
  },
  { passive: true }
);

// ======================================================================
// Film grain: a few frames of fine noise, cycled like real film.
// ======================================================================
function makeGrain() {
  // Phones get a still, light grain from CSS; the animated one costs too much there.
  if (reduce || touch) return;
  const S = 256;
  const F = 6;
  const c = document.createElement("canvas");
  c.width = S * F;
  c.height = S;
  const ctx = c.getContext("2d");
  const img = ctx.createImageData(S * F, S);
  for (let i = 0; i < img.data.length; i += 4) {
    // Gaussian-ish grain: sum of randoms, centred on mid grey.
    const v = ((Math.random() + Math.random() + Math.random()) / 3) * 255;
    img.data[i] = img.data[i + 1] = img.data[i + 2] = v;
    img.data[i + 3] = 255;
  }
  ctx.putImageData(img, 0, 0);
  const film = $("[data-film]");
  film.style.backgroundImage = `url(${c.toDataURL("image/png")})`;
  film.classList.add("is-on");
}
makeGrain();

const root = document.documentElement;
let chapList = [];

// ======================================================================
// 3D: hero pack and inspector (still images if WebGL is missing)
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
  if (!inspectViewer) $("[data-inspect-fallback]").src = packSrc(inspectProduct);
});
const markFace = (n) => $$("[data-face]").forEach((x) => x.setAttribute("aria-pressed", String(Number(x.dataset.face) === n)));
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
    const logo = await mod.loadAssets(`${B._brand}/logo.png`);
    // Developer hook: /en/betatest?render-packs renders the packshots for public/img/packs.
    if (location.search.includes("render-packs")) {
      window.__packs = mod.renderShots(products, { meats: cfg.meats, logo, texts: texts3d, stripes });
      return;
    }
    const opts = { meats: cfg.meats, logo, texts: texts3d, stripes, reduce };
    heroViewer = mod.createViewer($("[data-hero-canvas]"), { ...opts, mode: "hero", fill: 0.74, idle: !touch, maxDpr: touch ? 1.5 : 2 });
    if (heroViewer) {
      heroViewer.setProduct(heroProducts[0], { animate: false });
      root.classList.add("has-3d");
      let h = 0;
      $("[data-hero-canvas]").addEventListener("click", () => {
        h = (h + 1) % heroProducts.length;
        heroViewer.setProduct(heroProducts[h]);
      });
    }
    inspectViewer = mod.createViewer($("[data-inspect-canvas]"), { ...opts, mode: "inspect", fill: 0.7, maxDpr: touch ? 1.5 : 2 });
    if (inspectViewer) {
      inspectViewer.setProduct(inspectProduct, { animate: false });
      inspectViewer.onTurn(() => markFace(inspectViewer.faceIndex === 3 ? 1 : inspectViewer.faceIndex));
    }
  })
  .catch(() => {});
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
// First render
// ======================================================================
renderSwitch();
renderLineup();
renderChapters();
chapList = $$(".chap", chaptersEl);
updateLineup();
renderCart();
$$("[data-split]").forEach((el) => {
  const text = el.textContent.trim();
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${text
    .split(/\s+/)
    .map((w) => `<span class="w"><span class="wi">${esc(w)}</span></span>`)
    .join(" ")}</span>`;
});
$$("[data-words]").forEach((el) => {
  const text = el.textContent.trim();
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${text
    .split(/\s+/)
    .map((w) => `<span class="word">${esc(w)}</span>`)
    .join(" ")}</span>`;
});

// ======================================================================
// Loader + motion
// ======================================================================
const loader = $("[data-loader]");
function runLoader() {
  return new Promise((resolve) => {
    let seen = false;
    try {
      seen = Boolean(sessionStorage.getItem("bigeds-seen"));
    } catch {
      /* ignore */
    }
    if (reduce || seen) {
      loader.remove();
      resolve();
      return;
    }
    const n = $("[data-loader-n]");
    const skullFill = $(".loader__fill", loader);
    const t0 = performance.now();
    let real = 0;
    Promise.all([document.fonts?.ready, threeReady, new Promise((r) => (document.readyState === "complete" ? r() : addEventListener("load", r, { once: true })))]).then(() => (real = 1));
    setTimeout(() => (real = 1), 4000);
    (function step(now) {
      const time = clamp((now - t0) / 1500);
      const p = Math.min(time, real ? 1 : 0.9);
      n.textContent = String(Math.round(p * 100));
      skullFill.style.clipPath = `inset(${(1 - p) * 100}% 0 0 0)`;
      if (p >= 1) {
        try {
          sessionStorage.setItem("bigeds-seen", "1");
        } catch {
          /* ignore */
        }
        loader.classList.add("is-out");
        setTimeout(() => loader.remove(), 1200);
        setTimeout(resolve, 380);
        return;
      }
      requestAnimationFrame(step);
    })(t0);
  });
}

function initMotion() {
  const gsap = window.gsap;
  const ST = window.ScrollTrigger;
  if (!gsap || !ST || reduce) {
    root.classList.add("is-static");
    return;
  }
  root.classList.add("is-motion");
  gsap.registerPlugin(ST);
  // The address bar showing/hiding must not make every scroll effect re-measure.
  ST.config({ ignoreMobileResize: true });
  if (window.Lenis && !touch) {
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
      lenis.scrollTo(target, { offset: target === 0 ? 0 : -70, duration: 1.6, easing: (x) => 1 - Math.pow(1 - x, 4) });
    });
  }

  // Hero: the glow drifts with the pointer; on scroll the copy lifts and the pack sinks.
  if (finePointer) {
    const fx = gsap.quickTo(".hero__field", "--fx", { duration: 2.4, ease: "power3" });
    const fy = gsap.quickTo(".hero__field", "--fy", { duration: 2.4, ease: "power3" });
    window.addEventListener("pointermove", (e) => {
      fx((e.clientX / innerWidth - 0.5) * 30);
      fy((e.clientY / innerHeight - 0.5) * 20);
    });
  }
  // Phones: one-shot animations instead of effects tied to every scroll frame.
  const scrub = (opts) => (touch ? { trigger: opts.trigger, start: "top 85%", once: true } : opts);
  if (!touch) gsap.to(".hero__copy", { yPercent: -12, opacity: 0.15, ease: "none", scrollTrigger: { trigger: ".hero", start: "20% top", end: "bottom top", scrub: true } });
  if (!touch) gsap.to(".hero__stage", { yPercent: 14, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });

  // Tapes drift and speed up with scroll.
  $$("[data-tape]").forEach((track) => {
    const dir = Number(track.dataset.dir || 1);
    const loop = gsap.fromTo(track, { xPercent: dir > 0 ? 0 : -50 }, { xPercent: dir > 0 ? -50 : 0, ease: "none", duration: 36, repeat: -1 });
    if (!touch) ST.create({ trigger: ".tapes", start: "top bottom", end: "bottom top", onUpdate: (self) => loop.timeScale(1 + Math.min(Math.abs(self.getVelocity()) / 260, 6)) });
  });

  // The order bands: the words run, faster with scroll.
  $$("[data-band]").forEach((band) => {
    const track = $(".band__track", band);
    const loop = gsap.to(track, { xPercent: -50, ease: "none", duration: 22, repeat: -1 });
    if (!touch) ST.create({ trigger: band, start: "top bottom", end: "bottom top", onUpdate: (self) => loop.timeScale(1 + Math.min(Math.abs(self.getVelocity()) / 300, 5)) });
    if (!touch) gsap.fromTo(band, { clipPath: "inset(0 50% 0 50%)" }, { clipPath: "inset(0 0% 0 0%)", ease: "power2.out", scrollTrigger: { trigger: band, start: "top 95%", end: "top 55%", scrub: 0.6 } });
  });

  // How to order: the giant words slide in.
  $$("[data-how]").forEach((row, i) => {
    gsap.fromTo($(".how__word", row), { xPercent: i % 2 ? 30 : -30, opacity: 0.1 }, { xPercent: 0, opacity: 1, ease: "power2.out", duration: 1.1, scrollTrigger: scrub({ trigger: row, start: "top 95%", end: "top 50%", scrub: 0.8 }) });
    gsap.from([$(".how__n", row), $(".how__d", row)], { opacity: 0, y: 20, duration: 0.9, ease: "power3.out", stagger: 0.1, scrollTrigger: { trigger: row, start: "top 72%", once: true } });
  });

  // Headings: word by word from behind a mask.
  $$("[data-split]").forEach((el) =>
    gsap.from($$(".wi", el), { yPercent: 110, duration: 1.1, ease: "expo.out", stagger: 0.05, scrollTrigger: { trigger: el, start: "top 88%", once: true } })
  );

  // Lineup: cards rise in one after the other, packs drop onto their cards.
  gsap.from(".shop__controls > *", { y: 30, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.1, clearProps: "transform,opacity", scrollTrigger: { trigger: ".shop__controls", start: "top 88%", once: true } });
  ST.batch($$(".card", lineupEl), {
    start: "top 88%",
    once: true,
    onEnter: (els) => {
      gsap.from(els, { y: 80, opacity: 0, duration: 1.1, ease: "power3.out", stagger: 0.09, clearProps: "transform,opacity" });
      gsap.from(els.map((el) => $(".card__tilt", el)), { yPercent: -30, rotate: -10, duration: 1.3, ease: "back.out(1.6)", stagger: 0.09, delay: 0.1, clearProps: "transform" });
    },
  });

  // Chapters: the giant word slides across, the packs swing in, the info rises.
  chapList.forEach((chap) => {
    const range = { trigger: chap, start: "top bottom", end: "bottom top", scrub: true };
    if (!touch) gsap.fromTo($(".chap__word span", chap), { xPercent: 18 }, { xPercent: -42, ease: "none", scrollTrigger: range });
    const a = $(".chap__pack--a", chap);
    const b2 = $(".chap__pack--b", chap);
    if (a) gsap.fromTo(a, { yPercent: 24, rotate: -20 }, { yPercent: -10, rotate: -6, ease: touch ? "power3.out" : "none", duration: 1.2, scrollTrigger: scrub(range) });
    if (b2) gsap.fromTo(b2, { yPercent: 40, rotate: 24 }, { yPercent: -4, rotate: 10, ease: touch ? "power3.out" : "none", duration: 1.3, scrollTrigger: scrub(range) });
    $$(".chap__mini", chap).forEach((m, k) =>
      gsap.fromTo(m, { yPercent: 100, rotate: 0, opacity: 0 }, { yPercent: -10 - (k % 3) * 20, rotate: (k - 2.5) * 10, opacity: 1, ease: "back.out(1.4)", duration: 1, stagger: 0.05, scrollTrigger: scrub({ trigger: chap, start: "top 75%", end: "top 15%", scrub: 0.6 }) })
    );
    gsap.from($$(".chap__info > *", chap), { y: 40, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.07, scrollTrigger: { trigger: chap, start: "top 55%", once: true } });
    ST.create({ trigger: chap, start: "top 55%", once: true, onEnter: () => chap.classList.add("is-hot") });
  });

  // Ed: the story lights up word by word as you read; the facts label prints row by row.
  const words = $$(".ed__text .word");
  if (words.length && !touch)
    gsap.fromTo(words, { opacity: 0.16 }, { opacity: 1, ease: "none", stagger: 0.1, scrollTrigger: { trigger: ".ed__text", start: "top 80%", end: "bottom 45%", scrub: 0.4 } });
  gsap.from(".facts", { y: 60, rotate: 3, opacity: 0, duration: 1.1, ease: "power3.out", scrollTrigger: { trigger: ".facts", start: "top 85%", once: true } });
  gsap.from(".facts__row", { opacity: 0, x: -16, duration: 0.5, ease: "power2.out", stagger: 0.08, delay: 0.35, scrollTrigger: { trigger: ".facts", start: "top 85%", once: true } });
  gsap.fromTo("[data-ed-skull]", { rotate: -10, yPercent: 20, opacity: 0 }, { rotate: 0, yPercent: 0, opacity: 1, ease: touch ? "power3.out" : "none", duration: 1.1, scrollTrigger: scrub({ trigger: ".ed", start: "top 80%", end: "top 20%", scrub: true }) });

  // Blocks rise in.
  ST.batch($$("main [data-in], .foot [data-in]").filter((el) => !el.closest(".hero")), {
    start: "top 92%",
    once: true,
    onEnter: (els) => gsap.from(els, { y: 30, opacity: 0, duration: 1, ease: "power3.out", stagger: 0.08, clearProps: "transform,opacity" }),
  });

  // Footer mark grows from below.
  gsap.fromTo(".foot__word", { yPercent: 40, scale: 0.92 }, { yPercent: 0, scale: 1, ease: touch ? "power3.out" : "none", duration: 1.2, scrollTrigger: scrub({ trigger: ".foot__mark", start: "top bottom", end: "bottom bottom", scrub: true }) });
  gsap.fromTo(".foot__skull", { yPercent: 60, rotate: -8, opacity: 0 }, { yPercent: 0, rotate: 0, opacity: 1, ease: touch ? "power3.out" : "none", duration: 1.2, scrollTrigger: scrub({ trigger: ".foot__mark", start: "top bottom", end: "bottom bottom", scrub: true }) });

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
    .from("[data-hero-skull]", { yPercent: -40, scale: 1.3, opacity: 0, filter: "blur(16px)", duration: 1.7 }, 0.1)
    .from("[data-hero-wordmark]", { yPercent: 30, opacity: 0, filter: "blur(14px)", duration: 1.7 }, 0.25)
    .from(".hero__stage", { yPercent: 16, opacity: 0, duration: 1.9 }, 0.4)
    .from(".hero__text > *", { y: 26, opacity: 0, duration: 1.1, stagger: 0.09, clearProps: "transform,opacity" }, 0.75)
    .from(".nav", { y: -24, opacity: 0, duration: 1.1, clearProps: "transform,opacity" }, 0.6);
  root.classList.add("is-in");
  setTimeout(() => kick(40), 1500);
}

const start = () => {
  initMotion();
  runLoader().then(intro);
};
if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", start, { once: true });
else start();
