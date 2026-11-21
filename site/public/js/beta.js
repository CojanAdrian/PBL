// The redesigned page (/betatest): one menu, one order sheet, one step to send.
// Products, prices and the order endpoint are the same as the main page.
import { $, $$, esc, reduce, store, toast } from "./ui.js";
import { pouchSVG, prepareMenu } from "./pouch.js";
import { t, packs } from "./i18n.js";

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
const icon = (d, size = 20) =>
  `<svg viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${d}"/></svg>`;
const MINUS = icon("M5 12h14");
const PLUS = icon("M12 5v14M5 12h14");

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
function setQty(id, n) {
  const q = Math.max(0, Math.min(MAX_QTY, n));
  if (q) state.lines[id] = q;
  else delete state.lines[id];
  state.error = "";
  save();
  renderAll();
}

// ---------- Menu ----------
const cardsEl = $("[data-cards]");
const boxesEl = $("[data-boxes]");

function heatHTML(level) {
  const word = B[`heat_${Math.max(1, Math.min(5, level))}`];
  const bars = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}"></i>`).join("");
  return `<p class="heat"><span class="heat__bars" role="img" aria-label="${esc(b("heat_aria", { word }))}">${bars}</span><span aria-hidden="true">${esc(word)}</span></p>`;
}

function actHTML(p, dark = false) {
  const q = qty(p.id);
  if (!q) return `<button class="btn ${dark ? "btn--red" : "btn--line"}" type="button" data-add="${esc(p.id)}">${PLUS}<span>${esc(B.add)}</span></button>`;
  return `<div class="stepper" role="group" aria-label="${esc(fullName(p))}">
      <button type="button" data-dec="${esc(p.id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
      <output aria-live="polite">${q}<small>${esc(B.in_order)}</small></output>
      <button type="button" data-inc="${esc(p.id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
    </div>`;
}

function renderCards() {
  cardsEl.innerHTML = groups
    .map((g, gi) => {
      const p = selected(g);
      const meats = g.items.length > 1
        ? `<div class="meat" role="radiogroup" aria-label="${esc(b("meat_aria", { name: g.name }))}">
            ${g.items
              .map((item) => {
                const on = item.id === p.id;
                const n = qty(item.id);
                return `<button type="button" role="radio" aria-checked="${on}" tabindex="${on ? 0 : -1}" data-pick="${esc(item.id)}" data-group="${gi}">
                  <strong>${esc(item.meatName)}</strong><span>${esc(money(item.price))}</span>${n ? `<b aria-label="${n} ${esc(B.in_order)}">${n}</b>` : ""}
                </button>`;
              })
              .join("")}
          </div>`
        : `<p class="card__price"><strong>${esc(money(p.price))}</strong></p>`;
      return `<article class="card" style="--band:${esc(p.band)}">
          <div class="card__img">${pouchSVG(p, { label: t("pouch", { name: fullName(p) }) })}</div>
          <div class="card__body">
            <h3>${esc(g.name)}</h3>
            ${heatHTML(p.heat)}
            <p class="card__notes">${esc(p.notes || p.tagline)}</p>
            ${meats}
            <div class="card__act">${actHTML(p)}</div>
          </div>
        </article>`;
    })
    .join("");

  boxesEl.innerHTML = (cfg.boxes || [])
    .map(
      (box) => `<article class="boxcard">
          <div class="boxcard__img">${pouchSVG(box, { label: box.name })}</div>
          <div>
            <p class="boxcard__kicker">${esc(B.box_kicker)}</p>
            <h3>${esc(box.name)}</h3>
            <p class="boxcard__notes">${esc(box.notes || box.tagline)}</p>
          </div>
          <div class="boxcard__buy">
            <p class="boxcard__price">${esc(money(box.price))}</p>
            ${actHTML(box, true)}
          </div>
        </article>`
    )
    .join("");
}

// ---------- Order bar + header count ----------
const bar = $("[data-bar]");
let lastCount = 0;
function renderBar() {
  const { count, cost } = totals();
  bar.hidden = count === 0;
  document.body.classList.toggle("has-bar", count > 0);
  $("[data-bar-packs]").textContent = packs(count);
  $("[data-bar-total]").textContent = money(cost);
  const badge = $("[data-count]");
  badge.hidden = count === 0;
  badge.textContent = String(count);
  if (count > lastCount && lastCount > 0 && !reduce) {
    bar.classList.remove("is-bump");
    void bar.offsetWidth;
    bar.classList.add("is-bump");
  }
  lastCount = count;
}

// The one bit of motion on the page: the pack drops into your order.
function fly(fromEl) {
  if (reduce || !fromEl || bar.hidden) return;
  const from = fromEl.getBoundingClientRect();
  const to = bar.getBoundingClientRect();
  if (!from.width || from.bottom < 0 || from.top > innerHeight) return;
  const node = document.createElement("div");
  node.className = "fly";
  node.innerHTML = fromEl.outerHTML;
  Object.assign(node.style, { left: `${from.left}px`, top: `${from.top}px`, width: `${from.width}px` });
  document.body.appendChild(node);
  const dx = to.left + 28 - from.left - from.width / 2;
  const dy = to.top + to.height / 2 - from.top - from.height / 2;
  node
    .animate(
      [
        { transform: "translate(0,0) scale(1) rotate(0deg)", opacity: 1 },
        { transform: `translate(${dx * 0.5}px, ${dy * 0.5 - 60}px) scale(0.6) rotate(-8deg)`, opacity: 1, offset: 0.5 },
        { transform: `translate(${dx}px, ${dy}px) scale(0.18) rotate(-14deg)`, opacity: 0.2 },
      ],
      { duration: 520, easing: "cubic-bezier(0.5, 0, 0.6, 1)" }
    )
    .finished.finally(() => node.remove());
}

