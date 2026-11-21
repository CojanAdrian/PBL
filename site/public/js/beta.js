// The redesigned page (/betatest).
// 1. Story: a tall section whose stage sticks to the screen. Scrolling turns the
//    3D pack (beta3d.js); each half turn shows the next flavour, with one caption.
// 2. Choose: one card per flavour (product shots rendered from the same 3D pack),
//    beef/chicken, add to order. 3. The order panel.
// No animation libraries: native scroll, CSS transitions, one WebGL canvas that
// only draws while the pack is moving. Same products, prices and endpoint as the main page.
import { $, $$, esc, store, toast } from "./ui.js";
import { pouchSVG, prepareMenu, fmtGrams } from "./pouch.js";
import { t, packs } from "./i18n.js";

const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
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
const smooth = (a, z, v) => {
  const x = clamp((v - a) / (z - a));
  return x * x * (3 - 2 * x);
};
const icon = (d, size = 18, sw = 2) =>
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
const heatWord = (h) => B[`heat_${clamp(h, 1, 5)}`];
// The story shows each flavour (first meat), then the taster box.
const story = [...groups.map((g) => ({ product: g.items[0], group: g })), ...(box ? [{ product: box, box: true }] : [])];

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

// ======================================================================
// Story
// ======================================================================
const storyEl = $("[data-story]");
const capsEl = $("[data-caps]");
const heroCaps = $$("[data-cap-hero]");
const cue = $("[data-cue]");
const indexEl = $("[data-index]");
const fallbackEl = $("[data-fallback]");
const N = story.length;
const INTRO = 0.14; // share of the section used by the opening turn
storyEl.style.setProperty("--n", String(N));

capsEl.innerHTML = story
  .map((s, i) => {
    const p = s.product;
    if (s.box) {
      return `<div class="cap" data-cap="${i}">
          <p class="cap__n">0${i + 1} <span>/ 0${N}</span></p>
          <h2 class="cap__name">${esc(B.taster_name)}</h2>
          <p class="cap__tag">${esc(p.tagline || "")}</p>
          <p class="cap__meta">${esc(fmtGrams(p.grams))} · ${esc(money(p.price))}</p>
        </div>`;
    }
    const prices = s.group.items.map((x) => `${esc(x.meatName)} ${esc(money(x.price))}`).join(" · ");
    return `<div class="cap" data-cap="${i}">
        <p class="cap__n">0${i + 1} <span>/ 0${N}</span></p>
        <h2 class="cap__name">${esc(s.group.name)}</h2>
        <p class="cap__tag">${esc(p.tagline || "")}</p>
        <p class="cap__meta">${esc(heatWord(p.heat))} · ${prices}</p>
      </div>`;
  })
  .join("");
indexEl.innerHTML = story
  .map((s, i) => `<button type="button" data-goto="${i}"><span>${esc(s.box ? B.taster_name : s.group.name)}</span><i></i></button>`)
  .join("");
const caps = $$("[data-cap]", capsEl);
const dots = $$("[data-goto]", indexEl);

const ease = (x) => (x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2);
function angleAt(p) {
  if (p < INTRO) return -0.6 + 0.6 * ease(p / INTRO);
  const local = (p - INTRO) / (1 - INTRO);
  const seg = local * N;
  const k = Math.min(Math.floor(seg), N - 1);
  const s = seg - k;
  // Hold each flavour for most of its stretch, then turn half a revolution to the next.
  const turn = k < N - 1 ? ease(clamp((s - 0.55) / 0.45)) : 0;
  return Math.PI * (k + turn);
}
// The pack starts a little smaller under the headline and grows into place.
const sizeAt = (p) => 0.84 + 0.16 * ease(clamp(p / INTRO));
const progressFor = (i) => INTRO + (1 - INTRO) * ((i + 0.25) / N);
function storyProgress() {
  const r = storyEl.getBoundingClientRect();
  const total = storyEl.offsetHeight - innerHeight;
  return total > 0 ? clamp(-r.top / total) : 0;
}

