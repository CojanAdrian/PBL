// The interactive flavour stage in the "Pick your fire" section.
// Pick a flavour with the buttons, the arrows and dots, a swipe on the
// preview (phones), or the arrow keys.
import { $, $$, esc, reduce, arrowNav, store } from "./ui.js";
import { pouchSVG, heatBars } from "./pouch.js";

const SWIPED_KEY = "bigeds.swiped";
const chevron = (dir) =>
  `<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${dir < 0 ? "M15 6l-6 6 6 6" : "M9 6l6 6-6 6"}"/></svg>`;

export function initFlavours(cfg) {
  const stage = $("#fstage");
  if (!stage) return;
  const tabsEl = $(".fstage__tabs", stage);
  const panel = $(".fstage__panel", stage);
  const pouchEl = $(".fstage__pouch", stage);
  const infoEl = $(".fstage__info", stage);
  const tagEl = $(".fstage__tag", stage);
  const nameEl = $(".fstage__name", stage);
  const notesEl = $(".fstage__notes", stage);
  const heatEl = $(".fstage__heat", stage);
  const addBtn = $("#fstage-add");
  const ids = cfg.flavours.map((f) => f.id);
  let current = null;
  let seq = 0;

  panel.id = "fpanel";
  tabsEl.innerHTML = cfg.flavours
    .map(
      (f) => `<button class="ftab" role="tab" type="button" id="ftab-${esc(f.id)}" aria-controls="fpanel" aria-selected="false" tabindex="-1" data-id="${esc(f.id)}" style="--tab:${esc(f.accent)}">
        <span class="ftab__dot" aria-hidden="true"></span>
        <span class="ftab__name">${esc(f.name)}</span>
        <span class="ftab__heat">${heatBars(f.heat, { small: true })}</span>
      </button>`
    )
    .join("");
  const tabs = $$(".ftab", tabsEl);

  // Arrows, dots and a swipe hint, shown on phones under the pack.
  const nav = document.createElement("div");
  nav.className = "fstage__nav";
  nav.innerHTML = `
    <p class="fstage__hint" aria-hidden="true"><span>${chevron(-1)}</span>Swipe the pack to explore<span>${chevron(1)}</span></p>
    <button class="fstage__arrow" type="button" data-step="-1" aria-label="Previous flavour">${chevron(-1)}</button>
    <div class="fstage__dots">
      ${cfg.flavours.map((f) => `<button class="fstage__dot" type="button" data-id="${esc(f.id)}" aria-label="Show ${esc(f.name)}" style="--dot:${esc(f.accent)}"><i></i></button>`).join("")}
    </div>
    <button class="fstage__arrow" type="button" data-step="1" aria-label="Next flavour">${chevron(1)}</button>`;
  pouchEl.after(nav);
  const dots = $$(".fstage__dot", nav);
  if (store.get(SWIPED_KEY)) nav.classList.add("is-used");
  const markUsed = () => {
    nav.classList.add("is-used");
    store.set(SWIPED_KEY, "1");
  };

  function apply(flavour) {
    pouchEl.innerHTML = pouchSVG(flavour, { grams: 100, label: `${flavour.name} jerky pouch` });
    tagEl.textContent = flavour.tagline;
    nameEl.textContent = flavour.name;
    notesEl.textContent = flavour.notes;
    heatEl.innerHTML = heatBars(flavour.heat, { label: true });
    addBtn.querySelector("span").textContent = `Add ${flavour.name} to my box`;
  }

  const clearInline = () => {
    for (const el of [pouchEl, infoEl]) {
      el.style.transform = "";
      el.style.opacity = "";
    }
  };

  // The old pack slides out the way you swiped, the new one slides in from the other side.
  async function swap(flavour, dir, mySeq) {
    const targets = [
      { el: pouchEl, dx: 90, rot: 9, delay: 0 },
      { el: infoEl, dx: 40, rot: 0, delay: 40 },
    ];
    const starts = targets.map(({ el }) => ({ transform: getComputedStyle(el).transform, opacity: getComputedStyle(el).opacity }));
    clearInline();
    const outs = targets.map(({ el, dx, rot }, i) =>
      el.animate(
        [
          { transform: starts[i].transform === "none" ? "translateX(0)" : starts[i].transform, opacity: starts[i].opacity },
          { transform: `translateX(${-dir * dx}px) rotate(${-dir * rot}deg)`, opacity: 0 },
        ],
        { duration: 170, easing: "ease-in", fill: "forwards" }
      )
    );
    try {
      await Promise.all(outs.map((a) => a.finished));
    } catch {
      return; // a newer swap cancelled this one
    }
    if (mySeq !== seq) return;
    apply(flavour);
    const ins = targets.map(({ el, dx, rot, delay }) => {
      const anim = el.animate(
        [
          { transform: `translateX(${dir * dx}px) rotate(${dir * rot}deg)`, opacity: 0 },
          { transform: "translateX(0) rotate(0deg)", opacity: 1 },
        ],
        { duration: 480, delay, easing: "cubic-bezier(0.22, 1, 0.36, 1)", fill: "both" }
      );
      return anim;
    });
    outs.forEach((a) => a.cancel());
    try {
      await Promise.all(ins.map((a) => a.finished));
    } catch {
      return;
    }
    ins.forEach((a) => a.cancel());
  }

  function show(id, { animate = true, dir = 0, user = true } = {}) {
    const flavour = cfg.flavours.find((f) => f.id === id);
    if (!flavour || id === current) {
      clearInline();
      return;
    }
    const from = ids.indexOf(current);
    const to = ids.indexOf(id);
    const direction = dir || (to >= from ? 1 : -1);
    current = id;
    if (user && from >= 0) markUsed();
    for (const tab of tabs) {
      const on = tab.dataset.id === id;
      tab.setAttribute("aria-selected", String(on));
      tab.tabIndex = on ? 0 : -1;
    }
    for (const dot of dots) dot.classList.toggle("is-on", dot.dataset.id === id);
    panel.setAttribute("aria-labelledby", `ftab-${id}`);
    stage.style.setProperty("--accent", flavour.accent);
    seq += 1;
    for (const el of [pouchEl, infoEl]) el.getAnimations().forEach((a) => a.cancel());
    if (!animate || reduce) {
      clearInline();
      apply(flavour);
      return;
    }
    swap(flavour, direction, seq);
  }

  const go = (step) => {
    const next = (ids.indexOf(current) + step + ids.length) % ids.length;
    show(ids[next], { dir: step > 0 ? 1 : -1 });
  };

  // ---- Buttons, arrows, dots, keyboard ----
  tabsEl.addEventListener("click", (e) => {
    const tab = e.target.closest(".ftab");
    if (tab) show(tab.dataset.id);
  });
  arrowNav(tabsEl, ".ftab", (tab) => show(tab.dataset.id));
  nav.addEventListener("click", (e) => {
    const arrow = e.target.closest("[data-step]");
    if (arrow) return go(Number(arrow.dataset.step));
    const dot = e.target.closest(".fstage__dot");
    if (dot) show(dot.dataset.id);
  });
  addBtn.addEventListener("click", () => window.dispatchEvent(new CustomEvent("box:add", { detail: { id: current } })));

  // ---- Swipe on the preview (touch and pen; mouse users have the buttons) ----
  let drag = null;
  const release = (e) => {
    if (!drag || e.pointerId !== drag.id) return;
    const { dx, locked, t0 } = drag;
    drag = null;
    panel.classList.remove("is-dragging");
    if (locked !== "x") return;
    const speed = Math.abs(dx) / Math.max(1, performance.now() - t0);
    if (e.type !== "pointercancel" && (Math.abs(dx) > 70 || (speed > 0.45 && Math.abs(dx) > 28))) {
      // Continue from where the finger let go.
      pouchEl.style.transform = `translateX(${dx * 0.7}px) rotate(${dx * 0.05}deg)`;
      go(dx < 0 ? 1 : -1);
    } else {
      // Not far enough: spring back.
      const back = (el, k) => el.animate([{ transform: el.style.transform || "none", opacity: el.style.opacity || 1 }, { transform: "none", opacity: 1 }], { duration: 380, easing: "cubic-bezier(0.34, 1.56, 0.64, 1)" });
      back(pouchEl);
      back(infoEl);
      clearInline();
    }
  };
  panel.addEventListener("pointerdown", (e) => {
    if (e.pointerType === "mouse" || e.target.closest("button, a")) return;
    drag = { id: e.pointerId, x: e.clientX, y: e.clientY, t0: performance.now(), dx: 0, locked: null };
  });
  panel.addEventListener("pointermove", (e) => {
    if (!drag || e.pointerId !== drag.id) return;
    const dx = e.clientX - drag.x;
    const dy = e.clientY - drag.y;
    if (drag.locked === null) {
      if (Math.abs(dx) < 8 && Math.abs(dy) < 8) return;
      drag.locked = Math.abs(dx) > Math.abs(dy) ? "x" : "y";
      if (drag.locked === "x") {
        panel.setPointerCapture(e.pointerId);
        panel.classList.add("is-dragging");
        for (const el of [pouchEl, infoEl]) el.getAnimations().forEach((a) => a.cancel());
      }
    }
    if (drag.locked !== "x") return;
    drag.dx = dx;
    const fade = 1 - Math.min(0.65, Math.abs(dx) / 380);
    pouchEl.style.transform = `translateX(${dx * 0.7}px) rotate(${dx * 0.05}deg)`;
    pouchEl.style.opacity = String(fade);
    infoEl.style.transform = `translateX(${dx * 0.35}px)`;
    infoEl.style.opacity = String(fade);
  });
  panel.addEventListener("pointerup", release);
  panel.addEventListener("pointercancel", release);

  show(cfg.flavours[0].id, { animate: false, user: false });
}
