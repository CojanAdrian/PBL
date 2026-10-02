// Pouch illustrations and the heat meter, drawn as SVG so every flavour looks
// crisp at any size and takes its colours straight from config.json.
import { esc } from "./ui.js";

export function fmtGrams(g) {
  return g >= 1000 ? `${+(g / 1000).toFixed(2)} kg` : `${g} g`;
}

// `mini` drops the small print, for sizes where text would be unreadable.
export function pouchSVG(flavour, { grams = 100, mini = false, label = "" } = {}) {
  const body = "M16 30Q16 16 30 16H170Q184 16 184 30V264Q184 280 168 280H32Q16 280 16 264Z";
  const aria = label ? `role="img" aria-label="${esc(label)}"` : 'aria-hidden="true"';
  const name = esc(flavour.name.toUpperCase());
  const text = mini
    ? ""
    : `<text x="100" y="204" text-anchor="middle" class="pouch__title" fill="#efe8e1" font-size="11.5" font-weight="800" letter-spacing="1.8">PREMIUM BEEF JERKY</text>
       <text x="100" y="241" text-anchor="middle" class="pouch__flavour" fill="${flavour.bandText}" font-size="${name.length > 9 ? 21 : 25}" letter-spacing="1.5">${name}</text>
       <text x="100" y="270" text-anchor="middle" fill="rgba(255,255,255,.55)" font-size="9.5" font-weight="700" letter-spacing="1.4">NET WT. ${esc(fmtGrams(grams).toUpperCase())}</text>`;
  return `<svg class="pouch" viewBox="0 0 200 296" ${aria} focusable="false">
    <path d="${body}" fill="url(#pouchBody)" stroke="rgba(255,255,255,.1)" stroke-width="1.5"/>
    <path d="M16 30Q16 16 30 16H170Q184 16 184 30V48H16Z" fill="#0a0908"/>
    <path d="M24 30H176M24 38H176" stroke="rgba(255,255,255,.1)" stroke-width="1.5" stroke-linecap="round"/>
    <image href="/img/logo.webp" x="36" y="60" width="128" height="128" preserveAspectRatio="xMidYMid meet"/>
    <rect x="16" y="214" width="168" height="${mini ? 40 : 38}" fill="${flavour.band}"/>
    <path d="M16 214H184" stroke="rgba(255,255,255,.28)" stroke-width="1"/>
    ${text}
    <path d="${body}" fill="url(#pouchSheen)" pointer-events="none"/>
  </svg>`;
}

// Five bars, `level` of them lit. The aria-label gives the same info in words.
export function heatBars(level, { small = false, label = false } = {}) {
  const bars = Array.from({ length: 5 }, (_, i) => `<i class="${i < level ? "on" : ""}" style="--i:${i}"></i>`).join("");
  const text = label ? `<span class="heat__label" aria-hidden="true">Heat ${level}/5</span>` : "";
  return `<span class="heat${small ? " heat--sm" : ""}" role="img" aria-label="Heat ${level} out of 5">${bars}${text}</span>`;
}