let stage = null;
let lastActive = -1;
function updateStory() {
  const p = storyProgress();
  const a = angleAt(p);
  stage?.setAngle(a, { size: sizeAt(p) });
  // Opening caption fades and lifts as the first turn happens.
  const h = 1 - smooth(0.0, INTRO * 0.75, p);
  for (const el of heroCaps) {
    el.style.opacity = String(h);
    el.style.transform = `translateY(${(1 - h) * -24}px)`;
    el.style.visibility = h < 0.01 ? "hidden" : "visible";
  }
  cue.style.opacity = String(1 - smooth(0, 0.03, p));
  // Flavour captions: fully shown while the pack faces front, gone while it is edge-on.
  const pos = a / Math.PI;
  const idx = clamp(Math.round(pos), 0, N - 1);
  const off = Math.abs(pos - idx);
  const show = p < INTRO * 0.7 ? 0 : 1 - smooth(0.06, 0.3, off);
  caps.forEach((c, i) => {
    const on = i === idx;
    c.style.opacity = on ? String(show) : "0";
    c.style.transform = on ? `translateY(${(1 - show) * 18}px)` : "translateY(18px)";
    c.style.visibility = on && show > 0.01 ? "visible" : "hidden";
  });
  indexEl.style.opacity = String(smooth(INTRO * 0.5, INTRO, p));
  if (idx !== lastActive) {
    lastActive = idx;
    dots.forEach((d, i) => d.setAttribute("aria-current", i === idx ? "true" : "false"));
    if (!stage) fallbackEl.innerHTML = pouchSVG(story[idx].product, { label: b("pack_3d", { name: fullName(story[idx].product) }) });
  }
}
let ticking = false;
window.addEventListener(
  "scroll",
  () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      ticking = false;
      updateStory();
    });
  },
  { passive: true }
);
window.addEventListener("resize", updateStory);
indexEl.addEventListener("click", (e) => {
  const d = e.target.closest("[data-goto]");
  if (!d) return;
  const top = storyEl.getBoundingClientRect().top + window.scrollY;
  const total = storyEl.offsetHeight - innerHeight;
  window.scrollTo({ top: top + total * progressFor(Number(d.dataset.goto)), behavior: reduce ? "auto" : "smooth" });
});
// "Flavours" in the nav goes to the first flavour, not the opening shot.
$$('a[href="#flavours"]').forEach((a) =>
  a.addEventListener("click", (e) => {
    e.preventDefault();
    const top = storyEl.getBoundingClientRect().top + window.scrollY;
    window.scrollTo({ top: top + (storyEl.offsetHeight - innerHeight) * progressFor(0), behavior: reduce ? "auto" : "smooth" });
  })
);

// ======================================================================
// Choose
// ======================================================================
const gridEl = $("[data-grid]");
const boxEl = $("[data-box]");
let shots = {};
const shotFor = (p) => (shots[p.id] ? `<img src="${shots[p.id]}" alt="${esc(b("pack_3d", { name: fullName(p) }))}" width="720" height="900">` : pouchSVG(p, { label: b("pack_3d", { name: fullName(p) }) }));

