// WebGL2 fallback: the same ridge, sky and procession. Motion is analytic in the vertex shader
// (no compute), so torches follow their lanes exactly and lean toward the pointer without memory.
import { WORLD, PALETTE, GRID, torchCounts, bakeHeights } from 'rs/scene-core';

const v3 = (a) => `vec3(${a.map((x) => x.toFixed(3)).join(', ')})`;

const COMMON = `#version 300 es
precision highp float;
precision highp int;
uniform mat4 uViewProj;
uniform vec4 uCamPos, uCamRight, uCamUp, uCamFwd, uPointer, uEnv, uExtra, uViewport;
uniform highp sampler2D uHeights;
const float X0 = ${WORLD.X0.toFixed(1)}, Z0 = ${WORLD.Z0.toFixed(1)}, SX = ${WORLD.SX.toFixed(1)}, SZ = ${WORLD.SZ.toFixed(1)};
const vec2 SUMMIT_XZ = vec2(${WORLD.SUMMIT[0].toFixed(1)}, ${WORLD.SUMMIT[2].toFixed(1)});
const vec3 EMBER = ${v3(PALETTE.ember)};
const vec3 SAFFRON = ${v3(PALETTE.saffron)};
int gridN() { return int(uExtra.z); }
float hAt(int ix, int iz) { int n = gridN(); return texelFetch(uHeights, ivec2(clamp(ix, 0, n - 1), clamp(iz, 0, n - 1)), 0).r; }
float heightAt(vec2 xz) {
  float n = float(gridN() - 1);
  vec2 g = vec2((xz.x - X0) / SX, (xz.y - Z0) / SZ) * n;
  vec2 i = floor(g); vec2 f = clamp(g - i, 0.0, 1.0);
  int ix = int(i.x), iz = int(i.y);
  return mix(mix(hAt(ix, iz), hAt(ix + 1, iz), f.x), mix(hAt(ix, iz + 1), hAt(ix + 1, iz + 1), f.x), f.y);
}
float laneStart(int lane) { return lane == 0 ? -9.0 : (lane == 1 ? 7.0 : 23.0); }
float roadX(float s) { return SUMMIT_XZ.x + 1.5 * (1.0 - s) + mix(10.5, 0.5, pow(s, 0.9)) * sin(s * 25.13 + 0.6); }
float laneX(int lane, float s) { return mix(laneStart(lane), roadX(s), smoothstep(0.0, 0.3, s)); }
float laneZ(float s) { return mix(10.0, SUMMIT_XZ.y + 1.5, s); }
vec3 hourMix(vec3 a, vec3 b, vec3 c, float h) { return h < 0.5 ? mix(a, b, smoothstep(0.0, 0.5, h)) : mix(b, c, smoothstep(0.5, 1.0, h)); }
vec3 skyColor(vec3 dir, float h) {
  vec3 top = hourMix(${v3(PALETTE.duskTop)}, ${v3(PALETTE.nightTop)}, ${v3(PALETTE.dawnTop)}, h);
  vec3 hor = hourMix(${v3(PALETTE.duskHorizon)}, ${v3(PALETTE.nightHorizon)}, ${v3(PALETTE.dawnHorizon)}, h);
  vec3 c = mix(hor, top, pow(clamp(dir.y, 0.0, 1.0), 0.42));
  float dusk = 1.0 - smoothstep(0.0, 0.35, h), dawn = smoothstep(0.62, 1.0, h);
  c += vec3(0.9, 0.42, 0.2) * pow(max(dot(dir, normalize(vec3(-0.8, 0.05, -0.6))), 0.0), 5.0) * 0.55 * dusk;
  c += vec3(1.0, 0.8, 0.55) * pow(max(dot(dir, normalize(vec3(0.75, 0.08, -0.65))), 0.0), 4.0) * 0.32 * dawn;
  return c;
}
float fireStrength(float h) { return 1.0 - 0.62 * smoothstep(0.6, 1.0, h) - 0.25 * (1.0 - smoothstep(0.0, 0.3, h)); }
float hash11(float x) { return fract(sin(x * 127.1) * 43758.5453); }
float hash21(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }
float uhash(uint x) {
  x ^= x >> 16u; x *= 0x7feb352du; x ^= x >> 15u; x *= 0x846ca68bu; x ^= x >> 16u;
  return float(x & 0xffffffu) / 16777216.0;
}
`;

