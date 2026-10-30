// Pouch illustrations and the heat meter, drawn as SVG so every flavour looks
// crisp at any size and takes its colours straight from config.json.
import { esc } from "./ui.js";

export function fmtGrams(g) {
  return g >= 1000 ? `${+(g / 1000).toFixed(2)} kg` : `${g} g`;
}

// Gives every flavour its meat and full name ("Texas BBQ Beef"), marks the
// boxes, and adds one pouch colour gradient per meat to the shared SVG defs.
export function prepareMenu(cfg) {
  for (const b of cfg.boxes || []) {
    b.box = true;
    b.fullName = b.name;
    b.accent = b.accent || "#ff6a2b";
    b.stripes = cfg.flavours.map((f) => f.band);
  }
  const meats = new Map((cfg.meats || []).map((m) => [m.id, m]));
  for (const f of cfg.flavours) {
    const meat = meats.get(f.meat);
    f.meatName = meat ? meat.name : "";
    f.fullName = [f.name, f.meatName].filter(Boolean).join(" ");
    f.pouchFill = meat?.pouch ? `url(#pouchBody-${meat.id})` : "url(#pouchBody)";
    f.pouchCap = meat?.pouch?.cap || "#0a0908";
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

// `mini` drops the small print, for sizes where text would be unreadable.
export function pouchSVG(flavour, { grams = 100, mini = false, label = "" } = {}) {
  if (flavour.box) return boxSVG(flavour, { mini, label });
  const body = "M16 30Q16 16 30 16H170Q184 16 184 30V264Q184 280 168 280H32Q16 280 16 264Z";
  const aria = label ? `role="img" aria-label="${esc(label)}"` : 'aria-hidden="true"';
  const name = esc(flavour.name.toUpperCase());
  const title = `PREMIUM ${(flavour.meatName || "Beef").toUpperCase()} JERKY`;
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
    <image href="/img/logo.webp" x="36" y="60" width="128" height="128" preserveAspectRatio="xMidYMid meet"/>
    <rect x="16" y="214" width="168" height="${mini ? 40 : 38}" fill="${flavour.band}"/>
    <path d="M16 214H184" stroke="rgba(255,255,255,.28)" stroke-width="1"/>
    ${text}
    <path d="${body}" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

// A gift-style carton with one colour stripe per flavour inside it.
function boxSVG(box, { mini = false, label = "" } = {}) {
  const aria = label ? `role="img" aria-label="${esc(label)}"` : 'aria-hidden="true"';
  const stripes = box.stripes || [];
  const w = 152 / Math.max(1, stripes.length);
  const text = mini
    ? ""
    : `<text x="92" y="200" text-anchor="middle" class="pouch__flavour" fill="#efe8e1" font-size="24" letter-spacing="1.5">${esc(box.name.toUpperCase())}</text>
       <text x="92" y="266" text-anchor="middle" fill="rgba(255,255,255,.6)" font-size="9.5" font-weight="700" letter-spacing="1.4">ALL ${stripes.length} FLAVOURS</text>`;
  return `<svg class="pouch pouch--box" viewBox="0 0 200 296" ${aria} focusable="false">
    <path d="M16 60L42 30H190L168 60Z" fill="#3a3330" stroke="rgba(255,255,255,.12)" stroke-width="1.2"/>
    <path d="M168 60L190 30V250L168 280Z" fill="#141110" stroke="rgba(255,255,255,.08)" stroke-width="1.2"/>
    <rect x="16" y="60" width="152" height="220" rx="4" fill="url(#pouchBody)" stroke="rgba(255,255,255,.12)" stroke-width="1.5"/>
    <image href="/img/logo.webp" x="${mini ? 24 : 38}" y="${mini ? 70 : 72}" width="${mini ? 136 : 108}" height="${mini ? 136 : 108}" preserveAspectRatio="xMidYMid meet"/>
    ${stripes.map((c, i) => `<rect x="${16 + i * w}" y="${mini ? 214 : 212}" width="${w + 0.5}" height="${mini ? 40 : 30}" fill="${esc(c)}"/>`).join("")}
    ${text}
    <rect x="16" y="60" width="152" height="220" rx="4" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

// Five bars, `level` of them lit. The aria-label gives the same info in words.
export function heatBars(level, { small = false, label = false } = {}) {
  const bars = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}" style="--i:${i}"></i>`).join("");
  const text = label ? `<span class="heat__label" aria-hidden="true">Heat ${level}/5</span>` : "";
  return `<span class="heat${small ? " heat--sm" : ""}" role="img" aria-label="Heat ${level} out of 5">${bars}${text}</span>`;
}
