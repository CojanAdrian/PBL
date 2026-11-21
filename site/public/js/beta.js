// The redesigned page (/betatest): the flavour list, the order panel and the page
// motion. The 3D pack lives in beta3d.js and is loaded on its own, so the page
// still works (with flat pack drawings) when WebGL is not available.
// Products, prices and the order endpoint are the same as the main page.
import { $, $$, esc, store, toast } from "./ui.js";
import { pouchSVG, prepareMenu, fmtGrams } from "./pouch.js";
import { t, packs } from "./i18n.js";

const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const desktop = () => window.matchMedia("(min-width: 900px)").matches;
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
// The list in the flavour section: each flavour, then the taster box.
const items = [...groups.map((g) => ({ group: g })), ...(box ? [{ box }] : [])];
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
const productOf = (item) => (item.box ? item.box : selected(item.group));
function totals() {
  let count = 0;
  let cost = 0;
  for (const [id, q] of Object.entries(state.lines)) {
    count += q;
    cost += byId.get(id).price * q;
  }
  return { count, cost };
}

// ---------- 3D scene (or flat drawings) ----------
let scene = null;
const fallbacks = $$("[data-fallback]");
function showFallback(p) {
  if (scene) return;
  for (const el of fallbacks) el.innerHTML = pouchSVG(p, { label: b("pack_3d", { name: fullName(p) }) });
}
const accentOf = (p) => (p.box ? "#ff5a1f" : p.accent || "#ff5a1f");
function setAccent(p) {
  document.documentElement.style.setProperty("--accent", accentOf(p));
  scene?.setAccent(accentOf(p));
}

// ---------- Flavour list ----------
const listEl = $("[data-list]");
const detailsEl = $("[data-details]");
let active = 0;

function renderList() {
  listEl.innerHTML = items
    .map((it, i) => {
      const p = productOf(it);
      const name = it.box ? B.taster_name : it.group.name;
      const from = it.box ? money(it.box.price) : b("hero_from", { price: money(Math.min(...it.group.items.map((x) => x.price))) });
      return `<button class="lab__item" type="button" role="tab" id="lab-tab-${i}" aria-selected="${i === active}" tabindex="${i === active ? 0 : -1}" data-item="${i}" style="--c:${esc(accentOf(p))}">
          <span class="lab__n">0${i + 1}</span><span class="lab__name">${esc(name)}</span><span class="lab__from">${esc(from)}</span>
        </button>`;
    })
    .join("");
}
function heatHTML(level) {
  const word = B[`heat_${Math.max(1, Math.min(5, level))}`];
  const bars = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}" style="--i:${i}"></i>`).join("");
  return `<p class="heat"><span class="heat__bars" role="img" aria-label="${esc(b("heat_aria", { word }))}">${bars}</span><span>${esc(word)}</span></p>`;
}
function actHTML(p) {
  const q = qty(p.id);
  if (!q) return `<button class="btn btn--fire btn--wide" type="button" data-add="${esc(p.id)}">${PLUS}<span>${esc(B.add)}</span><span class="btn__price">${esc(money(p.price))}</span></button>`;
  return `<div class="stepper" role="group" aria-label="${esc(fullName(p))}">
      <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
      <output aria-live="polite"><strong>${q}</strong><small>${esc(B.in_order)}</small></output>
      <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
    </div>`;
}
function renderDetails({ animate = false } = {}) {
  const it = items[active];
  const p = productOf(it);
  let html = "";
  if (it.box) {
    html = `<p class="det__tag">${esc(it.box.tagline || "")}</p>
      <p class="det__notes">${esc(it.box.notes || "")}</p>
      <p class="det__meta">${esc(it.box.name)} · ${esc(fmtGrams(it.box.grams))}</p>`;
  } else {
    const g = it.group;
    html = `<p class="det__tag">${esc(p.tagline || "")}</p>
      <p class="det__notes">${esc(p.notes || "")}</p>
      ${heatHTML(p.heat)}
      ${g.items.length > 1 ? `<div class="meat" role="radiogroup" aria-label="${esc(b("meat_aria", { name: g.name }))}">${g.items
        .map((x) => {
          const on = x.id === p.id;
          const n = qty(x.id);
          return `<button type="button" role="radio" aria-checked="${on}" tabindex="${on ? 0 : -1}" data-pick="${esc(x.id)}"><strong>${esc(x.meatName)}</strong><span>${esc(money(x.price))}</span>${n ? `<b>${n}</b>` : ""}</button>`;
        })
        .join("")}</div>` : ""}`;
  }
  detailsEl.innerHTML = `<div class="det">${html}<div class="det__act">${actHTML(p)}</div></div>`;
  detailsEl.setAttribute("aria-labelledby", `lab-tab-${active}`);
  if (animate && !reduce) {
    $$(".det > *", detailsEl).forEach((el, i) =>
      el.animate([{ opacity: 0, transform: "translateY(18px)" }, { opacity: 1, transform: "none" }], { duration: 500, delay: 60 + i * 45, easing: "cubic-bezier(.2,.8,.2,1)", fill: "backwards" })
    );
  }
}
function setActive(i, { burst = true } = {}) {
  i = Math.max(0, Math.min(items.length - 1, i));
  if (i === active && listEl.children.length) return;
  active = i;
  $$(".lab__item", listEl).forEach((el, k) => {
    el.setAttribute("aria-selected", String(k === i));
    el.tabIndex = k === i ? 0 : -1;
  });
  const p = productOf(items[i]);
  setAccent(p);
  renderDetails({ animate: true });
  if (scene) scene.setProduct(p, { mode: burst ? "burst" : "swap" });
  else showFallback(p);
}

