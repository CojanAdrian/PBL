// The site's texts in the visitor's language. They come from the server with
// the menu (Admin -> Text blocks, the `shop_…` keys).
import { esc } from "./ui.js";

let dict = {};
let rules = new Intl.PluralRules("en");
let editing = false;

export function setTexts(texts, locale, isEditing = false) {
  dict = texts || {};
  editing = Boolean(isEditing);
  try {
    rules = new Intl.PluralRules(locale || "en");
  } catch {
    rules = new Intl.PluralRules("en");
  }
}

const fill = (text, vars) => text.replace(/\{(\w+)\}/g, (m, k) => (k in vars ? String(vars[k]) : m));

// Plain text, with {placeholders} filled in. For textContent, toasts and attributes.
export function t(key, vars = {}) {
  return fill(String(dict[key] ?? key), vars);
}

// The same as HTML, not editable: escaped, line breaks as <br>, and the
// placeholders in `html` inserted as they are (the caller escapes them).
export function tx(key, html = {}) {
  return fill(esc(String(dict[key] ?? key)), html).replace(/\n/g, "<br>");
}

// Like tx(), but in the admin's Visual editor the text can be clicked and edited.
// Texts with {placeholders} are left out there (edit those under Text blocks).
export function th(key, html = {}) {
  const out = tx(key, html);
  if (!editing || /\{\w+\}/.test(String(dict[key] ?? ""))) return out;
  return `<span data-cms-text="shop_${esc(key)}">${out}</span>`;
}

// "1 pack", "3 packs", "5 упаковок": packs_one / packs_few / packs_other, by the language's plural rules.
export function packs(n) {
  const key = `packs_${rules.select(n)}`;
  return t(key in dict ? key : "packs_other", { n });
}