const SKY_VS = `${COMMON}
out vec2 vNdc;
void main() { vec2 xy = vec2(float((gl_VertexID << 1) & 2), float(gl_VertexID & 2)) * 2.0 - 1.0; vNdc = xy; gl_Position = vec4(xy, 1.0, 1.0); }`;
const SKY_FS = `${COMMON}
in vec2 vNdc; out vec4 o;
void main() {
  vec3 dir = normalize(uCamFwd.xyz + vNdc.x * uCamRight.w * uCamRight.xyz + vNdc.y * uCamUp.w * uCamUp.xyz);
  vec3 c = skyColor(dir, uEnv.x);
  float night = pow(1.0 - abs(uEnv.x - 0.5) * 2.0, 1.5);
  if (dir.y > 0.02 && night > 0.01) {
    vec2 g = dir.xz / (dir.y + 0.35) * 160.0;
    float h = hash21(floor(g));
    if (h > 0.985) {
      float tw = 0.6 + 0.4 * sin(uCamPos.w * (1.0 + h * 3.0) + h * 40.0);
      c += vec3(0.85, 0.88, 1.0) * smoothstep(0.12, 0.0, length(fract(g) - 0.5)) * night * tw * (h - 0.985) * 55.0;
    }
  }
  o = vec4(c, 1.0);
}`;

const GROUND_VS = `${COMMON}
out vec3 vWorld; out vec3 vNormal;
void main() {
  int n = gridN();
  int ix = gl_VertexID % n, iz = gl_VertexID / n;
  float x = X0 + float(ix) / float(n - 1) * SX, z = Z0 + float(iz) / float(n - 1) * SZ;
  float y = hAt(ix, iz);
  float dx = SX / float(n - 1), dz = SZ / float(n - 1);
  vNormal = normalize(vec3((hAt(ix - 1, iz) - hAt(ix + 1, iz)) / (2.0 * dx), 1.0, (hAt(ix, iz - 1) - hAt(ix, iz + 1)) / (2.0 * dz)));
  vWorld = vec3(x, y, z);
  gl_Position = uViewProj * vec4(vWorld, 1.0);
}`;
const GROUND_FS = `${COMMON}
in vec3 vWorld; in vec3 vNormal; out vec4 o;
float laneGlow(vec3 p) {
  float s = (10.0 - p.z) / (10.0 - (SUMMIT_XZ.y + 1.5));
  if (s < -0.02 || s > 1.02) return 0.0;
  float sc = clamp(s, 0.0, 1.0), g = 0.0;
  for (int l = 0; l < 3; l++) {
    float x0 = laneX(l, sc);
    float slope = (laneX(l, min(sc + 0.002, 1.0)) - laneX(l, max(sc - 0.002, 0.0))) / (0.004 * 34.5);
    float d = abs(p.x - x0) / sqrt(1.0 + slope * slope);
    g += exp(-d * d * 0.7);
  }
  return g;
}
void main() {
  float h = uEnv.x;
  vec3 n = normalize(vNormal);
  float band = 0.5 + 0.5 * sin(vWorld.y * 3.1 + sin(vWorld.x * 0.35) * 0.8);
  float steep = 1.0 - n.y;
  vec3 albedo = vec3(0.56, 0.53, 0.49) * (0.86 + 0.14 * band * (0.4 + steep));
  albedo = mix(vec3(0.29, 0.31, 0.24), albedo, smoothstep(1.0, 5.0, vWorld.y + steep * 4.0));
  float dusk = 1.0 - smoothstep(0.0, 0.35, h), dawn = smoothstep(0.62, 1.0, h);
  vec3 light = skyColor(normalize(vec3(n.x, max(n.y, 0.05), n.z)), h) * 0.42 * (0.55 + 0.45 * n.y);
  light += vec3(0.95, 0.5, 0.28) * max(dot(n, normalize(vec3(-0.8, 0.35, -0.5))), 0.0) * 0.55 * dusk;
  light += vec3(1.0, 0.78, 0.55) * max(dot(n, normalize(vec3(0.7, 0.42, -0.55))), 0.0) * 1.25 * dawn;
  vec2 sd = vWorld.xz - SUMMIT_XZ;
  float fire = laneGlow(vWorld) * 0.5 + 2.2 * exp(-dot(sd, sd) / 18.0);
  light += EMBER * fire * fireStrength(h) * (1.0 + uExtra.x * 1.5);
  float pd = distance(vWorld.xz, uPointer.xz);
  light += SAFFRON * exp(-pd * pd / 6.0) * 0.6 * uPointer.w;
  vec3 c = albedo * light;
  float fog = 1.0 - exp(-distance(vWorld, uCamPos.xyz) * uExtra.y);
  vec3 view = normalize(vWorld - uCamPos.xyz);
  o = vec4(mix(c, skyColor(normalize(vec3(view.x, 0.02, view.z)), h), clamp(fog, 0.0, 1.0)), 1.0);
}`;

