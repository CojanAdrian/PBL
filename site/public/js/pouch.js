// Pack illustrations (pouch, box, sack) and the heat meter, drawn as SVG so every
// product looks crisp at any size and takes its colours from the admin.
import { esc } from "./ui.js";
import { t, setTexts } from "./i18n.js";

// The logo printed on every drawing (Admin -> Images -> site_logo).
let logo = "/img/logo.webp";

export function fmtGrams(g) {
  return g >= 1000 ? `${+(g / 1000).toFixed(2)} kg` : `${g} g`;
}

// Loads the texts, gives every product its meat and full name ("Texas BBQ Beef"),
// marks the mixes (boxes), and adds one colour gradient per meat to the shared SVG defs.
export function prepareMenu(cfg) {
  setTexts(cfg.t, cfg.locale, cfg.editing);
  if (cfg.logo) logo = cfg.logo;
  // One stripe per flavour of each meat; the same flavour in another pack type (a sack) counts once.
  const seen = new Set();
  const stripes = cfg.flavours.filter((f) => !seen.has(`${f.meat}:${f.name}`) && seen.add(`${f.meat}:${f.name}`)).map((f) => f.band);
  for (const b of cfg.boxes || []) {
    b.box = true;
    b.fullName = b.name;
    b.accent = b.accent || "#ff6a2b";
    b.stripes = stripes;
  }
  const meats = new Map((cfg.meats || []).map((m) => [m.id, m]));
  for (const f of cfg.flavours) {
    const meat = meats.get(f.meat);
    f.meatName = meat ? meat.name : "";
    f.fullName = f.meatName ? t("full_name", { name: f.name, meat: f.meatName }) : f.name;
    f.pouchFill = meat?.pouch ? `url(#pouchBody-${meat.id})` : "url(#pouchBody)";
    f.pouchCap = meat?.pouch?.cap || "#0a0908";
    f.stripes = [f.band];
  }
  const defs = document.querySelector(".svg-defs defs");
  if (!defs) return;
  defs.insertAdjacentHTML(
    "beforeend",
    [...meats.values()]
      .filter((m) => m.pouch)
      .map(
        (m) => `<linearGradient id="pouchBody-${esc(m.id)}" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stop-color="${esc(m.pouch.top)}"/><stop offset="1" stop-color="${esc(m.pouch.bottom)}"/>
        </linearGradient>`
      )
      .join("")
  );
}

