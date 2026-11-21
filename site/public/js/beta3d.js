// The 3D pack for the beta page. One WebGL canvas fixed behind the page; the pack
// flies to whichever "anchor" element is on screen (hero, intro, flavours), follows
// the pointer, can be dragged to spin, and explodes into its ingredients when the
// flavour changes or when it is tapped.
import * as THREE from "three";
import { RoomEnvironment } from "/vendor/three/RoomEnvironment.js";

const TAU = Math.PI * 2;
const rand = (a, b) => a + Math.random() * (b - a);
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
const ease = {
  inOutCubic: (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2),
  outCubic: (t) => 1 - Math.pow(1 - t, 3),
  outBack: (t) => 1 + 2.4 * Math.pow(t - 1, 3) + 1.4 * Math.pow(t - 1, 2),
  outBounce: (t) => {
    const n = 7.5625;
    const d = 2.75;
    if (t < 1 / d) return n * t * t;
    if (t < 2 / d) return n * (t -= 1.5 / d) * t + 0.75;
    if (t < 2.5 / d) return n * (t -= 2.25 / d) * t + 0.9375;
    return n * (t -= 2.625 / d) * t + 0.984375;
  },
};

// ---------- Labels painted on canvas (they use the page's own fonts) ----------
function makeCanvas(w, h) {
  const c = document.createElement("canvas");
  c.width = w;
  c.height = h;
  return c;
}
function fitText(ctx, text, maxW, size, font) {
  let s = size;
  ctx.font = font(s);
  while (ctx.measureText(text).width > maxW && s > 10) {
    s -= 2;
    ctx.font = font(s);
  }
  return s;
}
function paintBase(ctx, W, H, top, bottom, cap) {
  const g = ctx.createLinearGradient(0, 0, 0, H);
  g.addColorStop(0, top);
  g.addColorStop(1, bottom);
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, W, H);
  ctx.fillStyle = cap;
  ctx.fillRect(0, 0, W, H * 0.1);
  ctx.strokeStyle = "rgba(255,255,255,.12)";
  ctx.lineWidth = W * 0.006;
  for (const y of [0.045, 0.075]) {
    ctx.beginPath();
    ctx.moveTo(W * 0.05, H * y);
    ctx.lineTo(W * 0.95, H * y);
    ctx.stroke();
  }
}
function paintFront(p, meat, logo) {
  const W = 1024;
  const H = 1536;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  paintBase(ctx, W, H, meat?.pouch?.top || "#2a2624", meat?.pouch?.bottom || "#0d0b0a", meat?.pouch?.cap || "#0a0908");
  if (logo) ctx.drawImage(logo, W * 0.17, H * 0.17, W * 0.66, W * 0.66);
  ctx.textAlign = "center";
  ctx.fillStyle = "#efe8e1";
  const title = `PREMIUM ${(p.meat || "beef").toUpperCase()} JERKY`;
  ctx.letterSpacing = `${W * 0.008}px`;
  fitText(ctx, title, W * 0.84, W * 0.055, (s) => `800 ${s}px Manrope, sans-serif`);
  ctx.fillText(title, W / 2, H * 0.7);
  ctx.fillStyle = p.band || "#b8261c";
  ctx.fillRect(0, H * 0.725, W, H * 0.12);
  ctx.fillStyle = p.bandText || "#fff";
  ctx.letterSpacing = `${W * 0.006}px`;
  const name = p.name.toUpperCase();
  const s = fitText(ctx, name, W * 0.86, W * 0.12, (z) => `400 ${z}px Anton, Impact, sans-serif`);
  ctx.fillText(name, W / 2, H * 0.785 + s * 0.36);
  ctx.fillStyle = "rgba(255,255,255,.6)";
  ctx.letterSpacing = `${W * 0.006}px`;
  ctx.font = `700 ${W * 0.036}px Manrope, sans-serif`;
  ctx.fillText(`NET WT. ${p.grams} G`, W / 2, H * 0.92);
  return c;
}
function paintBack(p, meat, texts) {
  const W = 1024;
  const H = 1536;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  paintBase(ctx, W, H, meat?.pouch?.top || "#2a2624", meat?.pouch?.bottom || "#0d0b0a", meat?.pouch?.cap || "#0a0908");
  ctx.fillStyle = "#f4ecdf";
  ctx.fillRect(W * 0.12, H * 0.16, W * 0.76, H * 0.5);
  ctx.fillStyle = "#1c1814";
  ctx.textAlign = "left";
  ctx.font = `400 ${W * 0.07}px Anton, Impact, sans-serif`;
  ctx.fillText(texts.ingredients.toUpperCase(), W * 0.17, H * 0.23);
  ctx.font = `600 ${W * 0.038}px Manrope, sans-serif`;
  const words = `${texts.list}.`.split(" ");
  let line = "";
  let y = H * 0.29;
  for (const w of words) {
    if (ctx.measureText(`${line} ${w}`).width > W * 0.66) {
      ctx.fillText(line, W * 0.17, y);
      line = w;
      y += W * 0.052;
    } else line = line ? `${line} ${w}` : w;
  }
  ctx.fillText(line, W * 0.17, y);
  ctx.fillStyle = "#c42a1d";
  ctx.font = `800 ${W * 0.038}px Manrope, sans-serif`;
  ctx.fillText(texts.soy, W * 0.17, y + W * 0.08);
  // barcode
  ctx.fillStyle = "#f4ecdf";
  let x = W * 0.12;
  for (let i = 0; i < 46; i++) {
    const w = (((i * 7) % 5) + 1) * W * 0.004;
    if (i % 2 === 0) ctx.fillRect(x, H * 0.73, w, H * 0.08);
    x += w + W * 0.004;
  }
  ctx.fillStyle = "rgba(255,255,255,.7)";
  ctx.textAlign = "right";
  ctx.font = `700 ${W * 0.034}px Manrope, sans-serif`;
  ctx.fillText(`NET WT. ${p.grams} G`, W * 0.88, H * 0.77);
  ctx.fillText(texts.made.toUpperCase(), W * 0.88, H * 0.92);
  return c;
}
function paintBox(box, stripes, logo) {
  const W = 1024;
  const H = 1024;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  const g = ctx.createLinearGradient(0, 0, 0, H);
  g.addColorStop(0, "#2a2624");
  g.addColorStop(1, "#0d0b0a");
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, W, H);
  if (logo) ctx.drawImage(logo, W * 0.25, H * 0.08, W * 0.5, W * 0.5);
  ctx.fillStyle = "#efe8e1";
  ctx.textAlign = "center";
  const name = box.name.toUpperCase();
  const s = fitText(ctx, name, W * 0.86, W * 0.12, (z) => `400 ${z}px Anton, Impact, sans-serif`);
  ctx.fillText(name, W / 2, H * 0.67 + s * 0.3);
  const w = W / stripes.length;
  stripes.forEach((col, i) => {
    ctx.fillStyle = col;
    ctx.fillRect(i * w, H * 0.78, w + 1, H * 0.1);
  });
  ctx.fillStyle = "rgba(255,255,255,.6)";
  ctx.font = `700 ${W * 0.036}px Manrope, sans-serif`;
  ctx.fillText(`${box.grams} G`, W / 2, H * 0.95);
  return c;
}