function actHTML(p) {
  const q = qty(p.id);
  if (!q) return `<button class="btn btn--light btn--wide" type="button" data-add="${esc(p.id)}">${esc(B.add)}</button>`;
  return `<div class="stepper" role="group" aria-label="${esc(fullName(p))}">
      <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
      <output aria-live="polite">${q} <small>${esc(B.in_order)}</small></output>
      <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
    </div>`;
}
function cardHTML(g, gi) {
  const p = selected(g);
  return `<article class="card" data-card="${gi}" aria-labelledby="card-${gi}">
      <div class="card__shot" data-shot>${shotFor(p)}</div>
      <h3 class="card__name" id="card-${gi}">${esc(g.name)}</h3>
      <p class="card__tag">${esc(p.tagline || "")}</p>
      <p class="card__notes">${esc(p.notes || "")}</p>
      <dl class="card__facts"><div><dt>${esc(B.heat)}</dt><dd>${esc(heatWord(p.heat))}</dd></div><div><dt>${esc(p.typeName || "")}</dt><dd>${esc(fmtGrams(p.grams))}</dd></div></dl>
      ${g.items.length > 1 ? `<div class="seg" role="radiogroup" aria-label="${esc(b("meat_aria", { name: g.name }))}">${g.items
        .map((x) => {
          const on = x.id === p.id;
          const n = qty(x.id);
          return `<button type="button" role="radio" aria-checked="${on}" tabindex="${on ? 0 : -1}" data-pick="${esc(x.id)}" data-group="${gi}">${esc(x.meatName)}${n ? ` <b>${n}</b>` : ""}</button>`;
        })
        .join("")}</div>` : ""}
      <p class="card__price">${esc(money(p.price))}</p>
      <div class="card__act">${actHTML(p)}</div>
    </article>`;
}
function renderGrid() {
  gridEl.innerHTML = groups.map(cardHTML).join("");
  if (box) {
    boxEl.innerHTML = `<article class="boxrow" aria-labelledby="box-title">
        <div class="boxrow__shot">${shotFor(box)}</div>
        <div class="boxrow__info">
          <h3 id="box-title">${esc(B.box_title)}</h3>
          <p class="card__notes">${esc(box.notes || box.tagline || "")}</p>
          <p class="card__price">${esc(box.name)} · ${esc(fmtGrams(box.grams))} · ${esc(money(box.price))}</p>
          <div class="card__act" data-box-act>${actHTML(box)}</div>
        </div>
      </article>`;
  }
}
function updateCard(gi) {
  const el = $(`[data-card="${gi}"]`, gridEl);
  if (!el) return;
  const focused = document.activeElement?.dataset?.pick;
  el.outerHTML = cardHTML(groups[gi], gi);
  if (focused) $(`[data-pick="${CSS.escape(focused)}"]`, gridEl)?.focus({ preventScroll: true });
}
function updateBox() {
  const act = $("[data-box-act]", boxEl);
  if (act) act.innerHTML = actHTML(box);
}

// ======================================================================
// Order bar + panel
// ======================================================================
const bag = $("[data-bag]");
let lastCount = -1;
function renderBag() {
  const { count, cost } = totals();
  bag.hidden = count === 0;
  document.body.classList.toggle("has-bag", count > 0);
  $("[data-bag-packs]").textContent = packs(count);
  $("[data-bag-total]").textContent = money(cost);
  const badge = $("[data-count]");
  badge.hidden = count === 0;
  badge.textContent = String(count);
  if (count > lastCount && lastCount >= 0 && !reduce) {
    badge.animate([{ transform: "scale(1)" }, { transform: "scale(1.35)" }, { transform: "scale(1)" }], { duration: 400, easing: "cubic-bezier(.2,.8,.2,1)" });
  }
  lastCount = count;
}

