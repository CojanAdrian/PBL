// Motion and interaction polish. Everything here is optional: if GSAP or Lenis
// fail to load, or the visitor prefers reduced motion, the page is still complete.
import { $, $$, reduce, fine, setLenis, getLenis, scrollToEl } from "./ui.js";

const gsap = window.gsap;
const ScrollTrigger = window.ScrollTrigger;
const hasGSAP = Boolean(gsap && ScrollTrigger);
const root = document.documentElement;

if (hasGSAP) gsap.registerPlugin(ScrollTrigger);

// ---------- Smooth scrolling ----------
export function initSmoothScroll() {
  if (reduce || !window.Lenis) return null;
  const lenis = new window.Lenis({ lerp: 0.1, wheelMultiplier: 1 });
  if (hasGSAP) {
    lenis.on("scroll", ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
  } else {
    const loop = (t) => {
      lenis.raf(t);
      requestAnimationFrame(loop);
    };
    requestAnimationFrame(loop);
  }
  setLenis(lenis);
  return lenis;
}

// ---------- Preloader ----------
export function runPreloader() {
  return new Promise((resolve) => {
    const done = () => {
      root.classList.add("loaded");
      try {
        window.sessionStorage.setItem("bed-seen", "1");
      } catch {
        /* ignore */
      }
      resolve();
    };
    if (!root.classList.contains("motion") || root.classList.contains("loaded") || !hasGSAP) {
      done();
      return;
    }
    const minimum = new Promise((r) => setTimeout(r, 1300));
    const loaded = new Promise((r) => {
      if (document.readyState === "complete") r();
      else window.addEventListener("load", r, { once: true });
      setTimeout(r, 2800);
    });
    Promise.all([minimum, loaded]).then(() => {
      gsap.to(".preloader__inner", { scale: 0.7, opacity: 0, duration: 0.45, ease: "power2.in" });
      gsap.to(".preloader", { yPercent: -100, duration: 0.95, delay: 0.3, ease: "power4.inOut", onComplete: done });
    });
  });
}

// ---------- Hero intro ----------
export function heroIntro() {
  if (!hasGSAP || reduce) return;
  const tl = gsap.timeline({ defaults: { ease: "power4.out" } });
  tl.from(".hero__title .line > span", { yPercent: 115, duration: 1.2, stagger: 0.14 }, 0)
    .from(".hero .eyebrow", { y: 24, opacity: 0, duration: 0.9 }, 0.1)
    .from(".hero__lead", { y: 30, opacity: 0, duration: 1 }, 0.45)
    .from(".hero__cta .btn", { y: 30, opacity: 0, duration: 0.9, stagger: 0.1 }, 0.6)
    .from(".hero__facts li", { y: 20, opacity: 0, duration: 0.8, stagger: 0.08 }, 0.8)
    .from(".hero__logo", { scale: 0.6, rotate: -14, opacity: 0, duration: 1.5, ease: "elastic.out(1, 0.6)", clearProps: "transform,opacity" }, 0.2)
    .from(".hero__pouch", { x: 90, y: 40, rotate: 16, opacity: 0, duration: 1.3, clearProps: "transform,opacity" }, 0.6)
    .from(".hero__badge", { scale: 0, rotate: -90, opacity: 0, duration: 1.1, ease: "back.out(1.8)", clearProps: "transform,opacity" }, 0.95)
    .from(".nav__bar", { y: -30, opacity: 0, duration: 0.9 }, 0.5);
}

// ---------- Nav, progress, mobile menu ----------
export function initNav() {
  const nav = $("#nav");
  const progress = $(".progress span");
  const toggle = $(".nav__toggle");
  const menu = $("#menu");
  let last = 0;
  let ticking = false;
  let menuOpen = false;

  const update = () => {
    ticking = false;
    const y = window.scrollY;
    nav.classList.toggle("is-solid", y > 30);
    if (!menuOpen) {
      const dy = y - last;
      if (y > 500 && dy > 8) nav.classList.add("is-hidden");
      else if (dy < -8 || y <= 500) nav.classList.remove("is-hidden");
    }
    last = y;
    const max = document.documentElement.scrollHeight - window.innerHeight;
    progress.style.setProperty("--p", max > 0 ? Math.min(1, y / max).toFixed(4) : 0);
  };
  window.addEventListener(
    "scroll",
    () => {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    },
    { passive: true }
  );
  nav.addEventListener("focusin", () => nav.classList.remove("is-hidden"));
  update();

  // Highlight the link for the section in view.
  const links = new Map($$(".nav__links a").map((a) => [a.getAttribute("href").slice(1), a]));
  const io = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        const link = links.get(entry.target.id);
        if (link && entry.isIntersecting) {
          links.forEach((a) => a.classList.toggle("is-active", a === link));
        }
      }
    },
    { rootMargin: "-45% 0px -50% 0px" }
  );
  links.forEach((_, id) => {
    const section = document.getElementById(id);
    if (section) io.observe(section);
  });
  const hero = $("#top");
  new IntersectionObserver(([e]) => e.isIntersecting && links.forEach((a) => a.classList.remove("is-active")), { threshold: 0.5 }).observe(hero);

  // Mobile menu
  function setMenu(open) {
    if (open === menuOpen) return;
    menuOpen = open;
    toggle.setAttribute("aria-expanded", String(open));
    toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    nav.classList.remove("is-hidden");
    const lenis = getLenis();
    if (open) {
      menu.hidden = false;
      requestAnimationFrame(() => menu.classList.add("is-open"));
      lenis?.stop();
      document.body.style.overflow = "hidden";
      $("a", menu).focus({ preventScroll: true });
    } else {
      menu.classList.remove("is-open");
      setTimeout(() => {
        if (!menuOpen) menu.hidden = true;
      }, 420);
      lenis?.start();
      document.body.style.overflow = "";
    }
  }
  toggle.addEventListener("click", () => setMenu(!menuOpen));
  document.addEventListener("keydown", (e) => {
    if (!menuOpen) return;
    if (e.key === "Escape") {
      setMenu(false);
      toggle.focus();
    }
    if (e.key === "Tab") {
      const items = [toggle, ...$$("a", menu)];
      const i = items.indexOf(document.activeElement);
      if (e.shiftKey && i <= 0) {
        e.preventDefault();
        items[items.length - 1].focus();
      } else if (!e.shiftKey && i === items.length - 1) {
        e.preventDefault();
        items[0].focus();
      }
    }
  });
  window.matchMedia("(min-width: 840px)").addEventListener("change", (e) => e.matches && setMenu(false));

  // Smooth anchor links, via Lenis when it's running.
  document.addEventListener("click", (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute("href");
    const target = id.length > 1 ? $(id) : null;
    if (!target) return;
    e.preventDefault();
    setMenu(false);
    scrollToEl(target, id === "#top" ? 0 : -60);
    history.replaceState(null, "", id);
  });
}