// ---------- Geometry ----------
// A stand-up pouch: a subdivided box puffed in the middle, crimped flat at the
// top seal and pinched at the side seams.
function pouchGeometry() {
  const geo = new THREE.BoxGeometry(2, 3, 0.7, 28, 42, 6);
  const pos = geo.attributes.position;
  const v = new THREE.Vector3();
  for (let i = 0; i < pos.count; i++) {
    v.fromBufferAttribute(pos, i);
    const t = (v.y + 1.5) / 3;
    const seal = 0.9;
    let depth;
    if (t > seal) depth = 0.05;
    else {
      const u = t / seal;
      depth = 0.16 + 0.84 * Math.pow(Math.sin(Math.PI * (0.1 + 0.9 * u)), 0.55);
      if (u > 0.86) depth = THREE.MathUtils.lerp(depth, 0.05, (u - 0.86) / 0.14);
    }
    const edge = 1 - 0.65 * Math.pow(Math.abs(v.x), 4);
    const sign = Math.sign(v.z) || 1;
    const zMag = Math.abs(v.z) / 0.35;
    let z = sign * 0.35 * depth * edge * zMag;
    if (Math.abs(v.z) > 0.34) z += sign * 0.012 * Math.sin(v.x * 7 + v.y * 2.3) * depth;
    if (t > seal) v.x *= 1.015;
    if (t < 0.03) v.x *= 0.985;
    pos.setXYZ(i, v.x, v.y, z);
  }
  geo.computeVertexNormals();
  return geo;
}
function boxGeometry() {
  return new THREE.BoxGeometry(2.3, 2.3, 1.1, 2, 2, 2);
}
function teardrop(R, h, seg = 18) {
  const pts = [];
  for (let i = 0; i <= seg; i++) {
    const t = i / seg;
    pts.push(new THREE.Vector2(Math.max(0.0001, R * Math.pow(Math.sin(Math.PI * t), 0.85) * (1 - 0.55 * t)), h * t - h / 2));
  }
  return new THREE.LatheGeometry(pts, 20);
}
function spiceTexture() {
  const c = makeCanvas(256, 64);
  const ctx = c.getContext("2d");
  const g = ctx.createLinearGradient(0, 0, 256, 64);
  g.addColorStop(0, "#6e2a16");
  g.addColorStop(0.5, "#57200f");
  g.addColorStop(1, "#7a321a");
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, 256, 64);
  for (let i = 0; i < 520; i++) {
    ctx.fillStyle = ["#2a120a", "#c25a1c", "#d98a3a", "#3b1a0e", "#8c3a1c"][i % 5];
    ctx.globalAlpha = rand(0.4, 0.9);
    ctx.fillRect(rand(0, 256), rand(0, 64), rand(1, 3), rand(1, 3));
  }
  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function buildIngredients() {
  const G = {};
  const M = {};
  // Jerky strip: thin, wavy, ragged edges, spice specks.
  G.jerky = new THREE.BoxGeometry(1, 0.05, 0.3, 24, 1, 6);
  {
    const p = G.jerky.attributes.position;
    for (let i = 0; i < p.count; i++) {
      const x = p.getX(i);
      let z = p.getZ(i);
      const y = p.getY(i) + 0.08 * Math.sin(x * 3.6 + 0.4) + 0.025 * Math.sin(z * 14);
      z *= (1 + 0.18 * Math.sin(x * 11.3) + 0.1 * Math.sin(x * 23)) * (1 - 0.4 * Math.pow(Math.abs(x) / 0.5, 4));
      p.setXYZ(i, x, y, z);
    }
    G.jerky.computeVertexNormals();
  }
  M.jerky = new THREE.MeshStandardMaterial({ map: spiceTexture(), roughness: 0.78, metalness: 0.05 });

  G.pepper = new THREE.IcosahedronGeometry(0.075, 1);
  {
    const p = G.pepper.attributes.position;
    for (let i = 0; i < p.count; i++) {
      const k = 1 + 0.12 * Math.sin(i * 12.9898);
      p.setXYZ(i, p.getX(i) * k, p.getY(i) * k, p.getZ(i) * k);
    }
    G.pepper.computeVertexNormals();
  }
  M.pepper = new THREE.MeshStandardMaterial({ color: "#2b1f19", roughness: 0.6 });

  G.sugar = new THREE.BoxGeometry(0.16, 0.16, 0.16);
  M.sugar = new THREE.MeshStandardMaterial({ color: "#b5783a", roughness: 0.95 });

  G.sesame = new THREE.SphereGeometry(0.04, 10, 8);
  G.sesame.scale(1, 0.45, 0.62);
  M.sesame = new THREE.MeshStandardMaterial({ color: "#f0e2c2", roughness: 0.6 });

  G.garlic = teardrop(0.17, 0.44);
  {
    const p = G.garlic.attributes.position;
    for (let i = 0; i < p.count; i++) {
      const y = p.getY(i);
      p.setXYZ(i, p.getX(i) * 0.78 + 0.07 * Math.cos((y / 0.44) * Math.PI), y, p.getZ(i));
    }
    G.garlic.computeVertexNormals();
  }
  M.garlic = new THREE.MeshPhysicalMaterial({ color: "#f3ead9", roughness: 0.45, sheen: 0.6, sheenColor: new THREE.Color("#ffffff") });

  G.drop = teardrop(0.12, 0.32);
  M.honey = new THREE.MeshPhysicalMaterial({ color: "#f2a313", roughness: 0.06, clearcoat: 1, clearcoatRoughness: 0.05, emissive: "#6b3500", emissiveIntensity: 0.35 });
  M.soy = new THREE.MeshPhysicalMaterial({ color: "#2a1209", roughness: 0.08, clearcoat: 1, clearcoatRoughness: 0.05 });

  // Chilli: a tapered lathe bent into a curve, with a green stem.
  {
    const pts = [];
    for (let i = 0; i <= 16; i++) {
      const t = i / 16;
      pts.push(new THREE.Vector2(Math.max(0.0001, 0.1 * Math.pow(t, 0.55)), t * 0.95 - 0.475));
    }
    pts.push(new THREE.Vector2(0.0001, 0.48));
    G.chili = new THREE.LatheGeometry(pts, 18);
    const p = G.chili.attributes.position;
    for (let i = 0; i < p.count; i++) {
      const y = p.getY(i);
      const t = (y + 0.475) / 0.95;
      p.setX(i, p.getX(i) + 0.32 * Math.pow(1 - t, 2));
    }
    G.chili.computeVertexNormals();
    G.stem = new THREE.CylinderGeometry(0.022, 0.04, 0.16, 8);
    G.stem.translate(0, 0.54, 0);
  }
  M.chili = new THREE.MeshPhysicalMaterial({ color: "#d01f12", roughness: 0.28, clearcoat: 0.8, clearcoatRoughness: 0.15 });
  M.stem = new THREE.MeshStandardMaterial({ color: "#4f7f2f", roughness: 0.6 });
  return { G, M };
}

