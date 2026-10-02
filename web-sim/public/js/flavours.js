// The interactive flavour stage in the "Pick your fire" section.
import { $, $$, esc, reduce, arrowNav } from "./ui.js";
import { pouchSVG, heatBars } from "./pouch.js";

export function initFlavours(cfg) {
  const stage = $("#fstage");
  if (!stage) return;
  const tabsEl = $(".fstage__tabs", stage);
  const panel = $(".fstage__panel", stage);
  const pouchEl = $(".fstage__pouch", stage);
  const tagEl = $(".fstage__tag", stage);
  const nameEl = $(".fstage__name", stage);
  const notesEl = $(".fstage__notes", stage);
  const heatEl = $(".fstage__heat", stage);
  const addBtn = $("#fstage-add");
  let current = null;
  let timer;

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

  function show(id, animate = true) {
    const flavour = cfg.flavours.find((f) => f.id === id);
    if (!flavour || id === current) return;
    current = id;
    for (const tab of tabs) {
      const on = tab.dataset.id === id;
      tab.setAttribute("aria-selected", String(on));
      tab.tabIndex = on ? 0 : -1;
    }
    panel.setAttribute("aria-labelledby", `ftab-${id}`);
    stage.style.setProperty("--accent", flavour.accent);
    const apply = () => {
      pouchEl.innerHTML = pouchSVG(flavour, { grams: 100, label: `${flavour.name} jerky pouch` });
      tagEl.textContent = flavour.tagline;
      nameEl.textContent = flavour.name;
      notesEl.textContent = flavour.notes;
      heatEl.innerHTML = heatBars(flavour.heat, { label: true });
      addBtn.querySelector("span").textContent = `Add ${flavour.name} to my box`;
    };
    clearTimeout(timer);
    if (!animate || reduce) {
      apply();
      return;
    }
    panel.classList.add("is-swapping");
    timer = setTimeout(() => {
      apply();
      requestAnimationFrame(() => panel.classList.remove("is-swapping"));
    }, 260);
  }

  tabsEl.addEventListener("click", (e) => {
    const tab = e.target.closest(".ftab");
    if (tab) show(tab.dataset.id);
  });
  arrowNav(tabsEl, ".ftab", (tab) => show(tab.dataset.id));
  addBtn.addEventListener("click", () => window.dispatchEvent(new CustomEvent("box:add", { detail: { id: current } })));

  show(cfg.flavours[0].id, false);
}
