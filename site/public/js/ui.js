// Small helpers shared by every module.

export const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
export const fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

export const $ = (selector, root = document) => root.querySelector(selector);
export const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

export function esc(value) {
  return String(value ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
}

// localStorage can throw (private mode, blocked cookies), so never trust it.
export const store = {
  get(key) {
    try {
      return window.localStorage.getItem(key);
    } catch {
      return null;
    }
  },
  set(key, value) {
    try {
      window.localStorage.setItem(key, value);
    } catch {
      /* ignore */
    }
  },
  remove(key) {
    try {
      window.localStorage.removeItem(key);
    } catch {
      /* ignore */
    }
  },
};

let toastTimer;
export function toast(message) {
  const el = $("#toast");
  if (!el) return;
  el.textContent = message;
  el.classList.add("is-on");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove("is-on"), 3600);
}

// Set by fx.js once smooth scrolling is running.
let lenis = null;
export function setLenis(instance) {
  lenis = instance;
}
export function getLenis() {
  return lenis;
}

export function scrollToEl(target, offset = -70) {
  const el = typeof target === "string" ? $(target) : target;
  if (!el) return;
  if (lenis) {
    lenis.scrollTo(el, { offset, duration: 1.5, easing: (t) => 1 - Math.pow(1 - t, 4) });
  } else {
    el.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
  }
}

// Roving-tabindex radio/tab groups: arrow keys move and select.
export function arrowNav(group, selector, onMove) {
  group.addEventListener("keydown", (e) => {
    const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
    const home = e.key === "Home";
    const end = e.key === "End";
    if (!step && !home && !end) return;
    const items = $$(selector, group).filter((n) => !n.hidden && !n.disabled);
    const index = items.indexOf(document.activeElement);
    if (index < 0) return;
    e.preventDefault();
    const next = home ? items[0] : end ? items[items.length - 1] : items[(index + step + items.length) % items.length];
    next.focus();
    onMove(next);
  });
}
