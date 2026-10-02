import { $, esc, reduce, scrollToEl } from "./ui.js";
import { createEmbers } from "./embers.js";
import { initSmoothScroll, runPreloader, heroIntro, initNav, initScrollFX, initMarquee, pointerParallax, initMagnetic, initSpotlight, initCursor } from "./fx.js";
import { initFlavours } from "./flavours.js";
import { initBuilder } from "./builder.js";

$("#year").textContent = String(new Date().getFullYear());

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

// Menu data (flavours, sizes, prices) comes from the server's config.json.
async function loadMenu() {
  const loading = $("#builder-loading");
  try {
    const res = await fetch("/api/config", { headers: { Accept: "application/json" } });
    if (!res.ok) throw new Error(`Config responded ${res.status}`);
    const cfg = await res.json();
    initFlavours(cfg);
    pointerParallax($(".fstage__panel"));
    wireNavCount();
    initBuilder(cfg, $("#builder"));
    loading?.remove();
    renderContact(cfg.contact || {});
    // Layout changed (builder + stage are taller now), so pinned sections need to re-measure.
    window.ScrollTrigger?.refresh();
  } catch (err) {
    console.error(err);
    if (loading) {
      loading.innerHTML = `We couldn't load the menu. <button class="link-btn" type="button" id="retry-menu">Try again</button>`;
      $("#retry-menu").addEventListener("click", () => {
        loading.textContent = "Loading the menu…";
        loadMenu();
      });
    }
  }
}

function renderContact(contact) {
  const list = $("#footer-contact");
  const items = [];
  if (contact.phone) items.push(`<li><a href="tel:${esc(contact.phone.replace(/[^\d+]/g, ""))}">${esc(contact.phone)}</a></li>`);
  if (contact.email) items.push(`<li><a href="mailto:${esc(contact.email)}">${esc(contact.email)}</a></li>`);
  if (contact.instagram) items.push(`<li><a href="https://instagram.com/${esc(contact.instagram.replace(/^@/, ""))}" rel="noopener">Instagram @${esc(contact.instagram.replace(/^@/, ""))}</a></li>`);
  if (contact.telegram) items.push(`<li><a href="https://t.me/${esc(contact.telegram.replace(/^@/, ""))}" rel="noopener">Telegram @${esc(contact.telegram.replace(/^@/, ""))}</a></li>`);
  list.innerHTML = items.length ? items.join("") : `<li><span>Questions? Send a preorder and Ed will get back to you.</span></li>`;
}

// Show how many packs are in the box on the nav button.
function wireNavCount() {
  const cta = $(".nav__cta span");
  window.addEventListener("box:change", (e) => {
    const n = e.detail.packs;
    cta.textContent = n > 0 ? `Preorder · ${n}` : "Preorder";
  });
}

loadMenu();

// Deep link, e.g. /#preorder, after layout settles.
if (location.hash.length > 1) window.addEventListener("load", () => setTimeout(() => scrollToEl(location.hash, -40), 400), { once: true });