const FIRE_VS = `${COMMON}
uniform float uTorches;
uniform float uEmbers;
uniform float uIdOffset;
out vec2 vUv; out vec3 vCol;
void main() {
  float id = float(gl_InstanceID) + uIdOffset;
  float seed = uhash(uint(id) + 977u) * 100.0;
  float t = uCamPos.w;
  bool ember = id >= uTorches && id < uTorches + uEmbers;
  bool hill = id >= uTorches + uEmbers;
  vec3 pos; float heat;
  if (hill) {
    float k = id - uTorches - uEmbers;
    float fire = floor(k / 24.0), j = mod(k, 24.0);
    bool far = fract(fire * 0.618) > 0.55;
    float side = fract(fire * 0.381) > 0.5 ? -1.0 : 1.0;
    float x = 8.0 + side * (24.0 + 40.0 * fract(sin(fire * 12.9898) * 43758.5453));
    float z = far ? -80.0 + (fract(sin(fire * 78.233) * 43758.5453) - 0.5) * 8.0 : -34.0 - fract(sin(fire * 78.233) * 43758.5453) * 22.0;
    float a = j * 2.39996, r = 0.12 * sqrt(j);
    vec2 p = vec2(x + cos(a) * r, z + sin(a) * r);
    pos = vec3(p.x, heightAt(p) + 0.25 + fract(seed * 9.1) * 0.5, p.y);
    heat = 0.6 + 0.25 * sin(t * (7.0 + fract(seed) * 6.0) + seed * 31.0) + 0.15 * sin(t * 17.0 + seed * 3.0);
  } else if (!ember) {
    int lane = int(mod(id, 3.0));
    float group = floor(fract(seed * 13.7) * 220.0);
    float speed = 0.0055 + 0.0012 * hash11(group * 1.7);
    float s = fract(group / 220.0 + (fract(seed * 5.3) - 0.5) * 0.014 + t * speed);
    float x = laneX(lane, s) + (fract(seed * 31.3) - 0.5) * 2.4;
    float z = laneZ(s) + (fract(seed * 57.9) - 0.5) * 1.6;
    vec2 d = uPointer.xz - vec2(x, z);
    float dist = length(d);
    heat = 0.72 + 0.18 * sin(t * (5.0 + fract(seed * 3.1) * 4.0) + seed * 50.0) + 0.1 * sin(t * 13.0 + seed * 9.0);
    if (uPointer.w > 0.01 && dist < 7.0 && dist > 0.001) {
      float k = 1.0 - dist / 7.0;
      vec2 pull = d / dist * min(dist, k * k * 3.2 * uPointer.w);
      x += pull.x; z += pull.y;
      heat += k * 0.8 * uPointer.w;
    }
    heat = (heat + uExtra.x * 0.5) * smoothstep(0.0, 0.03, s) * (1.0 - 0.7 * smoothstep(0.96, 1.0, s));
    pos = vec3(x, heightAt(vec2(x, z)) + 0.32, z);
  } else {
    float rate = 0.22 + 0.3 * fract(seed * 3.7);
    float cyc = t * rate + fract(seed * 1.7);
    float life = 1.0 - fract(cyc);
    float age = (1.0 - life) / rate;
    float k = floor(cyc);
    vec2 r = vec2(uhash(uint(id) * 7919u + uint(k) * 104729u) - 0.5, uhash(uint(id) * 15485863u + uint(k) * 32452843u + 1u) - 0.5);
    vec2 base = SUMMIT_XZ + r * 2.4;
    float lift = 1.6 + 2.4 * uhash(uint(id) * 2654435761u + uint(k));
    pos = vec3(base.x, heightAt(base) + 0.4, base.y) + vec3(r.x * 1.2 + 0.35 * age * 0.5, lift + 0.3 * age, r.y * 1.2) * age
        + vec3(sin(t * 0.7 + seed * 6.0), 0.0, cos(t * 0.5 + seed * 4.0)) * 0.4 * age;
    heat = life * (1.0 + uExtra.x);
  }
  vec2 corner = vec2(float(gl_VertexID & 1), float(gl_VertexID >> 1)) * 2.0 - 1.0;
  float radius = ember ? 0.05 * clamp(heat, 0.0, 1.6) : (hill ? 0.24 : 0.11);
  vec4 clip = uViewProj * vec4(pos, 1.0);
  float rpx = max(radius * uCamFwd.w / max(clip.w, 0.05), (ember ? 0.55 : 0.9) * uExtra.w);
  float q = rpx * 3.0;
  gl_Position = clip + vec4(corner * q * 2.0 / uViewport.xy * clip.w, 0.0, 0.0);
  if (clip.w < 0.3) gl_Position = vec4(2.0, 2.0, 2.0, 1.0);
  vUv = corner;
  float fade = clamp(1.4 - clip.w / 110.0, 0.4, 1.0) * uViewport.z;
  float hh = ember ? heat * heat : clamp(heat, 0.0, 1.6);
  vCol = mix(EMBER, SAFFRON, clamp(hh - 0.45, 0.0, 1.0)) * hh * fireStrength(uEnv.x) * fade * (ember ? 0.9 : (hill ? 0.55 : 1.0));
}`;
const FIRE_FS = `${COMMON}
in vec2 vUv; in vec3 vCol; out vec4 o;
void main() {
  float d2 = dot(vUv, vUv) * 9.0;
  float core = exp(-d2 * 2.2), halo = exp(-d2 * 0.3) * 0.07;
  o = vec4(vCol * (core * 1.25 + halo) + SAFFRON * core * core * 0.25 * length(vCol), 1.0);
}`;