const sheet = $("[data-sheet]");
const sheetBody = $("[data-sheet-body]");
function lineHTML(id, q) {
  const p = byId.get(id);
  const sub = p.box ? fmtGrams(p.grams) : `${p.meatName} · ${fmtGrams(p.grams)}`;
  return `<li class="line">
      <span class="line__shot">${shots[p.id] ? `<img src="${shots[p.id]}" alt="" width="72" height="90">` : pouchSVG(p, { mini: true })}</span>
      <div class="line__main">
        <p class="line__name">${esc(p.name)}</p>
        <p class="line__sub">${esc(sub)}</p>
        <div class="line__row">
          <div class="stepper stepper--sm" role="group" aria-label="${esc(fullName(p))}">
            <button type="button" data-dec="${esc(id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
            <output aria-live="polite">${q}</output>
            <button type="button" data-inc="${esc(id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
          </div>
          <button class="line__remove" type="button" data-remove="${esc(id)}">${esc(b("remove", { name: "" }).trim())}</button>
        </div>
      </div>
      <p class="line__price">${esc(money(p.price * q))}</p>
    </li>`;
}
function renderSheet() {
  if (!sheet.open) return;
  if (state.done) {
    const d = state.done;
    sheetBody.innerHTML = `<div class="done">
        <svg class="done__check" viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="29"/><path d="M20 33l8 8 16-18"/></svg>
        <h3 tabindex="-1">${esc(b("done_title", { name: d.name }))}</h3>
        <p>${esc(b("done_lead", { phone: d.phone }))}</p>
        <p class="done__code"><small>${esc(B.done_code)}</small><strong>${esc(d.code)}</strong></p>
        <button class="btn btn--light btn--wide" type="button" data-close>${esc(B.done_close)}</button>
      </div>`;
    return;
  }
  const ids = Object.keys(state.lines);
  if (!ids.length) {
    sheetBody.innerHTML = `<p class="empty">${esc(B.sheet_empty)}</p>`;
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
      <button class="btn btn--light btn--wide" type="submit" ${state.sending ? "disabled" : ""}>${state.sending ? `<span class="spin" aria-hidden="true"></span>${esc(B.sending)}` : `${esc(B.send)} · ${esc(money(cost))}`}</button>
      <p class="form__note">${esc(B.sheet_pay)}</p>
    </form>`;
  if (focusedName) $(`[name="${focusedName}"]`, sheetBody)?.focus();
}
function openSheet() {
  if (!sheet.open) sheet.showModal();
  renderSheet();
  (state.done ? $(".done h3", sheetBody) : $(".panel__close", sheet))?.focus();
}
const closeSheet = () => sheet.close();
sheet.addEventListener("close", () => {
  if (state.done) {
    state.done = null;
    renderAll();
  }
});
sheet.addEventListener("click", (e) => {
  if (e.target === sheet) closeSheet();
});
$$("[data-open-order]").forEach((btn) => btn.addEventListener("click", openSheet));

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
function groupIndexOf(id) {
  return groups.findIndex((g) => g.items.some((i) => i.id === id));
}
function setQty(id, n) {
  const q = clamp(n, 0, MAX_QTY);
  if (q) state.lines[id] = q;
  else delete state.lines[id];
  state.error = "";
  save();
  const gi = groupIndexOf(id);
  if (gi >= 0) updateCard(gi);
  if (box && id === box.id) updateBox();
  renderBag();
  renderSheet();
}
function renderAll() {
  renderGrid();
  renderBag();
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
    updateCard(gi);
    $(`[data-pick="${CSS.escape(el.dataset.pick)}"]`, gridEl)?.focus({ preventScroll: true });
    return;
  }
  const id = el.dataset.add || el.dataset.inc || el.dataset.dec || el.dataset.remove;
  const inSheet = Boolean(el.closest("[data-sheet]"));
  const scope = inSheet ? sheetBody : document;
  if (el.dataset.add || el.dataset.inc) {
    setQty(id, qty(id) + 1);
    if (!inSheet && reduce) toast(fullName(byId.get(id)));
    $(`[data-inc="${CSS.escape(id)}"]`, scope)?.focus({ preventScroll: true });
  } else if (el.dataset.dec) {
    setQty(id, qty(id) - 1);
    ($(`[data-dec="${CSS.escape(id)}"]`, scope) || $(`[data-add="${CSS.escape(id)}"]`, scope))?.focus({ preventScroll: true });
  } else if (el.dataset.remove) {
    setQty(id, 0);
  }
});
gridEl.addEventListener("keydown", (e) => {
  const radio = e.target.closest("[data-pick]");
  const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
  if (!radio || !step) return;
  e.preventDefault();
  const all = $$("[data-pick]", radio.parentElement);
  all[(all.indexOf(radio) + step + all.length) % all.length].click();
});

// ---------- Nav ----------
const nav = $("[data-nav]");
window.addEventListener("scroll", () => nav.classList.toggle("is-solid", window.scrollY > 20), { passive: true });

// ---------- Reveals ----------
const io = new IntersectionObserver(
  (entries) => {
    for (const en of entries) {
      if (!en.isIntersecting) continue;
      en.target.classList.add("is-in");
      io.unobserve(en.target);
    }
  },
  { rootMargin: "0px 0px -12% 0px" }
);
$$("[data-reveal]").forEach((el) => io.observe(el));

// ---------- First render ----------
renderGrid();
renderBag();
updateStory();

// ---------- 3D ----------
import("./beta3d.js")
  .then(async ({ createStage }) => {
    const s = createStage({
      canvas: $("[data-canvas]"),
      items: story.map((x) => x.product),
      meats: cfg.meats,
      logoUrl: cfg.logo,
      stripes: cfg.flavours.map((f) => f.band),
    });
    if (!s) return;
    await s.ready;
    shots = s.snapshots(products);
    stage = s;
    if (reduce) stage.setDamping(1000);
    fallbackEl.innerHTML = "";
    stage.setAngle(angleAt(storyProgress()), { instant: true, size: sizeAt(storyProgress()) });
    stage.start();
    document.documentElement.classList.add("has-3d");
    renderGrid();
  })
  .catch(() => {});
