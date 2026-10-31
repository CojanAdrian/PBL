// Rising embers behind the hero, plus a one-off burst for the success screen.

export function createEmbers(canvas, { count = 70 } = {}) {
  const ctx = canvas.getContext("2d");
  if (!ctx) return { destroy() {} };
  let w = 0;
  let h = 0;
  let raf = 0;
  let visible = true;
  const mouse = { x: -9999, y: -9999 };
  const parts = [];

  function resize() {
    const rect = canvas.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    w = rect.width;
    h = rect.height;
    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }

  function spawn(p, initial) {
    p.x = Math.random() * w;
    p.y = initial ? Math.random() * h : h + 12;
    p.r = Math.random() * 2.1 + 0.6;
    p.vy = -(Math.random() * 0.7 + 0.25);
    p.vx = (Math.random() - 0.5) * 0.3;
    p.hue = 8 + Math.random() * 34;
    p.phase = Math.random() * Math.PI * 2;
    p.speed = Math.random() * 0.02 + 0.008;
    return p;
  }

  function frame(t) {
    raf = requestAnimationFrame(frame);
    if (!visible) return;
    ctx.clearRect(0, 0, w, h);
    ctx.globalCompositeOperation = "lighter";
    for (const p of parts) {
      p.phase += p.speed;
      p.x += p.vx + Math.sin(p.phase) * 0.35;
      p.y += p.vy;
      const dx = p.x - mouse.x;
      const dy = p.y - mouse.y;
      const dist = Math.hypot(dx, dy);
      if (dist < 130) {
        const push = (130 - dist) / 130;
        p.x += (dx / (dist || 1)) * push * 3.2;
        p.y += (dy / (dist || 1)) * push * 3.2;
      }
      if (p.y < -12 || p.x < -20 || p.x > w + 20) spawn(p, false);
      // Fade in near the bottom, burn out towards the top, flicker throughout.
      const life = Math.min(1, (h - p.y) / (h * 0.12)) * Math.max(0, p.y / h + 0.1);
      const flicker = 0.65 + Math.sin(t * 0.008 + p.phase * 3) * 0.35;
      const a = Math.max(0, life * flicker);
      ctx.fillStyle = `hsla(${p.hue}, 100%, 55%, ${a * 0.07})`;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r * 3.4, 0, Math.PI * 2);
      ctx.fill();
      ctx.fillStyle = `hsla(${p.hue + 8}, 100%, 70%, ${a * 0.95})`;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  const onMove = (e) => {
    const rect = canvas.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
  };
  const onLeave = () => {
    mouse.x = mouse.y = -9999;
  };
  const io = new IntersectionObserver(([entry]) => {
    visible = entry.isIntersecting;
  });
  const onVisibility = () => {
    if (document.hidden) {
      cancelAnimationFrame(raf);
      raf = 0;
    } else if (!raf) {
      raf = requestAnimationFrame(frame);
    }
  };

  resize();
  for (let i = 0; i < count; i++) parts.push(spawn({}, true));
  io.observe(canvas);
  window.addEventListener("resize", resize);
  canvas.parentElement.addEventListener("pointermove", onMove, { passive: true });
  canvas.parentElement.addEventListener("pointerleave", onLeave);
  document.addEventListener("visibilitychange", onVisibility);
  raf = requestAnimationFrame(frame);

  return {
    destroy() {
      cancelAnimationFrame(raf);
      io.disconnect();
      window.removeEventListener("resize", resize);
      document.removeEventListener("visibilitychange", onVisibility);
    },
  };
}

// A short shower of sparks from the middle of a canvas.
export function burst(canvas) {
  const ctx = canvas.getContext("2d");
  if (!ctx) return;
  const rect = canvas.getBoundingClientRect();
  const dpr = Math.min(window.devicePixelRatio || 1, 2);
  canvas.width = Math.round(rect.width * dpr);
  canvas.height = Math.round(rect.height * dpr);
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  const cx = rect.width / 2;
  const cy = Math.min(rect.height * 0.28, 220);
  const sparks = Array.from({ length: 110 }, () => {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * 7 + 2;
    return { x: cx, y: cy, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed - 3, r: Math.random() * 2.6 + 0.8, hue: 6 + Math.random() * 42, life: 1 };
  });
  let last = performance.now();
  (function tick(now) {
    const dt = Math.min(2, (now - last) / 16.67);
    last = now;
    ctx.clearRect(0, 0, rect.width, rect.height);
    ctx.globalCompositeOperation = "lighter";
    let alive = false;
    for (const s of sparks) {
      s.vy += 0.16 * dt;
      s.vx *= 0.985;
      s.x += s.vx * dt;
      s.y += s.vy * dt;
      s.life -= 0.012 * dt;
      if (s.life <= 0) continue;
      alive = true;
      ctx.fillStyle = `hsla(${s.hue}, 100%, 62%, ${s.life})`;
      ctx.beginPath();
      ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
      ctx.fill();
    }
    if (alive) requestAnimationFrame(tick);
    else ctx.clearRect(0, 0, rect.width, rect.height);
  })(last);
}