// ---------- Order ticket + nav count ----------
const ticket = $("[data-ticket]");
let lastCount = -1;
function renderTicket() {
  const { count, cost } = totals();
  ticket.hidden = count === 0;
  document.body.classList.toggle("has-ticket", count > 0);
  $("[data-ticket-count]").textContent = String(count);
  $("[data-ticket-packs]").textContent = packs(count);
  $("[data-ticket-total]").textContent = money(cost);
  const badge = $("[data-count]");
  badge.hidden = count === 0;
  badge.textContent = String(count);
  if (count > lastCount && lastCount >= 0 && !reduce) {
    for (const el of [ticket, badge]) el.animate([{ transform: "scale(1)" }, { transform: "scale(1.15)" }, { transform: "scale(1)" }], { duration: 380, easing: "cubic-bezier(.3,1.5,.5,1)" });
  }
  lastCount = count;
}
// A small pack flies from the 3D pack to the order button.
function fly(p) {
  if (reduce) return;
  const from = $(desktop() ? ".lab__anchor" : ".lab__anchor").getBoundingClientRect();
  const to = (ticket.hidden ? $(".nav__order") : ticket).getBoundingClientRect();
  if (from.bottom < 0 || from.top > innerHeight) return;
  const node = document.createElement("div");
  node.className = "fly";
  node.innerHTML = pouchSVG(p, { mini: true });
  const x0 = from.left + from.width / 2 - 30;
  const y0 = from.top + from.height / 2 - 45;
  Object.assign(node.style, { left: `${x0}px`, top: `${y0}px` });
  document.body.appendChild(node);
  const dx = to.left + to.width / 2 - x0 - 30;
  const dy = to.top + to.height / 2 - y0 - 45;
  node
    .animate(
      [
        { transform: "translate(0,0) scale(.6) rotate(0)", opacity: 0 },
        { transform: `translate(${dx * 0.3}px, ${dy * 0.3 - 140}px) scale(1) rotate(-16deg)`, opacity: 1, offset: 0.35 },
        { transform: `translate(${dx}px, ${dy}px) scale(.25) rotate(-30deg)`, opacity: 0.4 },
      ],
      { duration: 800, easing: "cubic-bezier(.45,0,.55,1)" }
    )
    .finished.finally(() => node.remove());
}

