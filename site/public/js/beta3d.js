// The 3D Big Ed's pack: a stand-up foil pouch (or the taster box) with the real
// label on the front and the ingredients on the back, crinkled foil, studio light.
//
// createViewer(canvas, opts) gives a live pack that follows the pointer (hero) or
// can be dragged and turned to a side (inspector). It only renders while visible
// and while something moves. renderShots() draws still packshots (used to make the
// images in public/img/packs).
import * as THREE from "three";
import { RoomEnvironment } from "/vendor/three/RoomEnvironment.js";

const TAU = Math.PI * 2;

// ---------- Painted textures ----------
function makeCanvas(w, h) {
  const c = document.createElement("canvas");
  c.width = w;
  c.height = h;
  return c;
}
function fitFont(ctx, text, maxW, size, font) {
  let s = size;
  ctx.font = font(s);
  while (ctx.measureText(text).width > maxW && s > 10) {
    s -= 2;
    ctx.font = font(s);
  }
  return s;
}
// The logo keeps its own proportions; (cx, top) is its top centre.
function drawLogo(ctx, logo, cx, top, width) {
  if (!logo) return;
  const h = width * (logo.naturalHeight / logo.naturalWidth);
  ctx.drawImage(logo, cx - width / 2, top, width, h);
}
function paintBase(ctx, W, H, meat) {
  const g = ctx.createLinearGradient(0, 0, 0, H);
  g.addColorStop(0, meat?.pouch?.top || "#2a2624");
  g.addColorStop(1, meat?.pouch?.bottom || "#0d0b0a");
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, W, H);
  // Top seal: darker band with fine crimp lines, a zip line and tear notches.
  ctx.fillStyle = meat?.pouch?.cap || "#0a0908";
  ctx.fillRect(0, 0, W, H * 0.1);
  ctx.fillStyle = "rgba(255,255,255,.045)";
  for (let x = 0; x < W; x += 9) ctx.fillRect(x, 0, 3, H * 0.062);
  ctx.fillStyle = "rgba(255,255,255,.12)";
  ctx.fillRect(W * 0.04, H * 0.083, W * 0.92, 4);
  ctx.fillStyle = "rgba(0,0,0,.5)";
  ctx.fillRect(W * 0.04, H * 0.087, W * 0.92, 3);
}
function paintFront(p, meat, logo) {
  const W = 1536;
  const H = 2304;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  paintBase(ctx, W, H, meat);
  drawLogo(ctx, logo, W / 2, H * 0.165, W * 0.8);
  ctx.textAlign = "center";
  ctx.fillStyle = "#f6e6cc";
  ctx.letterSpacing = `${W * 0.012}px`;
  const title = `${(p.meat || "beef").toUpperCase()} · SMALL BATCH`;
  fitFont(ctx, title, W * 0.8, W * 0.046, (s) => `800 ${s}px Manrope, sans-serif`);
  ctx.fillText(title, W / 2, H * 0.67);
  ctx.fillStyle = p.band || "#b8261c";
  ctx.fillRect(0, H * 0.715, W, H * 0.125);
  ctx.fillStyle = p.bandText || "#fff";
  ctx.letterSpacing = `${W * 0.006}px`;
  const name = p.name.toUpperCase();
  const s = fitFont(ctx, name, W * 0.86, W * 0.12, (z) => `700 ${z}px Oswald, Impact, sans-serif`);
  ctx.fillText(name, W / 2, H * 0.778 + s * 0.36);
  ctx.fillStyle = "rgba(255,255,255,.55)";
  ctx.letterSpacing = `${W * 0.01}px`;
  ctx.font = `700 ${W * 0.032}px Manrope, sans-serif`;
  ctx.fillText(`NET WT. ${p.grams} G`, W / 2, H * 0.905);
  ctx.fillText("MADE IN MOLDOVA", W / 2, H * 0.94);
  return c;
}
function paintBack(p, meat, texts) {
  const W = 1536;
  const H = 2304;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  paintBase(ctx, W, H, meat);
  ctx.fillStyle = "#f6e6cc";
  ctx.fillRect(W * 0.11, H * 0.15, W * 0.78, H * 0.52);
  ctx.fillStyle = "#16120f";
  ctx.textAlign = "left";
  ctx.font = `700 ${W * 0.075}px Oswald, Impact, sans-serif`;
  ctx.fillText(texts.ingredients.toUpperCase(), W * 0.16, H * 0.22);
  ctx.fillRect(W * 0.16, H * 0.235, W * 0.68, 6);
  ctx.font = `600 ${W * 0.038}px Manrope, sans-serif`;
  const words = `${texts.list}.`.split(" ");
  let line = "";
  let y = H * 0.28;
  for (const w of words) {
    if (ctx.measureText(`${line} ${w}`).width > W * 0.68) {
      ctx.fillText(line, W * 0.16, y);
      line = w;
      y += W * 0.052;
    } else line = line ? `${line} ${w}` : w;
  }
  ctx.fillText(line, W * 0.16, y);
  ctx.fillStyle = "#b3261b";
  ctx.font = `800 ${W * 0.038}px Manrope, sans-serif`;
  ctx.fillText(texts.soy, W * 0.16, y + W * 0.075);
  ctx.fillStyle = "#5a4f45";
  ctx.font = `600 ${W * 0.03}px Manrope, sans-serif`;
  ctx.fillText(texts.store, W * 0.16, H * 0.64);
  // Barcode
  ctx.fillStyle = "#f6e6cc";
  let x = W * 0.11;
  for (let i = 0; i < 52; i++) {
    const w = (((i * 7) % 5) + 1) * W * 0.0038;
    if (i % 2 === 0) ctx.fillRect(x, H * 0.73, w, H * 0.085);
    x += w + W * 0.0038;
  }
  ctx.fillStyle = "rgba(255,255,255,.6)";
  ctx.textAlign = "right";
  ctx.font = `700 ${W * 0.032}px Manrope, sans-serif`;
  ctx.fillText(`NET WT. ${p.grams} G`, W * 0.89, H * 0.77);
  ctx.fillText(texts.made.toUpperCase(), W * 0.89, H * 0.93);
  return c;
}
function paintBox(box, stripes, logo) {
  const W = 1536;
  const H = 1536;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  const g = ctx.createLinearGradient(0, 0, 0, H);
  g.addColorStop(0, "#2a2624");
  g.addColorStop(1, "#0d0b0a");
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, W, H);
  drawLogo(ctx, logo, W / 2, H * 0.08, W * 0.74);
  ctx.fillStyle = "#f6e6cc";
  ctx.textAlign = "center";
  ctx.letterSpacing = `${W * 0.006}px`;
  const name = box.name.toUpperCase();
  const s = fitFont(ctx, name, W * 0.86, W * 0.11, (z) => `700 ${z}px Oswald, Impact, sans-serif`);
  ctx.fillText(name, W / 2, H * 0.66 + s * 0.3);
  const w = W / stripes.length;
  stripes.forEach((col, i) => {
    ctx.fillStyle = col;
    ctx.fillRect(i * w, H * 0.77, w + 1, H * 0.1);
  });
  ctx.fillStyle = "rgba(255,255,255,.55)";
  ctx.font = `700 ${W * 0.034}px Manrope, sans-serif`;
  ctx.fillText(`${box.grams} G · MADE IN MOLDOVA`, W / 2, H * 0.95);
  return c;
}
// Crinkled foil: soft random blobs plus long creases, used as a bump map.
let crinkle = null;
function crinkleMap() {
  if (crinkle) return crinkle;
  const S = 1024;
  const c = makeCanvas(S, S * 1.5);
  const ctx = c.getContext("2d");
  ctx.fillStyle = "#808080";
  ctx.fillRect(0, 0, S, S * 1.5);
  let seed = 7;
  const rnd = () => ((seed = (seed * 16807) % 2147483647) / 2147483647);
  for (let i = 0; i < 260; i++) {
    const x = rnd() * S;
    const y = rnd() * S * 1.5;
    const r = 20 + rnd() * 120;
    const g = ctx.createRadialGradient(x, y, 0, x, y, r);
    const v = rnd() > 0.5 ? 255 : 0;
    g.addColorStop(0, `rgba(${v},${v},${v},${0.05 + rnd() * 0.08})`);
    g.addColorStop(1, `rgba(${v},${v},${v},0)`);
    ctx.fillStyle = g;
    ctx.fillRect(x - r, y - r, r * 2, r * 2);
  }
  ctx.lineCap = "round";
  for (let i = 0; i < 46; i++) {
    const x = rnd() * S;
    const y = rnd() * S * 1.5;
    const a = rnd() * Math.PI;
    const l = 60 + rnd() * 280;
    const v = rnd() > 0.5 ? 255 : 0;
    ctx.strokeStyle = `rgba(${v},${v},${v},${0.12 + rnd() * 0.15})`;
    ctx.lineWidth = 1 + rnd() * 3;
    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.quadraticCurveTo(x + Math.cos(a) * l * 0.5 + (rnd() - 0.5) * 40, y + Math.sin(a) * l * 0.5, x + Math.cos(a) * l, y + Math.sin(a) * l);
    ctx.stroke();
  }
  // Fine creases just under the seal.
  for (let i = 0; i < 30; i++) {
    const x = rnd() * S;
    ctx.strokeStyle = `rgba(0,0,0,${0.15 + rnd() * 0.2})`;
    ctx.lineWidth = 1 + rnd() * 2;
    ctx.beginPath();
    ctx.moveTo(x, S * 0.155);
    ctx.lineTo(x + (rnd() - 0.5) * 30, S * 0.155 + 30 + rnd() * 90);
    ctx.stroke();
  }
  crinkle = new THREE.CanvasTexture(c);
  return crinkle;
}

