// Checks a preorder coming from the website and works out the real price on
// the server, so nobody can send a made-up total.

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const DATE_RE = /^\d{4}-\d{2}-\d{2}$/;

function lineTotal(grams, config) {
  return Math.round((grams * config.pricePer100g) / 100);
}

function isAllowedGrams(grams, config) {
  if (!Number.isInteger(grams)) return false;
  if (config.sizes.includes(grams)) return true;
  const { min, max, step } = config.custom;
  return grams >= min && grams <= max && grams % step === 0;
}

function cleanText(value, max) {
  return String(value ?? "").replace(/\s+/g, " ").trim().slice(0, max);
}

// Returns { ok: true, order } or { ok: false, errors: { field: message } }.
function validateOrder(body, config, now = new Date()) {
  const errors = {};
  const input = body && typeof body === "object" ? body : {};

  // ---- items ----
  const flavourIds = new Set(config.flavours.map((f) => f.id));
  const merged = new Map();
  const rawItems = Array.isArray(input.items) ? input.items : [];
  for (const raw of rawItems) {
    const flavour = String(raw?.flavour ?? "");
    const grams = Number(raw?.grams);
    const qty = Number(raw?.qty);
    if (!flavourIds.has(flavour) || !isAllowedGrams(grams, config) || !Number.isInteger(qty) || qty < 1) {
      errors.items = "Something in your box isn't valid. Please check it and try again.";
      break;
    }
    const key = `${flavour}:${grams}`;
    merged.set(key, { flavour, grams, qty: (merged.get(key)?.qty || 0) + qty });
  }
  const items = [...merged.values()];
  if (!errors.items) {
    if (items.length === 0) errors.items = "Add at least one pack to your box.";
    else if (items.length > config.limits.maxLines || items.some((i) => i.qty > config.limits.maxPacksPerLine)) {
      errors.items = "That's a big box! For large orders please contact Ed directly.";
    }
  }
  const totalGrams = items.reduce((sum, i) => sum + i.grams * i.qty, 0);
  if (!errors.items && totalGrams > config.limits.maxTotalGrams) {
    errors.items = "That's a big box! For large orders please contact Ed directly.";
  }

  // ---- contact details ----
  const name = cleanText(input.name, 80);
  if (name.length < 2) errors.name = "Please tell us your name.";

  const phone = String(input.phone ?? "").replace(/[\s().-]/g, "");
  if (!/^\+?\d{7,15}$/.test(phone)) errors.phone = "Enter a phone number Ed can reach you on.";

  const email = cleanText(input.email, 254).toLowerCase();
  if (email && !EMAIL_RE.test(email)) errors.email = "That email doesn't look right.";

  const delivery = input.delivery === "delivery" ? "delivery" : input.delivery === "pickup" ? "pickup" : null;
  if (!delivery) errors.delivery = "Choose pickup or delivery.";

  const address = cleanText(input.address, 300);
  if (delivery === "delivery" && address.length < 5) errors.address = "Add your address or area for delivery.";

  let date = "";
  if (input.date) {
    const text = String(input.date);
    const parsed = DATE_RE.test(text) ? new Date(`${text}T00:00:00Z`) : null;
    const today = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate()));
    const latest = new Date(today.getTime() + 120 * 86400000);
    if (!parsed || Number.isNaN(parsed.getTime()) || parsed < today || parsed > latest) {
      errors.date = "Pick a date from today onwards.";
    } else {
      date = text;
    }
  }

  const note = String(input.note ?? "").replace(/\r/g, "").trim().slice(0, 500);

  if (Object.keys(errors).length) return { ok: false, errors };

  const totalPrice = items.reduce((sum, i) => sum + lineTotal(i.grams, config) * i.qty, 0);
  const totalPacks = items.reduce((sum, i) => sum + i.qty, 0);
  return {
    ok: true,
    order: {
      name,
      phone,
      email,
      delivery,
      address: delivery === "delivery" ? address : "",
      date,
      note,
      items: items.map((i) => ({ ...i, price: lineTotal(i.grams, config) })),
      totalGrams,
      totalPacks,
      totalPrice,
      currency: config.currency,
    },
  };
}

// No 0/O/1/I so a code read over the phone can't be mistaken.
const CODE_ALPHABET = "23456789ABCDEFGHJKLMNPQRSTUVWXYZ";
function makeCode(randomInt) {
  let code = "";
  for (let i = 0; i < 5; i++) code += CODE_ALPHABET[randomInt(CODE_ALPHABET.length)];
  return `BED-${code}`;
}

function describeOrder(order, config) {
  const names = new Map(config.flavours.map((f) => [f.id, f.name]));
  return order.items.map((i) => `${i.qty} x ${i.grams} g ${names.get(i.flavour)}`);
}

module.exports = { validateOrder, makeCode, describeOrder, lineTotal };
