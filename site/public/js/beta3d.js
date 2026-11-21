// The 3D pack for the beta page: one studio-lit pouch on black.
// The page tells it an angle (from the scroll position); every half turn shows
// the next product's label. It only renders while something is moving, so it
// costs nothing when the page is still. It can also render clean product shots
// for the "Choose" section.
import * as THREE from "three";
import { RoomEnvironment } from "/vendor/three/RoomEnvironment.js";

// ---------- Labels painted on canvas (in the page's own fonts) ----------
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
function paintPouch(p, meat, logo) {
  const W = 1024;
  const H = 1536;
  const c = makeCanvas(W, H);
  const ctx = c.getContext("2d");
  const g = ctx.createLinearGradient(0, 0, 0, H);
  g.addColorStop(0, meat?.pouch?.top || "#2a2624");
  g.addColorStop(1, meat?.pouch?.bottom || "#0d0b0a");
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, W, H);
  // Top seal with crimp lines
  ctx.fillStyle = meat?.pouch?.cap || "#0a0908";
  ctx.fillRect(0, 0, W, H * 0.1);
  ctx.fillStyle = "rgba(255,255,255,.05)";
  for (let x = 0; x < W; x += 10) ctx.fillRect(x, 0, 4, H * 0.065);
  ctx.strokeStyle = "rgba(255,255,255,.1)";
  ctx.lineWidth = 5;
  ctx.beginPath();
  ctx.moveTo(W * 0.04, H * 0.085);
  ctx.lineTo(W * 0.96, H * 0.085);
  ctx.stroke();
  if (logo) ctx.drawImage(logo, W * 0.17, H * 0.17, W * 0.66, W * 0.66);
  ctx.textAlign = "center";
  ctx.fillStyle = "#efe8e1";
  ctx.letterSpacing = `${W * 0.008}px`;
  const title = `PREMIUM ${(p.meat || "beef").toUpperCase()} JERKY`;
  fitFont(ctx, title, W * 0.84, W * 0.052, (s) => `800 ${s}px Manrope, sans-serif`);
  ctx.fillText(title, W / 2, H * 0.7);
  ctx.fillStyle = p.band || "#b8261c";
  ctx.fillRect(0, H * 0.725, W, H * 0.12);
  ctx.fillStyle = p.bandText || "#fff";
  ctx.letterSpacing = `${W * 0.006}px`;
  const name = p.name.toUpperCase();
  const s = fitFont(ctx, name, W * 0.86, W * 0.12, (z) => `400 ${z}px Anton, Impact, sans-serif`);
  ctx.fillText(name, W / 2, H * 0.785 + s * 0.36);
  ctx.fillStyle = "rgba(255,255,255,.55)";
  ctx.font = `700 ${W * 0.034}px Manrope, sans-serif`;
  ctx.fillText(`NET WT. ${p.grams} G`, W / 2, H * 0.92);
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
  if (logo) ctx.drawImage(logo, W * 0.25, H * 0.07, W * 0.5, W * 0.5);
  ctx.fillStyle = "#efe8e1";
  ctx.textAlign = "center";
  ctx.letterSpacing = `${W * 0.006}px`;
  const name = box.name.toUpperCase();
  const s = fitFont(ctx, name, W * 0.86, W * 0.11, (z) => `400 ${z}px Anton, Impact, sans-serif`);
  ctx.fillText(name, W / 2, H * 0.66 + s * 0.3);
  const w = W / stripes.length;
  stripes.forEach((col, i) => {
    ctx.fillStyle = col;
    ctx.fillRect(i * w, H * 0.77, w + 1, H * 0.1);
  });
  ctx.fillStyle = "rgba(255,255,255,.55)";
  ctx.font = `700 ${W * 0.034}px Manrope, sans-serif`;
  ctx.fillText(`${box.grams} G`, W / 2, H * 0.95);
  return c;
}