// ---------- Scroll-driven sections ----------
export function initScrollFX() {
  if (!hasGSAP || reduce) return;

  // Fade/rise reveals
  const items = $$("[data-reveal]");
  gsap.set(items, { opacity: 0, y: 38 });
  ScrollTrigger.batch(items, {
    start: "top 92%",
    once: true,
    onEnter: (batch) => gsap.to(batch, { opacity: 1, y: 0, duration: 1.1, ease: "power3.out", stagger: 0.09, overwrite: true }),
  });

  // Hero parallax as you scroll away
  gsap.to(".hero__word", { yPercent: -18, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
  gsap.to(".hero__visual", { y: -70, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
  gsap.to(".hero__copy", { y: -40, opacity: 0.2, ease: "none", scrollTrigger: { trigger: ".hero", start: "40% top", end: "bottom top", scrub: true } });

  // Story: words light up as you read
  const para = $("[data-words]");
  if (para) {
    const text = para.textContent.trim();
    const hidden = document.createElement("span");
    hidden.className = "sr-only";
    hidden.textContent = text;
    const visual = document.createElement("span");
    visual.setAttribute("aria-hidden", "true");
    text.split(/\s+/).forEach((word) => {
      const span = document.createElement("span");
      span.className = "word";
      span.textContent = `${word} `;
      visual.appendChild(span);
    });
    para.replaceChildren(hidden, visual);
    gsap.fromTo($$(".word", para), { opacity: 0.4 }, { opacity: 1, ease: "none", stagger: 0.12, scrollTrigger: { trigger: para, start: "top 82%", end: "bottom 55%", scrub: 0.5 } });
  }

  // Story photo: opens up and drifts
  const clip = $("[data-clip]");
  if (clip) {
    const frame = $(".story__photo-frame", clip);
    gsap.fromTo(
      frame,
      { clipPath: "inset(16% 16% 16% 16% round 32px)", scale: 0.9 },
      { clipPath: "inset(0% 0% 0% 0% round 32px)", scale: 1, ease: "none", scrollTrigger: { trigger: clip, start: "top 92%", end: "top 35%", scrub: 0.6 } }
    );
    gsap.fromTo($("img", frame), { yPercent: -5 }, { yPercent: 5, ease: "none", scrollTrigger: { trigger: clip, start: "top bottom", end: "bottom top", scrub: true } });
  }

  // Craft: pinned, sideways scroll on larger screens
  const mm = gsap.matchMedia();
  mm.add("(min-width: 900px)", () => {
    const pin = $(".craft__pin");
    const track = $(".craft__track");
    const bar = $(".craft__progress");
    const distance = () => Math.max(0, track.scrollWidth - window.innerWidth);
    gsap.to(track, {
      x: () => -distance(),
      ease: "none",
      scrollTrigger: {
        trigger: pin,
        start: "top top",
        end: () => `+=${distance()}`,
        pin: true,
        scrub: 0.7,
        anticipatePin: 1,
        invalidateOnRefresh: true,
        onUpdate: (self) => bar.style.setProperty("--p", self.progress.toFixed(4)),
      },
    });
    $$(".step__num").forEach((num) =>
      gsap.to(num, { x: -70, ease: "none", scrollTrigger: { trigger: pin, start: "top top", end: () => `+=${distance()}`, scrub: true } })
    );
  });

  window.addEventListener("load", () => ScrollTrigger.refresh());
  document.fonts?.ready.then(() => ScrollTrigger.refresh());
}

// ---------- Marquee speeds up with scrolling ----------
export function initMarquee() {
  const track = $(".marquee__track");
  if (!track || reduce) return;
  const anim = track.getAnimations()[0];
  if (!anim) return;
  let lastY = window.scrollY;
  let rate = 1;
  let visible = false;
  new IntersectionObserver(([e]) => (visible = e.isIntersecting)).observe(track);
  (function loop() {
    requestAnimationFrame(loop);
    const y = window.scrollY;
    const speed = Math.abs(y - lastY);
    lastY = y;
    if (!visible) return;
    rate += (1 + Math.min(speed * 0.35, 9) - rate) * 0.08;
    anim.playbackRate = rate;
  })();
}

// ---------- Pointer effects ----------
// Writes -1..1 pointer position into --px/--py; CSS does the moving.
export function pointerParallax(listenEl, targetEl = listenEl) {
  if (!fine || reduce || !listenEl) return;
  let tx = 0;
  let ty = 0;
  let cx = 0;
  let cy = 0;
  let raf = 0;
  const tick = () => {
    cx += (tx - cx) * 0.08;
    cy += (ty - cy) * 0.08;
    targetEl.style.setProperty("--px", cx.toFixed(3));
    targetEl.style.setProperty("--py", cy.toFixed(3));
    raf = Math.abs(tx - cx) > 0.002 || Math.abs(ty - cy) > 0.002 ? requestAnimationFrame(tick) : 0;
  };
  const start = () => {
    if (!raf) raf = requestAnimationFrame(tick);
  };
  listenEl.addEventListener(
    "pointermove",
    (e) => {
      const r = listenEl.getBoundingClientRect();
      tx = Math.max(-1, Math.min(1, ((e.clientX - r.left) / r.width - 0.5) * 2));
      ty = Math.max(-1, Math.min(1, ((e.clientY - r.top) / r.height - 0.5) * 2));
      start();
    },
    { passive: true }
  );
  listenEl.addEventListener("pointerleave", () => {
    tx = ty = 0;
    start();
  });
}

export function initMagnetic() {
  if (!fine || reduce || !hasGSAP) return;
  $$(".magnetic").forEach((el) => {
    const xTo = gsap.quickTo(el, "x", { duration: 0.7, ease: "elastic.out(1, 0.45)" });
    const yTo = gsap.quickTo(el, "y", { duration: 0.7, ease: "elastic.out(1, 0.45)" });
    el.addEventListener("pointermove", (e) => {
      const r = el.getBoundingClientRect();
      xTo((e.clientX - (r.left + r.width / 2)) * 0.28);
      yTo((e.clientY - (r.top + r.height / 2)) * 0.36);
    });
    el.addEventListener("pointerleave", () => {
      xTo(0);
      yTo(0);
    });
  });
}

// A soft light that follows the pointer across any .preset card.
export function initSpotlight() {
  if (!fine) return;
  document.addEventListener(
    "pointermove",
    (e) => {
      const card = e.target.closest?.(".preset");
      if (!card) return;
      const r = card.getBoundingClientRect();
      card.style.setProperty("--mx", `${e.clientX - r.left}px`);
      card.style.setProperty("--my", `${e.clientY - r.top}px`);
    },
    { passive: true }
  );
}

export function initCursor() {
  const el = $(".cursor");
  if (!el || !fine || reduce) return;
  let x = 0;
  let y = 0;
  let tx = 0;
  let ty = 0;
  let raf = 0;
  const tick = () => {
    x += (tx - x) * 0.2;
    y += (ty - y) * 0.2;
    el.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0)`;
    raf = Math.abs(tx - x) > 0.3 || Math.abs(ty - y) > 0.3 ? requestAnimationFrame(tick) : 0;
  };
  document.addEventListener(
    "pointermove",
    (e) => {
      if (e.pointerType !== "mouse") return;
      tx = e.clientX;
      ty = e.clientY;
      el.classList.add("is-on");
      el.classList.toggle("is-hover", Boolean(e.target.closest?.("a, button, [role=tab], [role=radio], input, textarea, label, .preset")));
      if (!raf) raf = requestAnimationFrame(tick);
    },
    { passive: true }
  );
  document.addEventListener("pointerdown", () => el.classList.add("is-down"));
  document.addEventListener("pointerup", () => el.classList.remove("is-down"));
  document.documentElement.addEventListener("pointerleave", () => el.classList.remove("is-on"));
}