// Draws a product the way its pack type says: pouch, box or sack.
// `mini` drops the small print, for sizes where text would be unreadable.
export function pouchSVG(product, { grams = product.grams || 100, mini = false, label = "" } = {}) {
  const style = product.style || (product.box ? "box" : "pouch");
  if (style === "box") return boxSVG(product, { mini, label });
  if (style === "sack") return sackSVG(product, { grams, mini, label });
  const flavour = product;
  const body = "M16 30Q16 16 30 16H170Q184 16 184 30V264Q184 280 168 280H32Q16 280 16 264Z";
  const aria = label ? `role="img" aria-label="${esc(label)}"` : 'aria-hidden="true"';
  const name = esc(flavour.name.toUpperCase());
  // Printed packaging, so it stays in English whatever the page language.
  const title = `PREMIUM ${(flavour.meat || "beef").toUpperCase()} JERKY`;
  // Long flavour names are squeezed to fit the band.
  const fit = name.length > 12 ? ' textLength="150" lengthAdjust="spacingAndGlyphs"' : "";
  const text = mini
    ? ""
    : `<text x="100" y="204" text-anchor="middle" class="pouch__title" fill="#efe8e1" font-size="${title.length > 18 ? 10 : 11.5}" font-weight="800" letter-spacing="${title.length > 18 ? 1.4 : 1.8}">${esc(title)}</text>
       <text x="100" y="241" text-anchor="middle" class="pouch__flavour" fill="${flavour.bandText}" font-size="${name.length > 12 ? 19 : name.length > 9 ? 21 : 25}" letter-spacing="1.5"${fit}>${name}</text>
       <text x="100" y="270" text-anchor="middle" fill="rgba(255,255,255,.55)" font-size="9.5" font-weight="700" letter-spacing="1.4">NET WT. ${esc(fmtGrams(grams).toUpperCase())}</text>`;
  return `<svg class="pouch" viewBox="0 0 200 296" ${aria} focusable="false">
    <path d="${body}" fill="${flavour.pouchFill || "url(#pouchBody)"}" stroke="rgba(255,255,255,.1)" stroke-width="1.5"/>
    <path d="M16 30Q16 16 30 16H170Q184 16 184 30V48H16Z" fill="${flavour.pouchCap || "#0a0908"}"/>
    <path d="M24 30H176M24 38H176" stroke="rgba(255,255,255,.1)" stroke-width="1.5" stroke-linecap="round"/>
    <image href="${esc(logo)}" x="36" y="60" width="128" height="128" preserveAspectRatio="xMidYMid meet"/>
    <rect x="16" y="214" width="168" height="${mini ? 40 : 38}" fill="${flavour.band}"/>
    <path d="M16 214H184" stroke="rgba(255,255,255,.28)" stroke-width="1"/>
    ${text}
    <path d="${body}" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

// A gift-style carton with one colour stripe per flavour inside it.
function boxSVG(box, { mini = false, label = "" } = {}) {
  const aria = label ? `role="img" aria-label="${esc(label)}"` : 'aria-hidden="true"';
  const stripes = box.stripes || [box.band];
  const w = 152 / Math.max(1, stripes.length);
  const name = box.name.toUpperCase();
  // Long names ("CUTIA DE DEGUSTARE") are squeezed to fit the front of the box.
  const fit = name.length > 10 ? ' textLength="136" lengthAdjust="spacingAndGlyphs"' : "";
  const small = box.box ? `ALL ${stripes.length} FLAVOURS` : `NET WT. ${fmtGrams(box.grams || 0).toUpperCase()}`;
  const text = mini
    ? ""
    : `<text x="92" y="200" text-anchor="middle" class="pouch__flavour" fill="#efe8e1" font-size="${name.length > 10 ? 20 : 24}" letter-spacing="1.5"${fit}>${esc(name)}</text>
       <text x="92" y="266" text-anchor="middle" fill="rgba(255,255,255,.6)" font-size="9.5" font-weight="700" letter-spacing="1.4">${esc(small)}</text>`;
  return `<svg class="pouch pouch--box" viewBox="0 0 200 296" ${aria} focusable="false">
    <path d="M16 60L42 30H190L168 60Z" fill="#3a3330" stroke="rgba(255,255,255,.12)" stroke-width="1.2"/>
    <path d="M168 60L190 30V250L168 280Z" fill="#141110" stroke="rgba(255,255,255,.08)" stroke-width="1.2"/>
    <rect x="16" y="60" width="152" height="220" rx="4" fill="${box.pouchFill || "url(#pouchBody)"}" stroke="rgba(255,255,255,.12)" stroke-width="1.5"/>
    <image href="${esc(logo)}" x="${mini ? 24 : 38}" y="${mini ? 70 : 72}" width="${mini ? 136 : 108}" height="${mini ? 136 : 108}" preserveAspectRatio="xMidYMid meet"/>
    ${stripes.map((c, i) => `<rect x="${16 + i * w}" y="${mini ? 214 : 212}" width="${w + 0.5}" height="${mini ? 40 : 30}" fill="${esc(c)}"/>`).join("")}
    ${text}
    <rect x="16" y="60" width="152" height="220" rx="4" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

// A rounded sack tied at the neck, with a label patch on the front.
function sackSVG(sack, { grams, mini = false, label = "" } = {}) {
  const aria = label ? `role="img" aria-label="${esc(label)}"` : 'aria-hidden="true"';
  const body = "M58 70C40 74 26 100 24 150C22 210 26 262 52 276C80 290 120 290 148 276C174 262 178 210 176 150C174 100 160 74 142 70Z";
  const band = sack.band || "#b8261c";
  const name = (sack.name || "").toUpperCase();
  const fit = name.length > 11 ? ' textLength="112" lengthAdjust="spacingAndGlyphs"' : "";
  const text = mini
    ? ""
    : `<text x="100" y="226" text-anchor="middle" class="pouch__flavour" fill="${esc(sack.bandText || "#fff")}" font-size="${name.length > 11 ? 16 : 19}" letter-spacing="1.2"${fit}>${esc(name)}</text>
       <text x="100" y="252" text-anchor="middle" fill="rgba(255,255,255,.65)" font-size="9" font-weight="700" letter-spacing="1.4">NET WT. ${esc(fmtGrams(grams).toUpperCase())}</text>`;
  return `<svg class="pouch pouch--sack" viewBox="0 0 200 296" ${aria} focusable="false">
    <path d="M66 46C72 28 128 28 134 46L142 70H58Z" fill="${sack.pouchCap || "#0a0908"}" stroke="rgba(255,255,255,.12)" stroke-width="1.2"/>
    <path d="${body}" fill="${sack.pouchFill || "url(#pouchBody)"}" stroke="rgba(255,255,255,.12)" stroke-width="1.5"/>
    <path d="M54 72C80 80 120 80 146 72" stroke="${esc(band)}" stroke-width="7" fill="none" stroke-linecap="round"/>
    <path d="M70 48L60 30M130 48L140 30" stroke="${esc(band)}" stroke-width="4" stroke-linecap="round"/>
    <rect x="40" y="96" width="120" height="168" rx="14" fill="rgba(10,9,8,.55)" stroke="rgba(255,255,255,.14)"/>
    <image href="${esc(logo)}" x="${mini ? 46 : 56}" y="104" width="${mini ? 108 : 88}" height="${mini ? 108 : 88}" preserveAspectRatio="xMidYMid meet"/>
    <rect x="40" y="${mini ? 214 : 206}" width="120" height="${mini ? 40 : 30}" fill="${esc(band)}"/>
    ${text}
    <path d="${body}" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

// Five bars, `level` of them lit. The aria-label gives the same info in words.
export function heatBars(level, { small = false, label = false } = {}) {
  const bars = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}" style="--i:${i}"></i>`).join("");
  const text = label ? `<span class="heat__label" aria-hidden="true">${esc(t("heat", { n: level }))}</span>` : "";
  return `<span class="heat${small ? " heat--sm" : ""}" role="img" aria-label="${esc(t("heat_aria", { n: level }))}">${bars}${text}</span>`;
}