// ---------- Geometry ----------
function pouchGeometry() {
  const geo = new THREE.BoxGeometry(2, 3, 0.7, 40, 60, 6);
  const pos = geo.attributes.position;
  const v = new THREE.Vector3();
  for (let i = 0; i < pos.count; i++) {
    v.fromBufferAttribute(pos, i);
    const t = (v.y + 1.5) / 3;
    const seal = 0.9;
    let depth;
    if (t > seal) depth = 0.04;
    else {
      const u = t / seal;
      depth = 0.16 + 0.84 * Math.pow(Math.sin(Math.PI * (0.1 + 0.9 * u)), 0.55);
      if (u > 0.86) depth = THREE.MathUtils.lerp(depth, 0.04, (u - 0.86) / 0.14);
    }
    const edge = 1 - 0.65 * Math.pow(Math.abs(v.x), 4);
    const sign = Math.sign(v.z) || 1;
    let z = sign * 0.35 * depth * edge * (Math.abs(v.z) / 0.35);
    // Gentle dents, so the foil isn't perfectly smooth.
    if (Math.abs(v.z) > 0.34) z += sign * depth * (0.01 * Math.sin(v.x * 5.3 + v.y * 2.1) + 0.006 * Math.sin(v.x * 11 - v.y * 7));
    if (t > seal) v.x *= 1.012;
    pos.setXYZ(i, v.x, v.y, z);
  }
  geo.computeVertexNormals();
  return geo;
}

