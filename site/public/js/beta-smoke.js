// Hero smoke: a WebGL shader draws drifting smoke over the jerky photo. Where the
// pointer (or a finger) passes, the smoke clears and the meat shows through; the
// cleared trail slowly fills back in. Falls back to the plain photo underneath
// when WebGL is missing, and draws a single still frame for reduced motion.

const VERT = `
attribute vec2 p;
varying vec2 v;
void main() { v = p * 0.5 + 0.5; gl_Position = vec4(p, 0.0, 1.0); }`;

const FRAG = `
precision mediump float;
varying vec2 v;
uniform sampler2D u_img;
uniform sampler2D u_trail;
uniform vec2 u_res;
uniform vec2 u_imgRes;
uniform float u_t;

float hash(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }
float noise(vec2 p) {
  vec2 i = floor(p), f = fract(p);
  vec2 u = f * f * (3.0 - 2.0 * f);
  return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x), mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);
}
float fbm(vec2 p) {
  float s = 0.0, a = 0.5;
  for (int i = 0; i < 5; i++) { s += a * noise(p); p = p * 2.03 + vec2(1.7, 9.2); a *= 0.5; }
  return s;
}

void main() {
  vec2 uv = v;
  float ar = u_res.x / u_res.y;
  vec2 q = vec2(uv.x * ar, uv.y);
  float t = u_t * 0.05;

  // Smoke: domain-warped noise that rises and curls.
  vec2 w = vec2(fbm(q * 1.4 + vec2(0.0, -t * 1.6)), fbm(q * 1.4 + vec2(5.2, -t * 1.3)));
  float d = fbm(q * 2.1 + w * 2.0 + vec2(t * 0.3, -t * 2.2));
  d = smoothstep(0.22, 0.82, d);
  d *= mix(1.25, 0.7, uv.y);

  float clear = texture2D(u_trail, vec2(uv.x, 1.0 - uv.y)).r;
  d *= 1.0 - clear * 0.95;

  // Photo, cropped to cover, shimmering a little in the heat.
  vec2 s = u_res / u_imgRes;
  float sc = max(s.x, s.y);
  vec2 iuv = (uv - 0.5) * (u_res / (u_imgRes * sc)) + 0.5;
  iuv.y = 1.0 - iuv.y;
  iuv += (w - 0.5) * 0.014 * (1.0 - clear);
  vec3 img = texture2D(u_img, iuv).rgb;
  img *= 0.42 + 0.58 * clear;

  vec3 smoke = mix(vec3(0.07, 0.06, 0.055), vec3(0.48, 0.44, 0.4), d);
  vec3 col = mix(img, smoke, clamp(d * 0.92, 0.0, 1.0));

  // A faint warm ember light from below, and a vignette.
  col += vec3(0.35, 0.09, 0.03) * smoothstep(0.5, 0.0, uv.y) * (0.25 + 0.2 * w.x);
  float vig = smoothstep(1.25, 0.35, length((uv - 0.5) * vec2(ar * 0.75, 1.0)));
  col *= mix(0.55, 1.0, vig);
  gl_FragColor = vec4(col, 1.0);
}`;