const RECIPES = {
  texas: [["jerky", 7], ["pepper", 18], ["sugar", 8]],
  garlic: [["jerky", 7], ["garlic", 9], ["sesame", 24], ["soy", 6]],
  honey: [["jerky", 7], ["honey", 9], ["chili", 7]],
  taster: [["mini", 6], ["jerky", 6]],
  any: [["jerky", 9], ["pepper", 12]],
};
const slugOf = (p) => (p.box ? "taster" : /texas|bbq/i.test(p.id) ? "texas" : /garlic|teriyaki/i.test(p.id) ? "garlic" : /honey|sriracha/i.test(p.id) ? "honey" : "any");

// ---------- The scene ----------
export function createScene({ canvas, meats = [], logoUrl, texts, reduce = false, flavours = [] }) {
  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: "high-performance" });
  } catch {
    return null;
  }
  if (!renderer.getContext()) return null;
  const mobile = window.matchMedia("(max-width: 899px)").matches;
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, mobile ? 1.6 : 2));
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  renderer.setClearColor(0x000000, 0);

  const scene = new THREE.Scene();
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(renderer), 0.04).texture;
  const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 100);
  camera.position.set(0, 0, 12);

  const key = new THREE.DirectionalLight("#fff3e6", 1.6);
  key.position.set(3, 4, 6);
  scene.add(key);
  const rim = new THREE.PointLight("#ff5a1f", 40, 30, 1.6);
  rim.position.set(-3.5, 2.5, -3);
  scene.add(rim);
  const fillLight = new THREE.PointLight("#ffd2b0", 12, 30, 1.6);
  fillLight.position.set(4, -2, 4);
  scene.add(fillLight);

  // root: placed on the active anchor · bob: floating · spin: pointer, drag, spins
  const root = new THREE.Group();
  const bob = new THREE.Group();
  const spin = new THREE.Group();
  scene.add(root);
  root.add(bob);
  bob.add(spin);

  const maxAniso = renderer.capabilities.getMaxAnisotropy();
  const texFrom = (cnv) => {
    const t = new THREE.CanvasTexture(cnv);
    t.colorSpace = THREE.SRGBColorSpace;
    t.anisotropy = maxAniso;
    return t;
  };
  const meatOf = (p) => meats.find((m) => m.id === p.meat);
  let logo = null;
  const matCache = new Map();
  function materialsFor(p) {
    if (matCache.has(p.id)) return matCache.get(p.id);
    const meat = meatOf(p);
    const foil = (opts) => new THREE.MeshPhysicalMaterial({ roughness: 0.34, metalness: 0.25, clearcoat: 0.7, clearcoatRoughness: 0.22, ...opts });
    let mats;
    if (p.box) {
      const stripes = flavours.map((f) => f.band);
      const front = foil({ map: texFrom(paintBox(p, stripes, logo)), metalness: 0.1 });
      const side = foil({ color: "#1d1917", metalness: 0.1 });
      mats = [side, side, side, side, front, front];
    } else {
      const front = foil({ map: texFrom(paintFront(p, meat, logo)) });
      const back = foil({ map: texFrom(paintBack(p, meat, texts)) });
      const side = foil({ color: meat?.pouch?.bottom || "#151210" });
      mats = [side, side, side, side, front, back];
    }
    matCache.set(p.id, mats);
    return mats;
  }

  const pouchGeo = pouchGeometry();
  const boxGeo = boxGeometry();
  const pack = new THREE.Mesh(pouchGeo, [new THREE.MeshStandardMaterial({ color: "#1a1513" })]);
  spin.add(pack);

  // Soft shadow under the pack
  const shadowTex = (() => {
    const c = makeCanvas(256, 64);
    const ctx = c.getContext("2d");
    const g = ctx.createRadialGradient(128, 32, 0, 128, 32, 128);
    g.addColorStop(0, "rgba(0,0,0,.55)");
    g.addColorStop(1, "rgba(0,0,0,0)");
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 256, 64);
    return new THREE.CanvasTexture(c);
  })();
  const shadow = new THREE.Mesh(new THREE.PlaneGeometry(3.2, 0.8), new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, depthWrite: false }));
  shadow.position.set(0, -2.05, -0.4);
  bob.add(shadow);

  // ---------- Ingredients ----------
  const { G, M } = buildIngredients();
  const particles = [];
  function makePiece(type, product) {
    if (type === "mini") {
      const f = flavours[particles.filter((p) => p.type === "mini" && p.state === "in").length % Math.max(1, flavours.length)];
      const m = new THREE.Mesh(pouchGeo, materialsFor(f));
      m.scale.setScalar(0.3);
      return { mesh: m, base: 1 };
    }
    if (type === "chili") {
      const g = new THREE.Group();
      g.add(new THREE.Mesh(G.chili, M.chili));
      g.add(new THREE.Mesh(G.stem, M.stem));
      return { mesh: g, base: rand(0.8, 1.15) };
    }
    const geo = { honey: G.drop, soy: G.drop }[type] || G[type];
    const mat = M[type];
    return { mesh: new THREE.Mesh(geo, mat), base: type === "jerky" ? rand(0.7, 1.05) : rand(0.85, 1.2) };
  }
  function homeFor(i, n) {
    const a = (i / n) * TAU + rand(-0.25, 0.25);
    const rx = rand(1.9, 2.9);
    const ry = rand(1.7, 2.5);
    const v = new THREE.Vector3(Math.cos(a) * rx, Math.sin(a) * ry, rand(-1.4, 1.1));
    // Keep the label in front clear.
    if (Math.abs(v.x) < 1.4 && Math.abs(v.y) < 1.7 && v.z > -0.5) v.z = -1.2;
    return v;
  }

  // Dust and a shockwave ring for the explosion
  const DUST = 140;
  const dustGeo = new THREE.BufferGeometry();
  const dustPos = new Float32Array(DUST * 3);
  const dustVel = new Float32Array(DUST * 3);
  dustGeo.setAttribute("position", new THREE.BufferAttribute(dustPos, 3));
  const dustMat = new THREE.PointsMaterial({ color: "#ff7a33", size: 0.06, transparent: true, opacity: 0, blending: THREE.AdditiveBlending, depthWrite: false });
  const dust = new THREE.Points(dustGeo, dustMat);
  root.add(dust);
  let dustAge = 99;
  const ring = new THREE.Mesh(new THREE.RingGeometry(0.9, 1, 64), new THREE.MeshBasicMaterial({ color: "#ff7a33", transparent: true, opacity: 0, side: THREE.DoubleSide, depthWrite: false, blending: THREE.AdditiveBlending }));
  root.add(ring);
  let ringAge = 99;

  function shockwave() {
    if (reduce) return;
    ringAge = 0;
    dustAge = 0;
    for (let i = 0; i < DUST; i++) {
      const d = new THREE.Vector3(rand(-1, 1), rand(-1, 1), rand(-1, 1)).normalize().multiplyScalar(rand(2, 9));
      dustPos.set([rand(-0.3, 0.3), rand(-0.3, 0.3), rand(-0.2, 0.2)], i * 3);
      dustVel.set([d.x, d.y, d.z], i * 3);
    }
    dustGeo.attributes.position.needsUpdate = true;
  }

  function burst(product, { strength = 1 } = {}) {
    // Old pieces fly off and vanish.
    for (const p of particles) {
      if (p.state !== "in") continue;
      p.state = "out";
      const away = p.pos.clone().normalize();
      p.vel.add(away.multiplyScalar(rand(6, 12)));
      p.target = 0;
    }
    const recipe = RECIPES[slugOf(product)] || RECIPES.any;
    const list = [];
    for (const [type, n] of recipe) for (let i = 0; i < n; i++) list.push(type);
    list.sort(() => Math.random() - 0.5);
    list.forEach((type, i) => {
      const piece = makePiece(type, product);
      const home = homeFor(i, list.length);
      const dir = home.clone().normalize();
      const p = {
        type,
        mesh: piece.mesh,
        base: piece.base,
        pos: reduce ? home.clone() : new THREE.Vector3(rand(-0.3, 0.3), rand(-0.4, 0.4), rand(0, 0.3)),
        vel: reduce ? new THREE.Vector3() : dir.multiplyScalar(rand(7, 13) * strength).add(new THREE.Vector3(rand(-2, 2), rand(-1, 3), rand(-1, 3))),
        rot: new THREE.Vector3(rand(-6, 6), rand(-6, 6), rand(-6, 6)),
        home,
        phase: rand(0, TAU),
        age: 0,
        scale: reduce ? 1 : 0.2,
        target: 1,
        state: "in",
      };
      p.mesh.rotation.set(rand(0, TAU), rand(0, TAU), rand(0, TAU));
      root.add(p.mesh);
      particles.push(p);
    });
    shockwave();
  }

  // ---------- Tweens ----------
  const tweens = [];
  const tween = (duration, fn, done) => tweens.push({ t: 0, duration, fn, done });

  let current = null;
  let spinOffset = 0;
  let squash = 1;
  function setProduct(product, { mode = "burst" } = {}) {
    const first = !current;
    const sameGeo = current && Boolean(current.box) === Boolean(product.box);
    current = product;
    const apply = () => {
      pack.geometry = product.box ? boxGeo : pouchGeo;
      pack.material = materialsFor(product);
    };
    if (first || reduce || mode === "instant") {
      apply();
      if (mode !== "none") burst(product);
      return;
    }
    // Spin one full turn; swap the pack while it is edge-on, explode at the same moment.
    let swapped = false;
    const start = spinOffset;
    tween(
      0.9,
      (t) => {
        const e = ease.inOutCubic(t);
        spinOffset = start + e * TAU;
        squash = 1 + Math.sin(Math.min(1, t * 1.6) * Math.PI) * 0.08;
        if (!swapped && t >= 0.45) {
          swapped = true;
          apply();
          if (mode === "burst") burst(product);
          else if (!sameGeo) burst(product, { strength: 0.6 });
        }
      },
      () => {
        spinOffset = start + TAU;
        squash = 1;
      }
    );
  }
  function pulse() {
    if (reduce) return;
    tween(
      0.55,
      (t) => {
        squash = 1 + Math.sin(t * Math.PI) * 0.12;
        hop = Math.sin(t * Math.PI) * 0.35;
      },
      () => {
        squash = 1;
        hop = 0;
      }
    );
    shockwave();
  }
  let hop = 0;
  let dropY = 0;
  function intro() {
    if (reduce || !current) return;
    dropY = 6;
    tween(
      1.15,
      (t) => {
        dropY = 6 * (1 - ease.outBounce(t));
      },
      () => (dropY = 0)
    );
    setTimeout(() => burst(current), 650);
  }

  // ---------- Pointer: tilt, drag to spin, tap to explode, repel ----------
  const pointer = new THREE.Vector2(0, 0);
  const pointerSmooth = new THREE.Vector2(0, 0);
  let pointerActive = false;
  window.addEventListener(
    "pointermove",
    (e) => {
      pointer.set((e.clientX / innerWidth) * 2 - 1, -(e.clientY / innerHeight) * 2 + 1);
      pointerActive = true;
    },
    { passive: true }
  );
  document.addEventListener("pointerleave", () => (pointerActive = false));

  let dragRot = 0;
  let dragVel = 0;
  let drag = null;
  const raycaster = new THREE.Raycaster();
  function bindDrag(el) {
    el.addEventListener("pointerdown", (e) => {
      drag = { x: e.clientX, y: e.clientY, t: performance.now(), moved: 0, id: e.pointerId, el };
    });
  }
  window.addEventListener("pointermove", (e) => {
    if (!drag || e.pointerId !== drag.id) return;
    const dx = e.clientX - drag.x;
    drag.moved += Math.abs(dx) + Math.abs(e.clientY - drag.y);
    drag.x = e.clientX;
    drag.y = e.clientY;
    if (drag.moved > 6) {
      dragRot += dx * 0.012;
      dragVel = dx * 0.012 * 60;
    }
  });
  window.addEventListener("pointerup", (e) => {
    if (!drag || e.pointerId !== drag.id) return;
    const tap = drag.moved < 8 && performance.now() - drag.t < 400;
    drag = null;
    if (!tap || !current) return;
    raycaster.setFromCamera(new THREE.Vector2((e.clientX / innerWidth) * 2 - 1, -(e.clientY / innerHeight) * 2 + 1), camera);
    if (raycaster.intersectObject(pack, false).length) {
      burst(current, { strength: 1.1 });
      pulse();
    }
  });

  // ---------- Anchors: where on the page the pack should be ----------
  let anchors = [];
  function setAnchors(list) {
    anchors = list;
  }
  const visH = () => 2 * camera.position.z * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
  const targetPos = new THREE.Vector3();
  let targetScale = 0;
  let presence = 0;
  function readAnchors() {
    let best = null;
    let bestW = 0;
    let fill = 0.8;
    const vh = innerHeight;
    for (const el of anchors) {
      if (!el.isConnected || el.offsetParent === null) continue;
      const r = el.getBoundingClientRect();
      if (!r.height) continue;
      const overlap = Math.max(0, Math.min(r.bottom, vh) - Math.max(r.top, 0));
      const w = overlap / Math.min(r.height, vh);
      if (w > bestW) {
        bestW = w;
        best = r;
        fill = Number(el.dataset.fill || 0.8);
      }
    }
    presence = THREE.MathUtils.smoothstep(bestW, 0.05, 0.45);
    if (!best) return;
    const H = visH();
    const Wd = H * camera.aspect;
    const cx = best.left + best.width / 2;
    const cy = best.top + best.height / 2;
    targetPos.set((cx / innerWidth - 0.5) * Wd, -(cy / vh - 0.5) * H, 0);
    const hWorld = Math.min((best.height / vh) * H, ((best.width / innerWidth) * Wd) * 1.2);
    targetScale = (hWorld * fill) / 5.2;
  }

  // ---------- Resize ----------
  function resize() {
    const w = innerWidth;
    const h = innerHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  window.addEventListener("resize", resize);
  resize();

  // ---------- Loop ----------
  const clock = new THREE.Clock();
  let accent = new THREE.Color("#ff5a1f");
  const accentTarget = new THREE.Color("#ff5a1f");
  const plane = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
  const hit = new THREE.Vector3();
  let wasDrawn = true;
  let running = true;
  document.addEventListener("visibilitychange", () => {
    running = !document.hidden;
    if (running) {
      clock.getDelta();
      requestAnimationFrame(loop);
    }
  });

  function loop() {
    if (!running) return;
    requestAnimationFrame(loop);
    const dt = Math.min(clock.getDelta(), 1 / 20);
    const time = clock.elapsedTime;
    readAnchors();

    // Tweens
    for (let i = tweens.length - 1; i >= 0; i--) {
      const tw = tweens[i];
      tw.t += dt / tw.duration;
      tw.fn(Math.min(1, tw.t));
      if (tw.t >= 1) {
        tweens.splice(i, 1);
        tw.done?.();
      }
    }

    // Follow the anchor
    const k = 1 - Math.exp(-dt * 7);
    root.position.lerp(targetPos, k);
    const s = THREE.MathUtils.lerp(root.scale.x, targetScale * presence, k);
    root.scale.setScalar(Math.max(0.0001, s));

    // Float, tilt, spin
    pointerSmooth.lerp(pointerActive ? pointer : new THREE.Vector2(0, 0), 1 - Math.exp(-dt * 4));
    bob.position.y = (reduce ? 0 : Math.sin(time * 1.2) * 0.12) + dropY + hop;
    bob.rotation.z = reduce ? 0 : Math.sin(time * 0.8) * 0.03;
    if (!drag) {
      dragRot += dragVel * dt;
      dragVel *= Math.exp(-dt * 2.5);
      // Ease back towards facing front when nobody is spinning it.
      if (Math.abs(dragVel) < 0.3) dragRot += (Math.round(dragRot / TAU) * TAU - dragRot) * (1 - Math.exp(-dt * 1.5));
    }
    const idle = reduce ? 0 : Math.sin(time * 0.5) * 0.18;
    spin.rotation.y = spinOffset + dragRot + idle + pointerSmooth.x * 0.45;
    spin.rotation.x = -pointerSmooth.y * 0.22;
    spin.scale.set(1 / Math.sqrt(squash), squash, 1 / Math.sqrt(squash));

    // Accent light
    accent.lerp(accentTarget, 1 - Math.exp(-dt * 3));
    rim.color.copy(accent);
    dustMat.color.copy(accent);
    ring.material.color.copy(accent);

    // Pointer position in the pack's space (for pushing pieces away)
    raycaster.setFromCamera(pointerSmooth, camera);
    plane.constant = -root.position.z;
    const hasHit = pointerActive && raycaster.ray.intersectPlane(plane, hit);
    if (hasHit) root.worldToLocal(hit);

    // Pieces
    for (let i = particles.length - 1; i >= 0; i--) {
      const p = particles[i];
      p.age += dt;
      if (p.state === "in") {
        const settle = clamp((p.age - 0.3) / 0.9, 0, 1);
        const home = p.home.clone().add(new THREE.Vector3(Math.sin(time * 0.6 + p.phase) * 0.14, Math.cos(time * 0.75 + p.phase) * 0.18, Math.sin(time * 0.5 + p.phase * 2) * 0.1));
        const acc = home.sub(p.pos).multiplyScalar(7 * settle);
        acc.addScaledVector(p.vel, -(1.2 + 2.6 * settle));
        if (hasHit) {
          const dx = p.pos.x - hit.x;
          const dy = p.pos.y - hit.y;
          const d = Math.hypot(dx, dy);
          if (d < 1.3 && d > 0.0001) {
            const f = (1 - d / 1.3) * 26;
            acc.x += (dx / d) * f;
            acc.y += (dy / d) * f;
          }
        }
        p.vel.addScaledVector(acc, dt);
      } else {
        p.vel.multiplyScalar(Math.exp(-dt * 1.2));
      }
      p.pos.addScaledVector(p.vel, dt);
      p.scale += (p.target - p.scale) * (1 - Math.exp(-dt * (p.state === "out" ? 5 : 9)));
      p.rot.multiplyScalar(Math.exp(-dt * 0.8));
      const minSpin = 0.35;
      p.mesh.rotation.x += (p.rot.x + minSpin) * dt;
      p.mesh.rotation.y += (p.rot.y + minSpin * 0.7) * dt;
      p.mesh.rotation.z += p.rot.z * dt;
      p.mesh.position.copy(p.pos);
      const sc = Math.max(0.0001, p.scale * p.base * (p.type === "mini" ? 0.3 : 1));
      p.mesh.scale.setScalar(sc);
      if (p.state === "out" && p.scale < 0.02) {
        root.remove(p.mesh);
        particles.splice(i, 1);
      }
    }

    // Dust and ring
    if (dustAge < 2) {
      dustAge += dt;
      for (let i = 0; i < DUST; i++) {
        dustVel[i * 3] *= Math.exp(-dt * 2.2);
        dustVel[i * 3 + 1] = dustVel[i * 3 + 1] * Math.exp(-dt * 2.2) - dt * 0.8;
        dustVel[i * 3 + 2] *= Math.exp(-dt * 2.2);
        dustPos[i * 3] += dustVel[i * 3] * dt;
        dustPos[i * 3 + 1] += dustVel[i * 3 + 1] * dt;
        dustPos[i * 3 + 2] += dustVel[i * 3 + 2] * dt;
      }
      dustGeo.attributes.position.needsUpdate = true;
      dustMat.opacity = Math.max(0, 1 - dustAge / 1.4);
    }
    if (ringAge < 1) {
      ringAge += dt;
      const e = ease.outCubic(Math.min(1, ringAge / 0.7));
      ring.scale.setScalar(0.5 + e * 3.6);
      ring.material.opacity = Math.max(0, 0.7 * (1 - e));
      ring.lookAt(camera.position);
    }

    // Only draw while the pack is on screen.
    if (root.scale.x > 0.002) {
      renderer.render(scene, camera);
      wasDrawn = true;
    } else if (wasDrawn) {
      renderer.clear();
      wasDrawn = false;
    }
  }

  // Wait for the logo and fonts so the labels print correctly, then start.
  const ready = Promise.all([
    new Promise((res) => {
      const img = new Image();
      img.onload = () => {
        logo = img;
        res();
      };
      img.onerror = res;
      img.src = logoUrl;
    }),
    document.fonts ? Promise.all([document.fonts.load("400 100px Anton"), document.fonts.load("800 40px Manrope")]).catch(() => {}) : Promise.resolve(),
  ]).then(() => {
    requestAnimationFrame(loop);
  });

  return {
    ready,
    setProduct,
    setAnchors,
    bindDrag,
    pulse,
    intro,
    burst: () => current && burst(current),
    setAccent: (hex) => accentTarget.set(hex),
  };
}