// ---------- Shared setup ----------
function setup(canvas, { alpha = true } = {}) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha, preserveDrawingBuffer: false, powerPreference: "high-performance" });
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 0.95;
  renderer.setClearColor(0x000000, 0);
  const scene = new THREE.Scene();
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(renderer), 0.03).texture;
  const camera = new THREE.PerspectiveCamera(22, 1, 0.1, 100);
  const key = new THREE.DirectionalLight("#fff3e8", 2.1);
  key.position.set(-3.2, 4, 6);
  scene.add(key);
  const rimL = new THREE.DirectionalLight("#ffb27a", 1.7);
  rimL.position.set(-5, 2.5, -4);
  scene.add(rimL);
  const rimR = new THREE.DirectionalLight("#ffe2c4", 1.2);
  rimR.position.set(5.5, 1, -3.5);
  scene.add(rimR);
  const fillL = new THREE.DirectionalLight("#ffffff", 0.5);
  fillL.position.set(4, -2, 5);
  scene.add(fillL);
  return { renderer, scene, camera };
}

function makeLibrary(renderer, { meats, logo, texts, stripes }) {
  const aniso = renderer.capabilities.getMaxAnisotropy();
  const tex = (cnv) => {
    const t = new THREE.CanvasTexture(cnv);
    t.colorSpace = THREE.SRGBColorSpace;
    t.anisotropy = aniso;
    return t;
  };
  const bump = crinkleMap();
  const foil = (opts) =>
    new THREE.MeshPhysicalMaterial({ roughness: 0.62, metalness: 0.04, clearcoat: 0.18, clearcoatRoughness: 0.55, sheen: 0.1, sheenRoughness: 0.85, sheenColor: new THREE.Color("#ffffff"), envMapIntensity: 0.26, bumpMap: bump, bumpScale: 0.35, ...opts });
  const pouchGeo = pouchGeometry();
  const boxGeo = new THREE.BoxGeometry(2.4, 2.4, 1.1, 1, 1, 1);
  const cache = new Map();
  return function meshFor(p) {
    if (cache.has(p.id)) return cache.get(p.id);
    const meat = meats.find((m) => m.id === p.meat);
    let mesh;
    if (p.box) {
      const face = foil({ map: tex(paintBox(p, stripes, logo)), metalness: 0.1, bumpScale: 0.15 });
      const side = foil({ color: "#1d1917", metalness: 0.1, bumpScale: 0.15 });
      mesh = new THREE.Mesh(boxGeo, [side, side, side, side, face, face]);
    } else {
      const front = foil({ map: tex(paintFront(p, meat, logo)) });
      const back = foil({ map: tex(paintBack(p, meat, texts)) });
      const side = foil({ color: meat?.pouch?.bottom || "#151210" });
      mesh = new THREE.Mesh(pouchGeo, [side, side, side, side, front, back]);
    }
    cache.set(p.id, mesh);
    return mesh;
  };
}