export function initSmoke(canvas, { reduce = false, onReady } = {}) {
  const gl = canvas.getContext("webgl", { antialias: false, alpha: false, preserveDrawingBuffer: false });
  if (!gl) return null;

  const shader = (type, src) => {
    const s = gl.createShader(type);
    gl.shaderSource(s, src);
    gl.compileShader(s);
    return gl.getShaderParameter(s, gl.COMPILE_STATUS) ? s : null;
  };
  const vs = shader(gl.VERTEX_SHADER, VERT);
  const fs = shader(gl.FRAGMENT_SHADER, FRAG);
  if (!vs || !fs) return null;
  const prog = gl.createProgram();
  gl.attachShader(prog, vs);
  gl.attachShader(prog, fs);
  gl.linkProgram(prog);
  if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return null;
  gl.useProgram(prog);

  const buf = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buf);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
  const loc = gl.getAttribLocation(prog, "p");
  gl.enableVertexAttribArray(loc);
  gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

  const U = (n) => gl.getUniformLocation(prog, n);
  const uRes = U("u_res");
  const uImgRes = U("u_imgRes");
  const uT = U("u_t");
  gl.uniform1i(U("u_img"), 0);
  gl.uniform1i(U("u_trail"), 1);

  const makeTex = (unit) => {
    const tex = gl.createTexture();
    gl.activeTexture(gl.TEXTURE0 + unit);
    gl.bindTexture(gl.TEXTURE_2D, tex);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    return tex;
  };
  const imgTex = makeTex(0);
  const trailTex = makeTex(1);

  // The cleared trail is painted on a small 2D canvas and uploaded every frame.
  const trail = document.createElement("canvas");
  trail.width = trail.height = 128;
  const tc = trail.getContext("2d");
  tc.fillStyle = "#000";
  tc.fillRect(0, 0, 128, 128);

  let w = 0;
  let h = 0;
  const resize = () => {
    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    const scale = Math.min(1, 1600 / (canvas.clientWidth * dpr));
    w = Math.max(1, Math.round(canvas.clientWidth * dpr * scale));
    h = Math.max(1, Math.round(canvas.clientHeight * dpr * scale));
    canvas.width = w;
    canvas.height = h;
    gl.viewport(0, 0, w, h);
    gl.uniform2f(uRes, w, h);
  };

  // Pointer → trail. Positions are 0..1 across the canvas.
  const pointer = { x: 0.5, y: 0.5, px: 0.5, py: 0.5, active: false, last: 0 };
  const onMove = (e) => {
    const r = canvas.getBoundingClientRect();
    pointer.x = (e.clientX - r.left) / r.width;
    pointer.y = (e.clientY - r.top) / r.height;
    if (!pointer.active) {
      pointer.px = pointer.x;
      pointer.py = pointer.y;
    }
    pointer.active = true;
    pointer.last = performance.now();
  };
  const host = canvas.closest("section") || canvas;
  host.addEventListener("pointermove", onMove, { passive: true });
  host.addEventListener("pointerdown", onMove, { passive: true });
  host.addEventListener("pointerleave", () => (pointer.active = false));

  const dab = (x, y, r, a) => {
    const g = tc.createRadialGradient(x * 128, y * 128, 0, x * 128, y * 128, r);
    g.addColorStop(0, `rgba(255,255,255,${a})`);
    g.addColorStop(1, "rgba(255,255,255,0)");
    tc.fillStyle = g;
    tc.beginPath();
    tc.arc(x * 128, y * 128, r, 0, Math.PI * 2);
    tc.fill();
  };

  let running = false;
  let visible = true;
  let raf = 0;
  const t0 = performance.now();
  const frame = (now) => {
    raf = 0;
    const t = (now - t0) / 1000;
    // Let the smoke creep back in.
    tc.fillStyle = "rgba(0,0,0,0.028)";
    tc.fillRect(0, 0, 128, 128);
    const idle = !pointer.active || now - pointer.last > 2200;
    if (idle) {
      // Nobody touching it: a slow draft opens a gap now and then so the meat peeks through.
      const x = 0.62 + Math.sin(t * 0.23) * 0.22;
      const y = 0.45 + Math.sin(t * 0.31 + 1.3) * 0.2;
      dab(x, y, 30, 0.05);
    } else {
      // Paint along the path since the last frame so fast moves leave no gaps.
      const steps = Math.ceil(Math.hypot(pointer.x - pointer.px, pointer.y - pointer.py) * 40) + 1;
      for (let i = 1; i <= steps; i++) {
        dab(pointer.px + ((pointer.x - pointer.px) * i) / steps, pointer.py + ((pointer.y - pointer.py) * i) / steps, 20, 0.22);
      }
    }
    pointer.px = pointer.x;
    pointer.py = pointer.y;

    gl.activeTexture(gl.TEXTURE1);
    gl.bindTexture(gl.TEXTURE_2D, trailTex);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, trail);
    gl.uniform1f(uT, t + 30);
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
    if (running && visible && !reduce) raf = requestAnimationFrame(frame);
  };
  const play = () => {
    if (!raf && running && visible) raf = requestAnimationFrame(frame);
  };

  const img = new Image();
  img.decoding = "async";
  img.onload = () => {
    gl.activeTexture(gl.TEXTURE0);
    gl.bindTexture(gl.TEXTURE_2D, imgTex);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
    gl.uniform2f(uImgRes, img.naturalWidth, img.naturalHeight);
    resize();
    running = true;
    if (reduce) dab(0.62, 0.45, 40, 0.6);
    requestAnimationFrame((now) => {
      frame(now);
      canvas.classList.add("is-on");
      onReady?.();
    });
  };
  img.src = canvas.dataset.src;

  new ResizeObserver(() => {
    if (!running) return;
    resize();
    if (reduce) requestAnimationFrame(frame);
  }).observe(canvas);
  new IntersectionObserver(([e]) => {
    visible = e.isIntersecting;
    play();
  }).observe(canvas);
  document.addEventListener("visibilitychange", () => {
    visible = !document.hidden;
    play();
  });
  return { canvas };
}