// ---------- Order panel ----------
const sheet = $("[data-sheet]");
const sheetBody = $("[data-sheet-body]");
function lineHTML(id, q) {
  const p = byId.get(id);
  const sub = p.box ? fmtGrams(p.grams) : `${p.meatName} · ${fmtGrams(p.grams)}`;
  return `<li class="line">
      <span class="line__pack">${pouchSVG(p, { mini: true })}</span>
      <div class="line__main">
        <p class="line__name">${esc(p.name)}</p>
        <p class="line__sub">${esc(sub)} · ${esc(money(p.price))}</p>
        <div class="line__row">
          <div class="stepper stepper--sm" role="group" aria-label="${esc(fullName(p))}">
            <button type="button" data-dec="${esc(id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
            <output aria-live="polite">${q}</output>
            <button type="button" data-inc="${esc(id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
          </div>
          <button class="line__remove" type="button" data-remove="${esc(id)}" aria-label="${esc(b("remove", { name: fullName(p) }))}">${icon("M6 6l12 12M18 6L6 18", 16)}</button>
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
        <svg class="done__check" viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="29"/><path d="M19 33l9 9 17-19"/></svg>
        <h3 tabindex="-1">${esc(b("done_title", { name: d.name }))}</h3>
        <p>${esc(b("done_lead", { phone: d.phone }))}</p>
        <p class="done__code"><small>${esc(B.done_code)}</small><strong>${esc(d.code)}</strong></p>
        <button class="btn btn--fire btn--wide" type="button" data-close>${esc(B.done_close)}</button>
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
  const active = document.activeElement;
  const keepFocus = active?.closest?.("[data-sheet]") && active.name;
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
      <button class="btn btn--fire btn--wide btn--lg" type="submit" ${state.sending ? "disabled" : ""}>${state.sending ? `<span class="spin" aria-hidden="true"></span>` : ""}<span>${esc(state.sending ? B.sending : B.send)}</span><span class="btn__price">${esc(money(cost))}</span></button>
      <p class="form__note">${esc(B.sheet_pay)}</p>
    </form>`;
  if (keepFocus) $(`[name="${keepFocus}"]`, sheetBody)?.focus();
}
let lenis = null;
function openSheet() {
  if (!sheet.open) {
    sheet.showModal();
    lenis?.stop();
  }
  renderSheet();
  (state.done ? $(".done h3", sheetBody) : $(".panel__close", sheet))?.focus();
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
  const q = Math.max(0, Math.min(MAX_QTY, n));
  if (q) state.lines[id] = q;
  else delete state.lines[id];
  state.error = "";
  save();
  renderDetails();
  renderTicket();
  renderSheet();
}
function renderAll() {
  renderDetails();
  renderTicket();
  renderSheet();
}