export function loadAssets(logoUrl) {
  return Promise.all([
    new Promise((res) => {
      const img = new Image();
      img.onload = () => res(img);
      img.onerror = () => res(null);
      img.src = logoUrl;
    }),
    document.fonts ? Promise.all([document.fonts.load("700 100px Oswald"), document.fonts.load("800 40px Manrope"), document.fonts.load("600 40px Manrope")]).catch(() => {}) : Promise.resolve(),
  ]).then(([logo]) => logo);
}

// ---------- Live viewer ----------
export function createViewer(canvas, { meats = [], logo = null, texts, stripes = [], mode = "hero", fill = 0.62, reduce = false, idle: idleOn = true, maxDpr = 2 } = {}) {
  let parts;
  try {
    parts = setup(canvas);
  } catch {
    return null;
  }
  const { renderer, scene, camera } = parts;
  const dpr = Math.min(window.devicePixelRatio || 1, maxDpr);
  renderer.setPixelRatio(dpr);
  const meshFor = makeLibrary(renderer, { meats, logo, texts, stripes });
  const holder = new THREE.Group();
  const pack = new THREE.Group();
  holder.add(pack);
  scene.add(holder);

  let current = null;
  function show(p) {
    if (current === p) return;
    if (current) pack.remove(meshFor(current));
    pack.add(meshFor(p));
    current = p;
  }

  function resize() {
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    const vis = Math.max(3 / fill, (2.6 / fill) / camera.aspect);
    camera.position.set(0, 0, vis / 2 / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)));
    camera.lookAt(0, 0, 0);
    camera.updateProjectionMatrix();
    wake();
  }

  // State: yaw is what we show, target what we ease towards.
  const REST = mode === "hero" ? -0.42 : -0.3;
  let yaw = REST;
  let target = REST;
  let pitch = 0;
  let tiltX = 0;
  let tiltY = 0;
  let tX = 0;
  let tY = 0;
  let swap = null; // { to, t }
  let visible = true;
  let raf = 0;
  let last = 0;
  const idle = mode === "hero" && !reduce && idleOn;

  function wake() {
    if (!raf && visible) {
      last = performance.now();
      raf = requestAnimationFrame(tick);
    }
  }
  function tick(now) {
    raf = 0;
    const dt = Math.min((now - last) / 1000, 1 / 20);
    last = now;
    const k = 1 - Math.exp(-dt * (drag ? 30 : 6));
    if (!drag) {
      yaw += (target - yaw) * k;
    }
    tiltX += (tX - tiltX) * (1 - Math.exp(-dt * 5));
    tiltY += (tY - tiltY) * (1 - Math.exp(-dt * 5));
    const time = now / 1000;
    let spin = 0;
    let scale = 1;
    if (swap) {
      swap.t += dt / 0.8;
      const e = swap.t < 0.5 ? 4 * swap.t ** 3 : 1 - Math.pow(-2 * swap.t + 2, 3) / 2;
      spin = e * TAU;
      scale = 1 - Math.sin(Math.min(1, swap.t) * Math.PI) * 0.08;
      if (swap.t >= 0.5 && !swap.done) {
        swap.done = true;
        show(swap.to);
      }
      if (swap.t >= 1) swap = null;
    }
    pack.rotation.y = yaw + spin + tiltX * 0.35 + (idle ? Math.sin(time * 0.45) * 0.08 : 0);
    pack.rotation.x = pitch - tiltY * 0.14 + (idle ? Math.sin(time * 0.6) * 0.025 : 0);
    pack.rotation.z = idle ? Math.sin(time * 0.35) * 0.03 : 0;
    holder.position.y = idle ? Math.sin(time * 0.8) * 0.05 : 0;
    pack.scale.setScalar(scale);
    renderer.render(scene, camera);
    const moving = idle || swap || drag || Math.abs(target - yaw) > 0.0005 || Math.abs(tX - tiltX) > 0.001 || Math.abs(tY - tiltY) > 0.001;
    if (moving && visible) raf = requestAnimationFrame(tick);
  }

  // Pointer tilt (hero) / drag to turn (inspector)
  let drag = null;
  if (mode === "hero" && window.matchMedia("(hover: hover)").matches) {
    window.addEventListener(
      "pointermove",
      (e) => {
        tX = (e.clientX / innerWidth) * 2 - 1;
        tY = -((e.clientY / innerHeight) * 2 - 1);
        wake();
      },
      { passive: true }
    );
  }
  if (mode === "inspect") {
    canvas.style.touchAction = "pan-y";
    canvas.addEventListener("pointerdown", (e) => {
      drag = { x: e.clientX, id: e.pointerId, v: 0 };
      canvas.setPointerCapture(e.pointerId);
      canvas.classList.add("is-grabbing");
      wake();
    });
    canvas.addEventListener("pointermove", (e) => {
      if (!drag || e.pointerId !== drag.id) return;
      const dx = e.clientX - drag.x;
      drag.x = e.clientX;
      yaw += dx * 0.012;
      drag.v = dx * 0.012;
      wake();
    });
    const end = () => {
      if (!drag) return;
      // Settle on the nearest face (front, side or back) after a flick.
      const fling = yaw + drag.v * 8;
      target = Math.round((fling - REST) / (Math.PI / 2)) * (Math.PI / 2) + REST;
      drag = null;
      canvas.classList.remove("is-grabbing");
      onTurn?.(target);
      wake();
    };
    canvas.addEventListener("pointerup", end);
    canvas.addEventListener("pointercancel", end);
  }
  let onTurn = null;

  new IntersectionObserver(([e]) => {
    visible = e.isIntersecting;
    if (visible) wake();
  }).observe(canvas);
  new ResizeObserver(resize).observe(canvas);
  resize();

  return {
    setProduct(p, { animate = true } = {}) {
      if (!current || !animate || reduce) {
        show(p);
        wake();
        return;
      }
      if (p === current) return;
      swap = { to: p, t: 0, done: false };
      wake();
    },
    // 0 = front, 1 = side, 2 = back (in quarter turns from the resting angle).
    face(n) {
      const base = Math.round((yaw - REST) / TAU) * TAU;
      target = base + REST + (n * Math.PI) / 2;
      wake();
    },
    reset() {
      target = REST;
      yaw = ((yaw - REST) % TAU) + REST;
      wake();
    },
    onTurn(fn) {
      onTurn = fn;
    },
    get faceIndex() {
      return (((Math.round((target - REST) / (Math.PI / 2)) % 4) + 4) % 4);
    },
  };
}

// ---------- Still packshots ----------
export function renderShots(list, { meats = [], logo = null, texts, stripes = [], width = 1200, height = 1500, yaw = -0.38, fill = 0.8 } = {}) {
  const canvas = makeCanvas(width, height);
  const { renderer, scene, camera } = setup(canvas);
  renderer.setPixelRatio(1);
  renderer.setSize(width, height, false);
  camera.aspect = width / height;
  const vis = 3 / fill;
  camera.position.set(0, 0, vis / 2 / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)));
  camera.updateProjectionMatrix();
  const meshFor = makeLibrary(renderer, { meats, logo, texts, stripes });
  const out = {};
  for (const p of list) {
    const m = meshFor(p);
    scene.add(m);
    m.rotation.set(0.04, p.box ? yaw * 0.9 : yaw, 0.012);
    renderer.render(scene, camera);
    out[p.id] = canvas.toDataURL("image/png");
    scene.remove(m);
  }
  renderer.dispose();
  return out;
}
