// The preorder builder: pick flavours and sizes, see the box fill up live,
// leave your details, review, send.
import { $, $$, esc, reduce, store, toast, scrollToEl, arrowNav } from "./ui.js";
import { pouchSVG, heatBars, fmtGrams } from "./pouch.js";
import { burst } from "./embers.js";

const STORE_KEY = "bigeds.box.v2";
const MAX_TRAY = 18;

const icon = (path, size = 20, width = 2.4) =>
  `<svg viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="${width}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${path}</svg>`;
const ICON = {
  plus: icon('<path d="M12 5v14M5 12h14"/>'),
  minus: icon('<path d="M5 12h14"/>'),
  close: icon('<path d="M6 6l12 12M18 6L6 18"/>'),
  arrow: icon('<path d="M5 12h14M13 6l6 6-6 6"/>'),
  check: icon('<path d="M5 12.5l4.5 4.5L19 7.5"/>', 16, 3),
  pickup: icon('<path d="M4 9l1.5-4h13L20 9M4 9v10h16V9M4 9h16M9 14h6"/>', 22, 2),
  truck: icon('<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7M7 19a2 2 0 100-4 2 2 0 000 4zM17 19a2 2 0 100-4 2 2 0 000 4z"/>', 22, 2),
};

export function initBuilder(cfg, mount) {
  const byId = new Map(cfg.flavours.map((f) => [f.id, f]));
  const cur = cfg.currency;
  // Price of one pack: the preset table first, then base + per gram for custom sizes.
  const price = (grams) => {
    const fixed = cfg.prices && cfg.prices[String(grams)];
    if (Number.isFinite(fixed)) return fixed;
    const { base = 0, perGram = 0 } = cfg.customPrice || {};
    return Math.round(base + perGram * grams);
  };
  const clampQty = (n) => Math.max(1, Math.min(cfg.limits.maxPacksPerLine, n));
  const { min: CMIN, max: CMAX, step: CSTEP } = cfg.custom;
  const num = (n) => Number(n).toLocaleString("en-US");

  const state = {
    lines: [], // { flavour, grams, qty, custom }
    step: 1,
    d: { name: "", phone: "", email: "", delivery: "pickup", address: "", date: "", note: "" },
    sending: false,
    error: "",
    done: null,
  };

  // ---------- Persistence ----------
  function load() {
    try {
      const saved = JSON.parse(store.get(STORE_KEY) || "null");
      if (!saved) return;
      state.lines = (saved.lines || [])
        .filter((l) => byId.has(l.flavour) && Number.isInteger(l.grams) && l.grams >= CMIN && l.grams <= CMAX)
        .map((l) => ({ flavour: l.flavour, grams: l.grams, qty: clampQty(Number(l.qty) || 1), custom: !cfg.sizes.includes(l.grams) || Boolean(l.custom) }));
      Object.assign(state.d, { name: "", phone: "", email: "", address: "", note: "" }, saved.d || {});
      if (state.d.delivery !== "delivery") state.d.delivery = "pickup";
    } catch {
      /* start fresh */
    }
  }
  let saveTimer;
  function save() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => store.set(STORE_KEY, JSON.stringify({ lines: state.lines, d: state.d })), 200);
  }

  // ---------- Maths ----------
  const lineOf = (id) => state.lines.find((l) => l.flavour === id);
  function totals() {
    let grams = 0;
    let packs = 0;
    let cost = 0;
    for (const l of state.lines) {
      grams += l.grams * l.qty;
      packs += l.qty;
      cost += price(l.grams) * l.qty;
    }
    return { grams, packs, cost };
  }

  // ---------- Skeleton ----------
  const presets = [
    { id: "taster", title: "Taster set", lines: cfg.flavours.map((f) => ({ flavour: f.id, grams: cfg.sizes[0], qty: 1 })) },
    { id: "lineup", title: "The full lineup", lines: cfg.flavours.map((f) => ({ flavour: f.id, grams: cfg.popularSize, qty: 1 })) },
    {
      id: "heat",
      title: "Heat seeker",
      lines: [
        { flavour: "spicy", grams: cfg.popularSize, qty: 2 },
        { flavour: "black-pepper", grams: cfg.popularSize, qty: 1 },
      ],
    },
  ]
    .map((p) => ({ ...p, lines: p.lines.filter((l) => byId.has(l.flavour)) }))
    .filter((p) => p.lines.length > 0);
  const describePreset = (p) =>
    p.id === "heat"
      ? p.lines.map((l) => `${l.qty} × ${byId.get(l.flavour).name}`).join(", ") + `, ${cfg.popularSize} g`
      : p.id === "taster"
        ? `One of every flavour, ${cfg.sizes[0]} g each`
        : `Every flavour, ${cfg.popularSize} g each`;

  mount.innerHTML = `
  <div class="builder__main">
    <ol class="steps" aria-label="Preorder progress">
      <li class="stepi" data-s="1"><span class="stepi__n">1</span><span class="stepi__t">Your box</span></li>
      <li class="stepi" data-s="2"><span class="stepi__n">2</span><span class="stepi__t">Your details</span></li>
      <li class="stepi" data-s="3"><span class="stepi__n">3</span><span class="stepi__t">Confirm</span></li>
    </ol>

    <section class="panel" data-panel="1" aria-labelledby="p1-title">
      <div class="panel__head">
        <h3 class="panel__title" id="p1-title" tabindex="-1">Fill your box</h3>
        <p class="panel__sub">Start with a quick pick, or add flavours one by one. Every pack size and amount is up to you.</p>
        <ol class="howto" aria-label="How it works">
          <li><b>1</b><span>Tap <strong>Add</strong> on a flavour</span></li>
          <li><b>2</b><span>Pick a size and how many</span></li>
          <li><b>3</b><span>Press <strong>Continue</strong></span></li>
        </ol>
      </div>
      <p class="lbl">Quick starts</p>
      <div class="presets">
        ${presets.map((p) => `<button class="preset" type="button" data-preset="${p.id}"><strong>${esc(p.title)}</strong><span>${esc(describePreset(p))}</span></button>`).join("")}
      </div>
      <p class="lbl">Or pick your flavours</p>
      <ul class="frows">${cfg.flavours.map(rowHTML).join("")}</ul>
      <div class="actions" style="justify-content:flex-end">
        <button class="btn btn--primary btn--lg" type="button" data-next aria-disabled="true"><span>Continue to details</span>${ICON.arrow}</button>
      </div>
    </section>

    <section class="panel" data-panel="2" aria-labelledby="p2-title" hidden>
      <div class="panel__head">
        <h3 class="panel__title" id="p2-title" tabindex="-1">Your details</h3>
        <p class="panel__sub">So Ed knows who the box is for and how to reach you.</p>
      </div>
      <form class="form" novalidate data-form>
        <div class="form__row">
          <div class="field">
            <label class="lbl" for="f-name">Your name <span class="req" aria-hidden="true">*</span></label>
            <input class="input" id="f-name" name="name" autocomplete="name" maxlength="80" required aria-describedby="e-name">
            <p class="err" id="e-name" role="alert"></p>
          </div>
          <div class="field">
            <label class="lbl" for="f-phone">Phone (optional)</label>
            <input class="input" id="f-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+373 …" maxlength="30" aria-describedby="h-phone e-phone">
            <p class="hint" id="h-phone">Add a phone number, an email, or both, so Ed can confirm your order.</p>
            <p class="err" id="e-phone" role="alert"></p>
          </div>
        </div>
        <div class="field">
          <label class="lbl" for="f-email">Email (optional)</label>
          <input class="input" id="f-email" name="email" type="email" inputmode="email" autocomplete="email" maxlength="254" aria-describedby="e-email">
          <p class="err" id="e-email" role="alert"></p>
        </div>
        <div class="field">
          <span class="lbl" id="l-delivery">How would you like to get it?</span>
          <div class="choices" role="radiogroup" aria-labelledby="l-delivery" data-delivery>
            <button class="choice" type="button" role="radio" aria-checked="true" data-v="pickup">${ICON.pickup}<strong>Pickup</strong><span>Collect it from Ed</span></button>
            <button class="choice" type="button" role="radio" aria-checked="false" tabindex="-1" data-v="delivery">${ICON.truck}<strong>Delivery</strong><span>Brought to your door</span></button>
          </div>
          <p class="err" id="e-delivery" role="alert"></p>
        </div>
        <div class="grow" data-addr>
          <div class="grow__clip" style="padding:6px;margin:-6px">
            <div class="field">
              <label class="lbl" for="f-address">Delivery address or area <span class="req" aria-hidden="true">*</span></label>
              <textarea class="input" id="f-address" name="address" rows="2" maxlength="300" autocomplete="street-address" aria-describedby="e-address"></textarea>
              <p class="err" id="e-address" role="alert"></p>
            </div>
          </div>
        </div>
        <div class="field">
          <label class="lbl" for="f-date">Preferred date (optional)</label>
          <input class="input" id="f-date" name="date" type="date" aria-describedby="h-date e-date" style="max-width:260px">
          <p class="hint" id="h-date">Ed will do his best, and confirms the real date with you.</p>
          <p class="err" id="e-date" role="alert"></p>
        </div>
        <div class="field">
          <label class="lbl" for="f-note">Anything Ed should know? (optional)</label>
          <textarea class="input" id="f-note" name="note" rows="3" maxlength="500" aria-describedby="h-note"></textarea>
          <p class="hint" id="h-note"><span data-note-count>0</span> / 500</p>
        </div>
        <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="actions">
          <button class="btn btn--ghost" type="button" data-back><span>Back</span></button>
          <button class="btn btn--primary btn--lg" type="submit"><span>Review order</span>${ICON.arrow}</button>
        </div>
      </form>
    </section>

    <section class="panel" data-panel="3" aria-labelledby="p3-title" hidden>
      <div class="panel__head">
        <h3 class="panel__title" id="p3-title" tabindex="-1">Check and confirm</h3>
        <p class="panel__sub">One last look. Nothing is charged online.</p>
      </div>
      <div class="review" data-review></div>
    </section>

    <section class="panel done" data-panel="done" aria-labelledby="pd-title" hidden>
      <canvas class="burst" aria-hidden="true"></canvas>
      <div data-done></div>
    </section>
  </div>

  <aside class="builder__aside" aria-label="Your box summary">
    <div class="boxcard">
      <div class="boxcard__head">
        <h3>Your box</h3>
        <span class="badge is-empty" data-badge>0 packs</span>
      </div>
      <div class="tray is-empty" data-tray>
        <div class="tray__stack" data-stack></div>
        <p class="tray__empty">Your box is empty.<br>Pick a flavour or try a quick start.</p>
      </div>
      <div class="totals">
        <div><p class="totals__lbl">Weight</p><p class="totals__val"><span data-weight data-v="0">0</span><small>g</small></p></div>
        <div><p class="totals__lbl">Total</p><p class="totals__val totals__val--price"><span data-total data-v="0">0</span><small>${esc(cur)}</small></p></div>
      </div>
      <div class="split" data-split aria-hidden="true"></div>
      <ul class="legend" data-legend></ul>
      <div class="boxcard__cta">
        <button class="btn btn--primary btn--lg" type="button" data-next aria-disabled="true"><span data-next-label>Continue to details</span>${ICON.arrow}</button>
        <p class="boxcard__help" data-help></p>
      </div>
      <p class="boxcard__pay">${esc(cfg.paymentNote)}</p>
    </div>
  </aside>

  <div class="mobilebar" data-mobilebar>
    <div class="mobilebar__sum"><small data-mb-info>Your box is empty</small><strong data-mb-total>0 ${esc(cur)}</strong></div>
    <button class="btn btn--primary" type="button" data-next aria-disabled="true"><span data-next-label>Continue</span>${ICON.arrow}</button>
  </div>`;

  function rowHTML(f) {
    const id = esc(f.id);
    return `<li class="frow" data-id="${id}" style="--accent:${esc(f.accent)}">
      <div class="frow__top">
        <div class="frow__pouch">${pouchSVG(f, { mini: true })}</div>
        <div class="frow__info">
          <h4 class="frow__name">${esc(f.name)}</h4>
          <p class="frow__tag">${esc(f.tagline)}</p>
          ${heatBars(f.heat, { small: true })}
        </div>
        <div class="frow__act">
          <button class="btn-add" type="button" data-act="add" aria-label="Add ${esc(f.name)} to your box">${ICON.plus}<span>Add</span></button>
          <button class="btn-icon" type="button" data-act="remove" aria-label="Remove ${esc(f.name)} from your box" hidden>${ICON.close}</button>
        </div>
      </div>
      <div class="grow" data-ctl>
        <div class="grow__clip">
          <div class="frow__ctl-in">
            <div class="field field--size">
              <span class="lbl" id="sz-${id}">Pack size</span>
              <div class="seg" role="radiogroup" aria-labelledby="sz-${id}">
                ${cfg.sizes.map((s) => `<button type="button" role="radio" aria-checked="false" tabindex="-1" data-act="size" data-g="${s}">${s} g${s === cfg.popularSize ? '<span class="pop">Popular</span>' : ""}</button>`).join("")}
                <button type="button" role="radio" aria-checked="false" tabindex="-1" data-act="size" data-g="custom">Custom</button>
              </div>
            </div>
            <div class="field">
              <span class="lbl" id="q-${id}">Packs</span>
              <div class="stepper" role="group" aria-labelledby="q-${id}">
                <button type="button" data-act="dec" aria-label="One fewer ${esc(f.name)} pack">${ICON.minus}</button>
                <output aria-live="polite" data-qty>1</output>
                <button type="button" data-act="inc" aria-label="One more ${esc(f.name)} pack">${ICON.plus}</button>
              </div>
            </div>
            <div class="frow__price"><span data-price>0</span><small>${esc(cur)}</small></div>
          </div>
          <div class="grow" data-custom>
            <div class="grow__clip">
              <div class="custom-in">
                <span class="lbl">Custom size per pack</span>
                <input class="range" type="range" min="${CMIN}" max="${CMAX}" step="${CSTEP}" data-act="range" aria-label="Custom pack size in grams for ${esc(f.name)}">
                <label class="num"><input type="number" min="${CMIN}" max="${CMAX}" step="${CSTEP}" inputmode="numeric" data-act="gnum" aria-label="Grams per pack"><span>g</span></label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </li>`;
  }

  // ---------- Element handles ----------
  const el = {
    steps: $$(".stepi", mount),
    panels: $$("[data-panel]", mount),
    rows: new Map($$(".frow", mount).map((li) => [li.dataset.id, li])),
    form: $("[data-form]", mount),
    review: $("[data-review]", mount),
    done: $("[data-done]", mount),
    tray: $("[data-tray]", mount),
    stack: $("[data-stack]", mount),
    badge: $("[data-badge]", mount),
    weight: $("[data-weight]", mount),
    total: $("[data-total]", mount),
    split: $("[data-split]", mount),
    legend: $("[data-legend]", mount),
    help: $("[data-help]", mount),
    bar: $("[data-mobilebar]", mount),
    nextBtns: $$("[data-next]", mount),
  };
  const field = (name) => el.form.elements[name];

  // ---------- Animated numbers ----------
  function tweenNum(span, to) {
    const from = Number(span.dataset.v || 0);
    span.dataset.v = String(to);
    cancelAnimationFrame(span._raf);
    if (reduce || from === to) {
      span.textContent = num(to);
      return;
    }
    const t0 = performance.now();
    const step = (now) => {
      const p = Math.min(1, (now - t0) / 650);
      span.textContent = num(Math.round(from + (to - from) * (1 - Math.pow(1 - p, 4))));
      if (p < 1) span._raf = requestAnimationFrame(step);
    };
    span._raf = requestAnimationFrame(step);
  }
  function bump(node) {
    if (reduce) return;
    node.classList.remove("bump");
    void node.offsetWidth;
    node.classList.add("bump");
  }

  // ---------- Rendering: rows ----------
  function syncRows() {
    for (const f of cfg.flavours) {
      const li = el.rows.get(f.id);
      const line = lineOf(f.id);
      const open = Boolean(line);
      li.classList.toggle("is-in", open);
      const ctl = $("[data-ctl]", li);
      ctl.classList.toggle("is-open", open);
      ctl.inert = !open;
      $('[data-act="add"]', li).hidden = open;
      $('[data-act="remove"]', li).hidden = !open;
      if (!line) continue;
      for (const b of $$('[data-act="size"]', li)) {
        const on = b.dataset.g === "custom" ? line.custom : !line.custom && Number(b.dataset.g) === line.grams;
        b.setAttribute("aria-checked", String(on));
        b.tabIndex = on ? 0 : -1;
      }
      const qty = $("[data-qty]", li);
      if (qty.textContent !== String(line.qty)) {
        qty.textContent = String(line.qty);
        bump(qty);
      }
      $('[data-act="dec"]', li).disabled = line.qty <= 1;
      $('[data-act="inc"]', li).disabled = line.qty >= cfg.limits.maxPacksPerLine;
      $("[data-price]", li).textContent = num(price(line.grams) * line.qty);
      const custom = $("[data-custom]", li);
      custom.classList.toggle("is-open", line.custom);
      custom.inert = !line.custom;
      const range = $('[data-act="range"]', li);
      const gnum = $('[data-act="gnum"]', li);
      if (document.activeElement !== range) range.value = String(line.grams);
      if (document.activeElement !== gnum) gnum.value = String(line.grams);
      range.style.setProperty("--fill", `${((line.grams - CMIN) / (CMAX - CMIN)) * 100}%`);
    }
  }

  // ---------- Rendering: box card ----------
  const trayMap = new Map();
  let moreEl = null;
  // Pouches grow with the pack size, so you can see a 50 g from a 150 g.
  const trayScale = (grams) => Math.max(0.8, Math.min(1.6, 0.75 + (grams / 150) * 0.45)).toFixed(3);
  function renderTray() {
    const want = [];
    for (const l of state.lines) for (let i = 0; i < l.qty; i++) want.push({ key: `${l.flavour}-${i}`, flavour: byId.get(l.flavour), grams: l.grams });
    const shown = want.slice(0, MAX_TRAY);
    const keep = new Set(shown.map((w) => w.key));
    for (const [key, node] of trayMap) {
      if (keep.has(key)) continue;
      trayMap.delete(key);
      node.classList.add("is-leaving");
      setTimeout(() => node.remove(), 320);
    }
    // The more packs, the more they overlap and the smaller they get, so the box always fits.
    const n = shown.length;
    el.stack.style.setProperty("--ovn", n <= 5 ? 0.26 : n <= 9 ? 0.4 : n <= 14 ? 0.5 : 0.58);
    el.stack.style.setProperty("--kn", n <= 6 ? 1 : n <= 12 ? 0.86 : 0.72);

    let prev = null;
    shown.forEach((w, i) => {
      let node = trayMap.get(w.key);
      if (!node) {
        node = document.createElement("div");
        node.className = "tp";
        node.innerHTML = pouchSVG(w.flavour, { grams: w.grams });
        node.dataset.g = String(w.grams);
        // Once it has dropped in, stop the animation so re-ordering never replays it.
        node.addEventListener("animationend", () => node.classList.add("is-settled"), { once: true });
        if (reduce) node.classList.add("is-settled");
        trayMap.set(w.key, node);
      } else if (node.dataset.g !== String(w.grams)) {
        node.dataset.g = String(w.grams);
        node.innerHTML = pouchSVG(w.flavour, { grams: w.grams });
      }
      node.dataset.label = `${w.flavour.name} · ${w.grams} g`;
      node.style.setProperty("--s", trayScale(w.grams));
      node.style.setProperty("--rot", `${(((i * 37) % 7) - 3) * 1.4}deg`);
      node.style.setProperty("--z", String(i + 1));
      node.style.setProperty("--glow", w.flavour.accent);
      // Keep the DOM in the same order as the pouches are shown (the hover effect depends on it).
      const ref = prev ? prev.nextSibling : el.stack.firstChild;
      if (node.parentNode !== el.stack || ref !== node) el.stack.insertBefore(node, ref);
      prev = node;
    });
    const extra = want.length - shown.length;
    if (extra > 0) {
      if (!moreEl) {
        moreEl = document.createElement("span");
        moreEl.className = "tray__more";
        el.tray.appendChild(moreEl);
      }
      moreEl.textContent = `+${extra} more`;
    } else if (moreEl) {
      moreEl.remove();
      moreEl = null;
    }
    el.tray.classList.toggle("is-empty", want.length === 0);
  }

  let lastPacks = 0;
  function renderAside() {
    const t = totals();
    el.badge.textContent = `${t.packs} ${t.packs === 1 ? "pack" : "packs"}`;
    el.badge.classList.toggle("is-empty", t.packs === 0);
    if (t.packs !== lastPacks) bump(el.badge);
    lastPacks = t.packs;
    tweenNum(el.weight, t.grams);
    tweenNum(el.total, t.cost);

    el.split.innerHTML = state.lines
      .map((l) => `<span style="flex-grow:${l.grams * l.qty};background:${esc(byId.get(l.flavour).accent)}"></span>`)
      .join("");
    el.legend.innerHTML = state.lines
      .map((l) => {
        const f = byId.get(l.flavour);
        const pct = Math.round(((l.grams * l.qty) / t.grams) * 100);
        return `<li><i style="background:${esc(f.accent)}"></i>${esc(f.name)} ${pct}%</li>`;
      })
      .join("");
    renderTray();
    renderCTAs();
    window.dispatchEvent(new CustomEvent("box:change", { detail: { packs: t.packs } }));
  }

  const tooBig = () => totals().grams > cfg.limits.maxTotalGrams;
  function renderCTAs() {
    const t = totals();
    const labels = { 1: "Continue to details", 2: "Review order", 3: state.sending ? "Sending…" : "Confirm preorder" };
    const helps = {
      1: t.packs ? "You can change everything before sending." : "Add at least one pack to continue.",
      2: "No payment now. You'll review everything next.",
      3: "Nothing is charged online.",
    };
    const blocked = (state.step === 1 && (t.packs === 0 || tooBig())) || state.sending;
    for (const btn of el.nextBtns) {
      btn.setAttribute("aria-disabled", String(blocked));
      const label = $("[data-next-label]", btn);
      if (label) label.textContent = state.step === 1 && btn.closest(".mobilebar") ? "Continue" : labels[state.step] || "";
    }
    el.help.textContent = state.step === 1 && tooBig() ? "That's a big box! For large orders please contact Ed directly." : helps[state.step] || "";
    $("[data-mb-info]", el.bar).textContent = t.packs ? `${t.packs} ${t.packs === 1 ? "pack" : "packs"} · ${fmtGrams(t.grams)}` : "Your box is empty";
    $("[data-mb-total]", el.bar).textContent = `${num(t.cost)} ${cur}`;
  }

  // ---------- Steps ----------
  function renderSteps() {
    const n = state.step === "done" ? 4 : state.step;
    for (const li of el.steps) {
      const s = Number(li.dataset.s);
      li.classList.toggle("is-current", s === n);
      li.classList.toggle("is-done", s < n);
      li.querySelector(".stepi__n").innerHTML = s < n ? ICON.check : String(s);
      if (s === n) li.setAttribute("aria-current", "step");
      else li.removeAttribute("aria-current");
    }
    $(".steps", mount).hidden = state.step === "done";
  }

  function goStep(next, { focus = true } = {}) {
    const dir = next === "done" || (typeof state.step === "number" && next >= state.step) ? 1 : -1;
    state.step = next;
    mount.classList.toggle("is-done", next === "done");
    for (const panel of el.panels) {
      const show = panel.dataset.panel === String(next);
      panel.hidden = !show;
      panel.classList.remove("is-in");
      if (show) {
        panel.style.setProperty("--dx", String(dir));
        void panel.offsetWidth;
        panel.classList.add("is-in");
      }
    }
    if (next === 2) syncDetails();
    if (next === 3) renderReview();
    renderSteps();
    renderCTAs();
    updateBar();
    if (focus) {
      scrollToEl($(".steps", mount).hidden ? mount : $(".steps", mount), -110);
      const title = $(`[data-panel="${next}"] .panel__title, [data-panel="${next}"] h3`, mount);
      setTimeout(() => title?.focus({ preventScroll: true }), 60);
    }
  }

  // ---------- Box edits ----------
  function addFlavour(id, { announce = true } = {}) {
    const f = byId.get(id);
    if (!f) return;
    const line = lineOf(id);
    if (line) {
      line.qty = clampQty(line.qty + 1);
      if (announce) toast(`Another ${f.name} added. Now ${line.qty} × ${line.grams} g.`);
    } else {
      state.lines.push({ flavour: id, grams: cfg.popularSize, qty: 1, custom: false });
      if (announce) toast(`${f.name} added to your box.`);
    }
    commit();
  }
  function setPreset(id) {
    const preset = presets.find((p) => p.id === id);
    if (!preset) return;
    state.lines = preset.lines.map((l) => ({ ...l, custom: !cfg.sizes.includes(l.grams) }));
    toast(`${preset.title} loaded. Tweak anything you like.`);
    commit();
  }
  function commit() {
    state.error = "";
    syncRows();
    renderAside();
    save();
  }

  mount.addEventListener("click", (e) => {
    const preset = e.target.closest("[data-preset]");
    if (preset) return setPreset(preset.dataset.preset);
    const btn = e.target.closest("[data-act]");
    if (!btn) return;
    const li = btn.closest(".frow");
    if (!li) return;
    const id = li.dataset.id;
    const line = lineOf(id);
    switch (btn.dataset.act) {
      case "add":
        addFlavour(id);
        break;
      case "remove":
        state.lines = state.lines.filter((l) => l.flavour !== id);
        commit();
        break;
      case "inc":
        if (line) line.qty = clampQty(line.qty + 1);
        commit();
        break;
      case "dec":
        if (line) line.qty = clampQty(line.qty - 1);
        commit();
        break;
      case "size":
        if (!line) break;
        if (btn.dataset.g === "custom") line.custom = true;
        else {
          line.custom = false;
          line.grams = Number(btn.dataset.g);
        }
        commit();
        break;
      default:
    }
  });
  mount.addEventListener("input", (e) => {
    const input = e.target.closest('[data-act="range"], [data-act="gnum"]');
    if (!input) return;
    const li = input.closest(".frow");
    const line = lineOf(li.dataset.id);
    if (!line) return;
    let g = Number(input.value);
    if (!Number.isFinite(g)) return;
    if (input.dataset.act === "gnum" && (g < CMIN || g > CMAX)) return; // wait until it's valid
    g = Math.max(CMIN, Math.min(CMAX, Math.round(g / CSTEP) * CSTEP));
    line.grams = g;
    line.custom = true;
    // Don't rebuild while dragging; just refresh the numbers.
    const other = input.dataset.act === "range" ? $('[data-act="gnum"]', li) : $('[data-act="range"]', li);
    other.value = String(g);
    syncRows();
    renderAside();
    save();
  });
  mount.addEventListener("change", (e) => {
    const input = e.target.closest('[data-act="gnum"]');
    if (!input) return;
    const line = lineOf(input.closest(".frow").dataset.id);
    if (!line) return;
    const g = Math.max(CMIN, Math.min(CMAX, Math.round((Number(input.value) || CMIN) / CSTEP) * CSTEP));
    line.grams = g;
    line.custom = true;
    input.value = String(g);
    commit();
  });
  $$(".seg", mount).forEach((group) =>
    arrowNav(group, '[role="radio"]', (btn) => btn.click())
  );
  arrowNav($("[data-delivery]", mount), '[role="radio"]', (btn) => btn.click());
  window.addEventListener("box:add", (e) => addFlavour(e.detail.id));

  // ---------- Details form ----------
  const today = new Date();
  const todayStr = new Date(today.getTime() - today.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
  field("date").min = todayStr;

  const checks = {
    name: (v) => (v.trim().length < 2 ? "Please tell us your name." : ""),
    phone: (v) => (v.trim() && !/^\+?\d{7,15}$/.test(v.replace(/[\s().-]/g, "")) ? "That phone number doesn't look right." : ""),
    email: (v) => (v.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? "That email doesn't look right." : ""),
    address: (v) => (state.d.delivery === "delivery" && v.trim().length < 5 ? "Add your address or area for delivery." : ""),
    date: (v) => (v && v < todayStr ? "Pick a date from today onwards." : ""),
  };
  function showError(name, message) {
    const input = field(name);
    const err = $(`#e-${name}`, mount);
    if (err) err.textContent = message;
    if (input && "setAttribute" in input) {
      if (message) input.setAttribute("aria-invalid", "true");
      else input.removeAttribute("aria-invalid");
    }
  }
  function validateField(name) {
    const message = checks[name] ? checks[name](state.d[name] || "") : "";
    showError(name, message);
    return message;
  }

  function syncDetails() {
    for (const name of ["name", "phone", "email", "address", "date", "note"]) field(name).value = state.d[name] || "";
    $("[data-note-count]", mount).textContent = String(state.d.note.length);
    syncDelivery();
  }
  function syncDelivery() {
    for (const b of $$("[data-delivery] [role=radio]", mount)) {
      const on = b.dataset.v === state.d.delivery;
      b.setAttribute("aria-checked", String(on));
      b.tabIndex = on ? 0 : -1;
    }
    const open = state.d.delivery === "delivery";
    const wrap = $("[data-addr]", mount);
    wrap.classList.toggle("is-open", open);
    wrap.inert = !open;
    if (!open) showError("address", "");
  }

  el.form.addEventListener("input", (e) => {
    const name = e.target.name;
    if (!(name in state.d)) return;
    state.d[name] = e.target.value;
    if (name === "note") $("[data-note-count]", mount).textContent = String(e.target.value.length);
    if (e.target.getAttribute("aria-invalid") === "true") validateField(name);
    // Typing an email (or phone) clears the "add a way to reach you" message.
    if ((name === "email" || name === "phone") && (state.d.phone.trim() || state.d.email.trim()) && $("#e-phone", mount).textContent.startsWith("Add a phone")) {
      showError("phone", "");
      showError("email", checks.email(state.d.email));
    }
    save();
  });
  el.form.addEventListener("focusout", (e) => {
    const name = e.target.name;
    if (checks[name] && state.d[name] !== undefined && e.target.value !== "") validateField(name);
    else if (name === "name") validateField(name);
  });
  $("[data-delivery]", mount).addEventListener("click", (e) => {
    const b = e.target.closest("[role=radio]");
    if (!b) return;
    state.d.delivery = b.dataset.v;
    showError("delivery", "");
    syncDelivery();
    save();
  });
  el.form.addEventListener("submit", (e) => {
    e.preventDefault();
    let firstBad = null;
    for (const name of Object.keys(checks)) {
      if (validateField(name) && !firstBad) firstBad = field(name);
    }
    // Phone and email are both optional, but Ed needs at least one of them.
    if (!state.d.phone.trim() && !state.d.email.trim()) {
      showError("phone", "Add a phone number or an email so Ed can confirm your order.");
      if (!firstBad) firstBad = field("phone");
    }
    if (firstBad) {
      firstBad.focus();
      toast("Please fix the highlighted fields.");
      return;
    }
    goStep(3);
  });
  mount.addEventListener("click", (e) => {
    if (e.target.closest("[data-back]")) goStep(state.step === 3 ? 2 : 1);
    const edit = e.target.closest("[data-edit]");
    if (edit) goStep(Number(edit.dataset.edit));
  });

  // ---------- Review ----------
  function renderReview() {
    const t = totals();
    const d = state.d;
    const rows = state.lines
      .map((l) => {
        const f = byId.get(l.flavour);
        return `<div class="rrow">${pouchSVG(f, { mini: true })}<div><b>${esc(f.name)}</b><small>${l.qty} × ${l.grams} g</small></div><output>${num(price(l.grams) * l.qty)} ${esc(cur)}</output></div>`;
      })
      .join("");
    el.review.innerHTML = `
      <div class="review__head"><h4>Your box</h4><button class="link-btn" type="button" data-edit="1">Edit</button></div>
      <div class="rlist">${rows}<div class="rtotal"><span>${t.packs} ${t.packs === 1 ? "pack" : "packs"} · ${esc(fmtGrams(t.grams))}</span><output>${num(t.cost)} ${esc(cur)}</output></div></div>
      <div class="review__head"><h4>Your details</h4><button class="link-btn" type="button" data-edit="2">Edit</button></div>
      <dl class="kv">
        <div><dt>Name</dt><dd>${esc(d.name)}</dd></div>
        ${d.phone ? `<div><dt>Phone</dt><dd>${esc(d.phone)}</dd></div>` : ""}
        ${d.email ? `<div><dt>Email</dt><dd>${esc(d.email)}</dd></div>` : ""}
        <div><dt>${d.delivery === "delivery" ? "Delivery to" : "Pickup"}</dt><dd>${d.delivery === "delivery" ? esc(d.address) : "Collect from Ed"}</dd></div>
        ${d.date ? `<div><dt>Preferred date</dt><dd>${esc(d.date)}</dd></div>` : ""}
        ${d.note ? `<div style="grid-column:1/-1"><dt>Note</dt><dd>${esc(d.note)}</dd></div>` : ""}
      </dl>
      <p class="note">${esc(cfg.paymentNote)} Contains soy.</p>
      ${state.error ? `<div class="banner" role="alert" tabindex="-1" data-banner>${esc(state.error)}</div>` : ""}
      <div class="actions">
        <button class="btn btn--ghost" type="button" data-back><span>Back</span></button>
        <button class="btn btn--primary btn--lg" type="button" data-confirm><span>${state.error ? "Try again" : "Confirm preorder"}</span></button>
      </div>`;
  }

  function setSending(on) {
    state.sending = on;
    const btn = $("[data-confirm]", mount);
    if (btn) {
      btn.disabled = on;
      btn.setAttribute("aria-busy", String(on));
      btn.innerHTML = on ? `<span class="spin" aria-hidden="true"></span><span>Sending…</span>` : "<span>Try again</span>";
    }
    renderCTAs();
  }

  const FIELD_STEP = { name: 2, phone: 2, email: 2, address: 2, date: 2, delivery: 2, items: 1 };
  async function submit() {
    if (state.sending) return;
    state.error = "";
    const banner = $("[data-banner]", mount);
    if (banner) banner.remove();
    setSending(true);
    const body = {
      ...state.d,
      items: state.lines.map(({ flavour, grams, qty }) => ({ flavour, grams, qty })),
      website: field("website").value,
    };
    const ctrl = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), 20000);
    let failed = true;
    try {
      const res = await fetch("/api/preorders", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body), signal: ctrl.signal });
      const data = await res.json().catch(() => ({}));
      if (res.ok) {
        failed = false;
        finish(data);
      } else if (res.status === 400 && data.errors) {
        failed = false;
        const fields = Object.keys(data.errors);
        const target = Math.min(...fields.map((k) => FIELD_STEP[k] || 2));
        if (target === 1) {
          setSending(false);
          goStep(1);
          toast(data.errors.items || "Please check your box.");
        } else {
          for (const [name, message] of Object.entries(data.errors)) showError(name, message);
          setSending(false);
          goStep(2);
          const first = fields.map((k) => field(k)).find(Boolean);
          setTimeout(() => first?.focus(), 120);
        }
        return;
      } else {
        state.error = data.error || "Something went wrong. Please try again.";
      }
    } catch (err) {
      state.error = err.name === "AbortError" ? "That took too long. Check your connection and try again." : "Could not reach the server. Check your connection and try again.";
    } finally {
      clearTimeout(timer);
      state.sending = false;
    }
    if (failed) {
      renderReview();
      renderCTAs();
      $("[data-banner]", mount)?.focus();
    }
  }

  // ---------- Success ----------
  function finish(data) {
    const t = totals();
    const snapshot = {
      code: data.code,
      name: state.d.name.trim(),
      phone: state.d.phone,
      email: state.d.email,
      delivery: state.d.delivery,
      lines: state.lines.map((l) => ({ ...l })),
      totals: t,
    };
    state.done = snapshot;
    state.lines = [];
    state.d.note = "";
    store.remove(STORE_KEY);
    state.sending = false;
    syncRows();
    renderAside();
    renderDone();
    goStep("done");
    setTimeout(() => !reduce && burst($(".burst", mount)), 350);
  }

  function renderDone() {
    const s = state.done;
    const first = esc(s.name.split(" ")[0] || "friend");
    const rows = s.lines
      .map((l) => {
        const f = byId.get(l.flavour);
        return `<div class="rrow">${pouchSVG(f, { mini: true })}<div><b>${esc(f.name)}</b><small>${l.qty} × ${l.grams} g</small></div><output>${num(price(l.grams) * l.qty)} ${esc(cur)}</output></div>`;
      })
      .join("");
    el.done.innerHTML = `
      <svg class="done__check" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="46"/><path d="M30 52l14 14 27-30"/></svg>
      <h3 id="pd-title" tabindex="-1">You're in, ${first}!</h3>
      <p class="lead" style="margin:14px auto 0">Your preorder is with Ed. Keep this code handy.</p>
      <p class="done__code-lbl">Your order code</p>
      <p class="done__code" aria-label="Order code ${esc(s.code.split("").join(" "))}">${esc(s.code)}</p>
      <button class="btn btn--ghost btn--sm" type="button" data-copy style="margin-top:12px"><span>Copy code</span></button>
      <div class="rlist done__summary">${rows}<div class="rtotal"><span>${s.totals.packs} ${s.totals.packs === 1 ? "pack" : "packs"} · ${esc(fmtGrams(s.totals.grams))}</span><output>${num(s.totals.cost)} ${esc(cur)}</output></div></div>
      <ol class="done__next">
        <li><b>1</b><span>Ed gets your preorder right away.</span></li>
        <li><b>2</b><span>He gets in touch on <strong>${esc(s.phone || s.email)}</strong> to confirm the details.</span></li>
        <li><b>3</b><span>${s.delivery === "delivery" ? "Your jerky is delivered" : "You collect your jerky"}, and you pay then.</span></li>
      </ol>
      <div class="done__actions">
        <button class="btn btn--primary" type="button" data-again><span>Build another box</span></button>
      </div>`;
  }
  mount.addEventListener("click", async (e) => {
    if (e.target.closest("[data-confirm]")) submit();
    if (e.target.closest("[data-again]")) {
      state.done = null;
      goStep(1);
    }
    const copy = e.target.closest("[data-copy]");
    if (copy && state.done) {
      try {
        await navigator.clipboard.writeText(state.done.code);
        toast("Code copied.");
      } catch {
        toast(`Your code is ${state.done.code}`);
      }
    }
  });

  // ---------- Next button (aside, mobile bar, panel) ----------
  function onNext() {
    if (state.step === 1) {
      if (totals().packs === 0) return toast("Add a flavour to your box first.");
      if (tooBig()) return toast("That's a big box! For large orders please contact Ed directly.");
      goStep(2);
    } else if (state.step === 2) {
      el.form.requestSubmit();
    } else if (state.step === 3) {
      submit();
    }
  }
  for (const btn of el.nextBtns) btn.addEventListener("click", onNext);

  // ---------- Mobile bar visibility ----------
  let inView = false;
  function updateBar() {
    el.bar.classList.toggle("is-on", inView && state.step !== "done");
  }
  new IntersectionObserver(
    ([entry]) => {
      inView = entry.isIntersecting;
      updateBar();
    },
    { rootMargin: "-12% 0px -12% 0px" }
  ).observe($(".builder__main", mount));

  // ---------- Go ----------
  load();
  syncRows();
  syncDetails();
  renderSteps();
  renderAside();
  if (state.lines.length) toast("Welcome back. Your box is saved.");
}