document.addEventListener("click", (e) => {
  const tab = e.target.closest("[data-item]");
  if (tab) {
    const i = Number(tab.dataset.item);
    if (pinned && lenis) {
      // Scroll to that flavour's part of the pinned section; the scroll picks it.
      const lab = $("[data-lab]");
      const top = lab.getBoundingClientRect().top + window.scrollY;
      const span = lab.offsetHeight - innerHeight;
      lenis.scrollTo(top + span * ((i + 0.5) / items.length), { duration: 1.1 });
    } else setActive(i);
    return;
  }
  const el = e.target.closest("[data-add], [data-inc], [data-dec], [data-remove], [data-pick], [data-close]");
  if (!el) return;
  if (el.dataset.close !== undefined) return closeSheet();
  if (el.dataset.pick) {
    const g = items[active].group;
    if (!g || state.sel[g.name] === el.dataset.pick) return;
    state.sel[g.name] = el.dataset.pick;
    save();
    const p = productOf(items[active]);
    setAccent(p);
    renderDetails();
    scene?.setProduct(p, { mode: "swap" });
    showFallback(p);
    $(`[data-pick="${CSS.escape(el.dataset.pick)}"]`, detailsEl)?.focus({ preventScroll: true });
    return;
  }
  const id = el.dataset.add || el.dataset.inc || el.dataset.dec || el.dataset.remove;
  const inSheet = Boolean(el.closest("[data-sheet]"));
  const scope = inSheet ? sheetBody : detailsEl;
  if (el.dataset.add || el.dataset.inc) {
    setQty(id, qty(id) + 1);
    if (!inSheet) {
      scene?.pulse();
      fly(byId.get(id));
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
// Arrow keys: through the flavour tabs, and between beef and chicken.
document.addEventListener("keydown", (e) => {
  const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
  if (!step) return;
  const tab = e.target.closest?.("[data-item]");
  const radio = e.target.closest?.("[data-pick]");
  if (tab) {
    e.preventDefault();
    const next = $$("[data-item]", listEl)[(Number(tab.dataset.item) + step + items.length) % items.length];
    next.focus();
    next.click();
  } else if (radio) {
    e.preventDefault();
    const all = $$("[data-pick]", radio.parentElement);
    all[(all.indexOf(radio) + step + all.length) % all.length].click();
  }
});

// ---------- First render ----------
renderList();
active = 0;
setAccent(productOf(items[0]));
renderDetails();
renderTicket();
showFallback(productOf(items[0]));

// ---------- Nav ----------
const nav = $("[data-nav]");
let lastY = window.scrollY;
window.addEventListener(
  "scroll",
  () =>
    requestAnimationFrame(() => {
      const y = window.scrollY;
      nav.classList.toggle("is-solid", y > 40);
      if (y > 300 && y > lastY + 4 && !nav.contains(document.activeElement) && !sheet.open) nav.classList.add("is-hidden");
      else if (y < lastY - 4 || y <= 300) nav.classList.remove("is-hidden");
      lastY = y;
    }),
  { passive: true }
);

// ---------- Text splitting ----------
$$("[data-split]").forEach((el) => {
  const text = el.textContent.trim();
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${text
    .split(/\s+/)
    .map((w) => `<span class="w">${[...w].map((c) => `<span class="c">${esc(c)}</span>`).join("")}</span>`)
    .join(" ")}</span>`;
});
$$("[data-words]").forEach((el) => {
  const text = el.textContent.trim();
  el.innerHTML = `<span class="sr-only">${esc(text)}</span><span aria-hidden="true">${text
    .split(/\s+/)
    .map((w) => `<span class="w"><span class="wi">${esc(w)}</span></span>`)
    .join(" ")}</span>`;
});

// ---------- Motion ----------
let pinned = false;
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
    lenis = new window.Lenis({ lerp: 0.09, smoothWheel: true });
    lenis.on("scroll", ST.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    document.addEventListener("click", (e) => {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const target = a.getAttribute("href") === "#top" ? 0 : $(a.getAttribute("href"));
      if (target === null) return;
      e.preventDefault();
      lenis.scrollTo(target, { duration: 1.4 });
    });
  }

  // Intro: the headline letters rise, the rest follows.
  gsap
    .timeline({ defaults: { ease: "expo.out" }, delay: 0.15 })
    .from(".hero__title .c", { yPercent: 120, rotate: 8, duration: 1.3, stagger: 0.022 }, 0)
    .from(".hero [data-in]", { y: 26, opacity: 0, duration: 1.1, stagger: 0.1 }, 0.45)
    .from(".nav__in", { y: -20, opacity: 0, duration: 1, clearProps: "transform,opacity" }, 0.3);
  root.classList.add("is-intro");

  // Headings: word by word from behind a mask. Blocks: rise in.
  $$("[data-words]").forEach((el) =>
    gsap.from($$(".wi", el), { yPercent: 110, duration: 1.1, ease: "expo.out", stagger: 0.04, scrollTrigger: { trigger: el, start: "top 85%", once: true } })
  );
  ST.batch($$("main [data-in]").filter((el) => !el.closest(".hero")), {
    start: "top 90%",
    once: true,
    onEnter: (els) => gsap.from(els, { y: 36, opacity: 0, duration: 1, ease: "expo.out", stagger: 0.07 }),
  });

  // Flavours: on large screens the section is pinned and scrolling walks through the list.
  const mm = gsap.matchMedia();
  mm.add("(min-width: 900px)", () => {
    pinned = true;
    const bar = $("[data-progress]");
    const st = ST.create({
      trigger: "[data-lab]",
      start: "top top",
      end: "bottom bottom",
      onUpdate: (self) => {
        bar.style.transform = `scaleX(${self.progress})`;
        const i = Math.min(items.length - 1, Math.floor(self.progress * items.length * 0.9999));
        if (i !== active) setActive(i);
      },
    });
    return () => {
      pinned = false;
      st.kill();
    };
  });

  // Footer wordmark slides up letter by letter.
  const word = $("[data-foot-word]");
  if (word) {
    word.innerHTML = [...word.textContent].map((c) => `<span class="c">${c === " " ? "&nbsp;" : esc(c)}</span>`).join("");
    gsap.from($$(".c", word), { yPercent: 100, duration: 1.2, ease: "expo.out", stagger: 0.05, scrollTrigger: { trigger: word, start: "top 95%", once: true } });
  }

  window.addEventListener("load", () => ST.refresh());
  document.fonts?.ready.then(() => ST.refresh());
}
initMotion();

// Load the 3D pack.
import("./beta3d.js")
  .then(async ({ createScene }) => {
    scene = createScene({
      canvas: $("[data-stage]"),
      meats: cfg.meats,
      logoUrl: cfg.logo,
      reduce,
      flavours: cfg.flavours,
      texts: { ingredients: B.pack_ingredients, list: (B._ingredients || []).join(", "), soy: B.contains_soy, made: B.pack_made },
    });
    if (!scene) return;
    await scene.ready;
    scene.setAnchors($$("[data-anchor]").filter((el) => desktop() || !("desktopOnly" in el.dataset)));
    $$("[data-drag]").forEach((el) => scene.bindDrag(el));
    const p = productOf(items[active]);
    scene.setAccent(accentOf(p));
    scene.setProduct(p, { mode: "none" });
    scene.intro();
    for (const el of fallbacks) el.innerHTML = "";
    document.documentElement.classList.add("has-3d");
  })
  .catch(() => {
    scene = null;
  });