function program(gl, vs, fs) {
  const make = (type, src) => {
    const s = gl.createShader(type);
    gl.shaderSource(s, src); gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(s) || 'shader');
    return s;
  };
  const p = gl.createProgram();
  gl.attachShader(p, make(gl.VERTEX_SHADER, vs));
  gl.attachShader(p, make(gl.FRAGMENT_SHADER, fs));
  gl.linkProgram(p);
  if (!gl.getProgramParameter(p, gl.LINK_STATUS)) throw new Error(gl.getProgramInfoLog(p) || 'link');
  return p;
}

export async function createRenderer({ canvas, lowPower, state }) {
  const gl = canvas.getContext('webgl2', { antialias: !lowPower, alpha: false, depth: true, powerPreference: lowPower ? 'low-power' : 'high-performance' });
  if (!gl) throw new Error('no webgl2');
  const counts = torchCounts(lowPower, 'WebGL2');
  const n = GRID(true);
  const heights = await bakeHeights(n);

  const tex = gl.createTexture();
  gl.bindTexture(gl.TEXTURE_2D, tex);
  gl.texImage2D(gl.TEXTURE_2D, 0, gl.R32F, n, n, 0, gl.RED, gl.FLOAT, heights);
  gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
  gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);

  const sky = program(gl, SKY_VS, SKY_FS);
  const ground = program(gl, GROUND_VS, GROUND_FS);
  const fire = program(gl, FIRE_VS, FIRE_FS);

  const idx = new Uint32Array((n - 1) * (n - 1) * 6);
  let k = 0;
  for (let z = 0; z < n - 1; z++) for (let x = 0; x < n - 1; x++) {
    const a = z * n + x, b = a + 1, c = a + n, d = c + 1;
    idx[k++] = a; idx[k++] = c; idx[k++] = b; idx[k++] = b; idx[k++] = c; idx[k++] = d;
  }
  const vao = gl.createVertexArray();
  gl.bindVertexArray(vao);
  const ib = gl.createBuffer();
  gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, ib);
  gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, idx, gl.STATIC_DRAW);

  const locs = new Map();
  const loc = (p, name) => {
    const key = name + (p === sky ? 's' : p === ground ? 'g' : 'f');
    if (!locs.has(key)) locs.set(key, gl.getUniformLocation(p, name));
    return locs.get(key);
  };

  let torchCount = counts.torches, lostFn = null;
  canvas.addEventListener('webglcontextlost', (e) => { e.preventDefault(); lostFn?.(); }, { once: true });

  return {
    backend: 'WebGL2',
    zeroToOne: false,
    heights, gridN: n,
    torches: () => torchCount,
    resize: () => {},
    reduce() { if (torchCount <= 2048) return false; torchCount = Math.floor(torchCount / 2); return true; },
    onLost: (fn) => { lostFn = fn; },
    render(cam) {
      const w = canvas.width, h = canvas.height;
      gl.viewport(0, 0, w, h);
      const set = (p) => {
        gl.useProgram(p);
        gl.uniformMatrix4fv(loc(p, 'uViewProj'), false, cam.viewProj);
        gl.uniform4f(loc(p, 'uCamPos'), cam.eye[0], cam.eye[1], cam.eye[2], state.time);
        gl.uniform4f(loc(p, 'uCamRight'), cam.right[0], cam.right[1], cam.right[2], cam.tanX);
        gl.uniform4f(loc(p, 'uCamUp'), cam.up[0], cam.up[1], cam.up[2], cam.tanY);
        gl.uniform4f(loc(p, 'uCamFwd'), cam.fwd[0], cam.fwd[1], cam.fwd[2], cam.focal * (h / Math.max(1, canvas.clientHeight || h)));
        gl.uniform4f(loc(p, 'uPointer'), state.pointer[0], state.pointer[1], state.pointer[2], state.pointer[3]);
        gl.uniform4f(loc(p, 'uEnv'), state.hour, 0, counts.torches, counts.torches + counts.embers);
        gl.uniform4f(loc(p, 'uExtra'), state.flare, 0.0105, n, w / Math.max(1, canvas.clientWidth || w));
        gl.uniform4f(loc(p, 'uViewport'), w, h, 0.5, counts.embers);
        gl.uniform1i(loc(p, 'uHeights'), 0);
      };
      gl.activeTexture(gl.TEXTURE0);
      gl.bindTexture(gl.TEXTURE_2D, tex);
      gl.bindVertexArray(vao);
      gl.clearColor(0, 0, 0, 1);
      gl.depthMask(true);                       // a masked depth buffer would not clear
      gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
      gl.disable(gl.BLEND);
      gl.disable(gl.DEPTH_TEST);
      gl.depthMask(false);
      set(sky); gl.drawArrays(gl.TRIANGLES, 0, 3);
      gl.enable(gl.DEPTH_TEST); gl.depthFunc(gl.LESS); gl.depthMask(true);
      set(ground); gl.drawElements(gl.TRIANGLES, idx.length, gl.UNSIGNED_INT, 0);
      gl.depthMask(false);
      gl.enable(gl.BLEND); gl.blendFunc(gl.ONE, gl.ONE);
      set(fire);
      gl.uniform1f(loc(fire, 'uTorches'), counts.torches);
      gl.uniform1f(loc(fire, 'uEmbers'), counts.embers);
      gl.uniform1f(loc(fire, 'uIdOffset'), 0);
      gl.drawArraysInstanced(gl.TRIANGLE_STRIP, 0, 4, torchCount);
      gl.uniform1f(loc(fire, 'uIdOffset'), counts.torches);
      gl.drawArraysInstanced(gl.TRIANGLE_STRIP, 0, 4, counts.embers + counts.hill);
    },
  };
}
