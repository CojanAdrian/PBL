// The site's texts in the visitor's language. They come from the server with
// the menu (Admin -> Text blocks, the `shop_…` keys).
import { esc } from "./ui.js";

let dict = {};
let rules = new Intl.PluralRules("en");

export function setTexts(texts, locale) {
  dict = texts || {};
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

// The same as HTML: the text is escaped, line breaks become <br>, and the
// placeholders in `html` are inserted as they are (the caller escapes them).
export function th(key, html = {}) {
  return fill(esc(String(dict[key] ?? key)), html).replace(/\n/g, "<br>");
}

// "1 pack", "3 packs", "5 упаковок": packs_one / packs_few / packs_other, by the language's plural rules.
export function packs(n) {
  const key = `packs_${rules.select(n)}`;
  return t(key in dict ? key : "packs_other", { n });
}