// ---------- Geometry ----------
// A stand-up pouch: a subdivided box puffed in the middle, flat at the top seal,
// pinched at the side seams, with a faint ripple in the foil.
function pouchGeometry() {
  const geo = new THREE.BoxGeometry(2, 3, 0.7, 32, 48, 6);
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
    if (Math.abs(v.z) > 0.34) z += sign * 0.008 * Math.sin(v.x * 6 + v.y * 2.1) * depth;
    if (t > seal) v.x *= 1.012;
    pos.setXYZ(i, v.x, v.y, z);
  }
  geo.computeVertexNormals();
  return geo;
}

// ---------- The stage ----------
export function createStage({ canvas, items, meats = [], logoUrl, stripes = [] }) {
  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: "high-performance" });
  } catch {
    return null;
  }
  if (!renderer.getContext()) return null;
  const small = window.matchMedia("(max-width: 899px)").matches;
  const dpr = Math.min(window.devicePixelRatio || 1, small ? 1.75 : 1.5);
  renderer.setPixelRatio(dpr);
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1;
  renderer.setClearColor(0x000000, 0);

  const scene = new THREE.Scene();
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(renderer), 0.03).texture;
  const camera = new THREE.PerspectiveCamera(24, 1, 0.1, 100);

  // Studio light: a soft key from the front left, two cool rims from behind.
  const key = new THREE.DirectionalLight("#fff6ee", 2.1);
  key.position.set(-3, 3.5, 6);
  scene.add(key);
  const rimL = new THREE.DirectionalLight("#ffffff", 2.4);
  rimL.position.set(-5, 2, -4);
  scene.add(rimL);
  const rimR = new THREE.DirectionalLight("#ffffff", 1.8);
  rimR.position.set(5, 1, -4);
  scene.add(rimR);

  const holder = new THREE.Group();
  scene.add(holder);
  const pack = new THREE.Group();
  holder.add(pack);


  const maxAniso = renderer.capabilities.getMaxAnisotropy();
  const tex = (cnv) => {
    const t = new THREE.CanvasTexture(cnv);
    t.colorSpace = THREE.SRGBColorSpace;
    t.anisotropy = maxAniso;
    return t;
  };
  const foil = (opts) => new THREE.MeshPhysicalMaterial({ roughness: 0.32, metalness: 0.3, clearcoat: 0.8, clearcoatRoughness: 0.18, envMapIntensity: 0.55, ...opts });
  const pouchGeo = pouchGeometry();
  const boxGeo = new THREE.BoxGeometry(2.4, 2.4, 1.1, 1, 1, 1);
  let logo = null;
  const meshCache = new Map();
  // Both big faces carry the label, so every half turn shows a readable front.
  function meshFor(p) {
    if (meshCache.has(p.id)) return meshCache.get(p.id);
    const meat = meats.find((m) => m.id === p.meat);
    let mesh;
    if (p.box) {
      const face = foil({ map: tex(paintBox(p, stripes, logo)), metalness: 0.12 });
      const side = foil({ color: "#1d1917", metalness: 0.12 });
      mesh = new THREE.Mesh(boxGeo, [side, side, side, side, face, face]);
    } else {
      const face = foil({ map: tex(paintPouch(p, meat, logo)) });
      const side = foil({ color: meat?.pouch?.bottom || "#151210" });
      mesh = new THREE.Mesh(pouchGeo, [side, side, side, side, face, face]);
    }
    meshCache.set(p.id, mesh);
    return mesh;
  }
  let shown = null;
  function show(p) {
    if (shown === p) return;
    if (shown) pack.remove(meshFor(shown));
    pack.add(meshFor(p));
    shown = p;
  }

  // ---------- Framing ----------
  let frame = { fill: 0.56, lift: 0.05 };
  function fitCamera(aspect, fill, lift) {
    camera.aspect = aspect;
    // Distance so the 3-unit tall pack fills `fill` of the view height.
    const h = 3 / fill;
    camera.position.set(0, 0, h / 2 / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)));
    camera.lookAt(0, 0, 0);
    holder.position.y = lift * h;
    camera.updateProjectionMatrix();
  }
  function resize() {
    const w = canvas.clientWidth || innerWidth;
    const h = canvas.clientHeight || innerHeight;
    renderer.setSize(w, h, false);
    const narrow = w < 900;
    // Room above for the headline and below for the caption.
    frame = narrow ? { fill: 0.38, lift: 0.09 } : { fill: 0.47, lift: 0.05 };
    fitCamera(w / h, frame.fill, frame.lift);
    invalidate();
  }

  // ---------- State and loop ----------
  const REST = -0.32;
  let angle = -0.6; // shown
  let target = -0.6; // asked for
  let scale = 1;
  let scaleTarget = 1;
  let tilt = new THREE.Vector2();
  let tiltTarget = new THREE.Vector2();
  let damping = 9;
  let raf = 0;
  let started = false;
  const clock = new THREE.Clock();
  function invalidate() {
    if (!started || raf) return;
    clock.getDelta();
    raf = requestAnimationFrame(tick);
  }
  function indexFor(a) {
    return Math.max(0, Math.min(items.length - 1, Math.round(a / Math.PI)));
  }
  function tick() {
    raf = 0;
    const dt = Math.min(clock.getDelta(), 1 / 20);
    const k = 1 - Math.exp(-dt * damping);
    angle += (target - angle) * k;
    scale += (scaleTarget - scale) * k;
    pack.scale.setScalar(scale);
    tilt.lerp(tiltTarget, 1 - Math.exp(-dt * 6));
    // Rest at a three-quarter angle so the pouch reads as an object, not a picture.
    const yaw = angle + REST + tilt.x * 0.22;
    show(items[indexFor(yaw)]);
    pack.rotation.y = yaw;
    pack.rotation.x = -tilt.y * 0.1;
    // A slight lean while it turns, like it is being handled.
    pack.rotation.z = Math.sin(angle * 2) * 0.025;
    renderer.render(scene, camera);
    const moving = Math.abs(target - angle) > 0.0005 || Math.abs(scaleTarget - scale) > 0.0005 || tilt.distanceTo(tiltTarget) > 0.0005;
    if (moving) raf = requestAnimationFrame(tick);
  }

  window.addEventListener("resize", resize);
  if (window.matchMedia("(hover: hover) and (pointer: fine)").matches) {
    window.addEventListener(
      "pointermove",
      (e) => {
        tiltTarget.set((e.clientX / innerWidth) * 2 - 1, -(e.clientY / innerHeight) * 2 + 1);
        invalidate();
      },
      { passive: true }
    );
  }

  // ---------- Product shots for the "Choose" section ----------
  function snapshots(list, { width = 720, height = 900 } = {}) {
    const out = {};
    const prev = { angle: pack.rotation.y, x: pack.rotation.x, z: pack.rotation.z };
    renderer.setPixelRatio(1);
    renderer.setSize(width, height, false);
    fitCamera(width / height, 0.74, 0);
    pack.scale.setScalar(1);
    for (const p of list) {
      show(p);
      pack.rotation.set(0.04, -0.42, 0.012);
      renderer.render(scene, camera);
      out[p.id] = canvas.toDataURL("image/webp", 0.9);
    }
    pack.scale.setScalar(scale);
    renderer.setPixelRatio(dpr);
    pack.rotation.set(prev.x, prev.angle, prev.z);
    resize();
    return out;
  }

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
    show(items[0]);
    resize();
  });

  return {
    ready,
    snapshots,
    start() {
      started = true;
      angle = target;
      invalidate();
    },
    setAngle(a, { instant = false, size = 1 } = {}) {
      target = a;
      scaleTarget = size;
      if (instant) {
        angle = a;
        scale = size;
      }
      invalidate();
    },
    setDamping(d) {
      damping = d;
    },
  };
}
