import { $, reduce, scrollToEl } from "./ui.js";
import { createEmbers } from "./embers.js";
import { initSmoothScroll, runPreloader, heroIntro, initNav, initScrollFX, initMarquee, pointerParallax, initMagnetic, initSpotlight, initCursor } from "./fx.js";
import { initFlavours } from "./flavours.js";
import { initBuilder } from "./builder.js";
import { prepareMenu } from "./pouch.js";
import { t } from "./i18n.js";

// Motion and interaction, none of which needs the menu data.
initSmoothScroll();
initNav();
initCursor();
initSpotlight();
if (!reduce) {
  const canvas = $(".embers");
  if (canvas) createEmbers(canvas, { count: window.innerWidth < 700 ? 34 : 72 });
  pointerParallax($(".hero"), $(".hero__visual"));
}
initScrollFX();
initMarquee();
initMagnetic();
runPreloader().then(heroIntro);

// The menu (products, prices, texts) is in the page, rendered by the server from the admin.
const cfg = JSON.parse($("#menu-data").textContent);
prepareMenu(cfg);
initFlavours(cfg);
pointerParallax($(".fstage__panel"));
wireNavCount();
initBuilder(cfg, $("#builder"));
// Layout changed (builder + stage are taller now), so pinned sections need to re-measure.
window.ScrollTrigger?.refresh();

// Show how many packs are in the box on the nav button.
function wireNavCount() {
  const cta = $(".nav__cta span");
  // In the admin's Visual editor the button's text is editable, so leave it alone.
  if (!cta || cta.querySelector("[data-cms-text]")) return;
  const label = cta.textContent;
  window.addEventListener("box:change", (e) => {
    const n = e.detail.packs;
    cta.textContent = n > 0 ? t("nav_count", { n }) : label;
  });
}

// Deep link, e.g. /en#preorder, after layout settles.
if (location.hash.length > 1) window.addEventListener("load", () => setTimeout(() => scrollToEl(location.hash, -40), 400), { once: true });