// ---------- Order sheet ----------
const sheet = $("[data-sheet]");
const sheetBody = $("[data-sheet-body]");

function lineHTML(id, q) {
  const p = byId.get(id);
  const sub = p.box ? p.tagline : `${p.meatName} · ${p.grams} g`;
  return `<li class="line">
      ${pouchSVG(p, { mini: true })}
      <p class="line__name">${esc(p.name)}<small>${esc(sub)}</small></p>
      <p class="line__price">${esc(money(p.price * q))}</p>
      <div class="stepper stepper--sm" role="group" aria-label="${esc(fullName(p))}">
        <button type="button" data-dec="${esc(id)}" aria-label="${esc(b("fewer", { name: fullName(p) }))}">${MINUS}</button>
        <output aria-live="polite">${q}</output>
        <button type="button" data-inc="${esc(id)}" aria-label="${esc(b("more", { name: fullName(p) }))}" ${q >= MAX_QTY ? "disabled" : ""}>${PLUS}</button>
      </div>
      <button class="line__remove" type="button" data-remove="${esc(id)}" aria-label="${esc(b("remove", { name: fullName(p) }))}">${icon("M6 6l12 12M18 6L6 18", 18)}</button>
    </li>`;
}

function renderSheet() {
  if (!sheet.open) return;
  if (state.done) {
    const d = state.done;
    sheetBody.innerHTML = `<div class="done">
        <span class="done__mark">${icon("M5 12.5l4.5 4.5L19 7.5", 28)}</span>
        <h3 tabindex="-1">${esc(b("done_title", { name: d.name }))}</h3>
        <p>${esc(b("done_lead", { phone: d.phone }))}</p>
        <p class="done__code"><small>${esc(B.done_code)}</small><strong>${esc(d.code)}</strong></p>
        <button class="btn btn--red" type="button" data-close>${esc(B.done_close)}</button>
      </div>`;
    return;
  }
  const ids = Object.keys(state.lines);
  if (!ids.length) {
    sheetBody.innerHTML = `<p class="sheet__empty">${esc(B.sheet_empty)}</p>`;
    return;
  }
  const { cost } = totals();
  const err = (k) => esc(state.errors[k] || "");
  const keepFocus = document.activeElement?.closest?.("[data-sheet]") && document.activeElement.name;
  sheetBody.innerHTML = `
    <ul class="lines">${ids.map((id) => lineHTML(id, state.lines[id])).join("")}</ul>
    <p class="sheet__total"><span>${esc(B.total)}</span><strong>${esc(money(cost))}</strong></p>
    ${state.errors.items ? `<p class="sheet__err" role="alert">${err("items")}</p>` : ""}
    <form class="form" novalidate data-form>
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
        <textarea class="input" id="b-note" name="note" rows="3" maxlength="500" placeholder="${esc(B.note_ph)}">${esc(state.d.note)}</textarea>
      </div>
      <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
      ${state.error ? `<p class="sheet__err" role="alert">${esc(state.error)}</p>` : ""}
      <button class="btn btn--red" type="submit" ${state.sending ? "disabled" : ""}>${esc(state.sending ? B.sending : B.send)} · ${esc(money(cost))}</button>
      <p class="sheet__pay">${esc(B.sheet_pay)}</p>
    </form>`;
  if (keepFocus) $(`[name="${keepFocus}"]`, sheetBody)?.focus();
}

function openSheet() {
  if (!sheet.open) sheet.showModal();
  renderSheet();
  const focusEl = state.done ? $(".done h3", sheetBody) : $(".sheet__close", sheet);
  focusEl?.focus();
}
function closeSheet() {
  sheet.close();
}
sheet.addEventListener("close", () => {
  if (state.done) {
    state.done = null;
    renderAll();
  }
});
// A click on the dimmed area outside the sheet closes it.
sheet.addEventListener("click", (e) => {
  if (e.target === sheet) closeSheet();
});
$$("[data-open-order]").forEach((btn) => btn.addEventListener("click", openSheet));

// ---------- Validation and sending ----------
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

// ---------- Clicks (menu, sheet) ----------
document.addEventListener("click", (e) => {
  const el = e.target.closest("[data-add], [data-inc], [data-dec], [data-remove], [data-pick], [data-close]");
  if (!el) return;
  if (el.dataset.close !== undefined) return closeSheet();
  if (el.dataset.pick) {
    const g = groups[Number(el.dataset.group)];
    state.sel[g.name] = el.dataset.pick;
    save();
    renderCards();
    $(`[data-pick="${CSS.escape(el.dataset.pick)}"]`, cardsEl)?.focus();
    return;
  }
  const id = el.dataset.add || el.dataset.inc || el.dataset.dec || el.dataset.remove;
  const inMenu = !el.closest("[data-sheet]");
  const pouch = inMenu ? el.closest(".card, .boxcard")?.querySelector(".pouch") : null;
  if (el.dataset.add || el.dataset.inc) {
    setQty(id, qty(id) + 1);
    if (inMenu) {
      fly(pouch);
      if (reduce) toast(fullName(byId.get(id)));
      // Keep keyboard focus on the same control after the card re-renders.
      $(`[data-inc="${CSS.escape(id)}"]`, cardsEl.parentElement)?.focus();
    }
  } else if (el.dataset.dec) {
    setQty(id, qty(id) - 1);
    const next = $(`[data-dec="${CSS.escape(id)}"]`, inMenu ? cardsEl.parentElement : sheetBody) || $(`[data-add="${CSS.escape(id)}"]`, cardsEl.parentElement);
    next?.focus();
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

function renderAll() {
  renderCards();
  renderBar();
  renderSheet();
}
renderAll();
