// WebGPU renderer: a compute pass moves every torch and ember; one render pass draws sky, ridge and fire.
import { WORLD, PALETTE, GRID, torchCounts, bakeHeights, sampleHeights } from 'rs/scene-core';

// Uniform block: 12 × vec4f = 192 bytes (std layout: mat4 = 4 vec4, then 8 vec4).
const UNIFORM_FLOATS = 48;

const COMMON = /* wgsl */ `
struct U {
  viewProj: mat4x4f,
  camPos: vec4f,     // xyz eye, w time
  camRight: vec4f,   // xyz, w tan(fovX/2)
  camUp: vec4f,      // xyz, w tan(fovY/2)
  camFwd: vec4f,     // xyz, w focal length in pixels
  pointer: vec4f,    // xyz on the ground, w strength
  env: vec4f,        // hour 0..1, dt, torch count, total count
  extra: vec4f,      // flare, fog density, grid n, pixel ratio
  viewport: vec4f,   // width, height
};
struct P { pos: vec4f, vel: vec4f };  // pos.w heat/life, vel.w seed

@group(0) @binding(0) var<uniform> u: U;
@group(0) @binding(1) var<storage, read> heights: array<f32>;

const X0 = ${WORLD.X0.toFixed(1)};
const Z0 = ${WORLD.Z0.toFixed(1)};
const SX = ${WORLD.SX.toFixed(1)};
const SZ = ${WORLD.SZ.toFixed(1)};
const SUMMIT_XZ = vec2f(${WORLD.SUMMIT[0].toFixed(1)}, ${WORLD.SUMMIT[2].toFixed(1)});
const EMBER = vec3f(${PALETTE.ember.join(', ')});
const SAFFRON = vec3f(${PALETTE.saffron.join(', ')});

fn gridN() -> i32 { return i32(u.extra.z); }
fn hAt(ix: i32, iz: i32) -> f32 {
  let n = gridN();
  return heights[u32(clamp(iz, 0, n - 1) * n + clamp(ix, 0, n - 1))];
}
fn heightAt(xz: vec2f) -> f32 {
  let n = f32(gridN() - 1);
  let g = vec2f((xz.x - X0) / SX, (xz.y - Z0) / SZ) * n;
  let i = floor(g);
  let f = clamp(g - i, vec2f(0.0), vec2f(1.0));
  let ix = i32(i.x); let iz = i32(i.y);
  return mix(mix(hAt(ix, iz), hAt(ix + 1, iz), f.x), mix(hAt(ix, iz + 1), hAt(ix + 1, iz + 1), f.x), f.y);
}

// three switchback lanes climbing the south face to the summit
fn laneStart(lane: u32) -> f32 { return select(select(23.0, 7.0, lane == 1u), -9.0, lane == 0u); }
// three valley approaches merge into one switchback road a third of the way up
fn roadX(s: f32) -> f32 { return SUMMIT_XZ.x + 1.5 * (1.0 - s) + mix(10.5, 0.5, pow(s, 0.9)) * sin(s * 25.13 + 0.6); }
fn laneX(lane: u32, s: f32) -> f32 { return mix(laneStart(lane), roadX(s), smoothstep(0.0, 0.3, s)); }
fn laneZ(s: f32) -> f32 { return mix(10.0, SUMMIT_XZ.y + 1.5, s); }

fn hourMix(a: vec3f, b: vec3f, c: vec3f, h: f32) -> vec3f {
  return select(mix(b, c, smoothstep(0.5, 1.0, h)), mix(a, b, smoothstep(0.0, 0.5, h)), h < 0.5);
}
fn skyColor(dir: vec3f, h: f32) -> vec3f {
  let top = hourMix(vec3f(${PALETTE.duskTop.join(', ')}), vec3f(${PALETTE.nightTop.join(', ')}), vec3f(${PALETTE.dawnTop.join(', ')}), h);
  let hor = hourMix(vec3f(${PALETTE.duskHorizon.join(', ')}), vec3f(${PALETTE.nightHorizon.join(', ')}), vec3f(${PALETTE.dawnHorizon.join(', ')}), h);
  let e = clamp(dir.y, 0.0, 1.0);
  var c = mix(hor, top, pow(e, 0.42));
  let dusk = 1.0 - smoothstep(0.0, 0.35, h);
  let dawn = smoothstep(0.62, 1.0, h);
  let west = normalize(vec3f(-0.8, 0.05, -0.6));
  let east = normalize(vec3f(0.75, 0.08, -0.65));
  c += vec3f(0.9, 0.42, 0.2) * pow(max(dot(dir, west), 0.0), 5.0) * 0.55 * dusk;
  c += vec3f(1.0, 0.8, 0.55) * pow(max(dot(dir, east), 0.0), 4.0) * 0.32 * dawn;
  return c;
}
// bonfires on the far ranges: 24 flames per fire, fires scattered along the ridgelines behind the massif
fn hillFire(k: u32) -> vec3f {
  let fire = f32(k / 24u);
  let far = fract(fire * 0.618) > 0.55;
  let side = select(1.0, -1.0, fract(fire * 0.381) > 0.5);
  let x = 8.0 + side * (24.0 + 40.0 * fract(sin(fire * 12.9898) * 43758.5453));
  let z = select(-34.0 - fract(sin(fire * 78.233) * 43758.5453) * 22.0, -80.0 + (fract(sin(fire * 78.233) * 43758.5453) - 0.5) * 8.0, far);
  let a = f32(k % 24u) * 2.39996;
  let r = 0.12 * sqrt(f32(k % 24u));
  let p = vec2f(x + cos(a) * r, z + sin(a) * r);
  return vec3f(p.x, heightAt(p) + 0.25, p.y);
}
fn fireStrength(h: f32) -> f32 { return 1.0 - 0.62 * smoothstep(0.6, 1.0, h) - 0.25 * (1.0 - smoothstep(0.0, 0.3, h)); }
fn hash11(x: f32) -> f32 { return fract(sin(x * 127.1) * 43758.5453); }
fn hash21(p: vec2f) -> f32 { return fract(sin(dot(p, vec2f(127.1, 311.7))) * 43758.5453); }
`;

const SIM = /* wgsl */ `
${COMMON}
@group(0) @binding(2) var<storage, read_write> parts: array<P>;

fn torchTarget(i: u32, seed: f32, t: f32) -> vec4f {
  let lane = i % 3u;
  // walkers move in small groups: a shared place and pace per group, a little scatter inside it
  let group = floor(fract(seed * 13.7) * 220.0);
  let speed = 0.0055 + 0.0012 * hash11(group * 1.7);
  let s = fract(group / 220.0 + (fract(seed * 5.3) - 0.5) * 0.014 + t * speed);
  let side = (fract(seed * 31.3) - 0.5) * 2.4;
  let x = laneX(lane, s) + side;
  let z = laneZ(s) + (fract(seed * 57.9) - 0.5) * 1.6;
  return vec4f(x, heightAt(vec2f(x, z)) + 0.32, z, s);
}

@compute @workgroup_size(64)
fn simulate(@builtin(global_invocation_id) gid: vec3u) {
  let i = gid.x;
  let total = u32(u.env.w);
  if (i >= total) { return; }
  var p = parts[i];
  let dt = u.env.y;
  let t = u.camPos.w;
  let seed = p.vel.w;
  let torchCount = u32(u.env.z);
  var pos = p.pos.xyz;
  var vel = p.vel.xyz;

  if (i < torchCount) {
    let tg = torchTarget(i, seed, t);
    let goal = tg.xyz;
    if (distance(pos, goal) > 5.0) { pos = goal; vel = vec3f(0.0); }
    var acc = (goal - pos) * 5.0 - vel * 3.2;
    // the visitor's torch: walkers within reach turn toward it
    let d = u.pointer.xz - pos.xz;
    let dist = length(d);
    let reach = 7.0;
    var heat = 0.72 + 0.18 * sin(t * (5.0 + fract(seed * 3.1) * 4.0) + seed * 50.0) + 0.1 * sin(t * 13.0 + seed * 9.0);
    if (u.pointer.w > 0.01 && dist < reach && dist > 0.001) {
      let k = 1.0 - dist / reach;
      acc += vec3f(d.x, 0.0, d.y) / dist * (k * k * 26.0 * u.pointer.w);
      heat += k * 0.8 * u.pointer.w;
    }
    vel += acc * dt;
    pos += vel * dt;
    pos.y = max(pos.y, heightAt(pos.xz) + 0.25);
    heat = (heat + u.extra.x * 0.5) * smoothstep(0.0, 0.03, tg.w) * (1.0 - 0.7 * smoothstep(0.96, 1.0, tg.w));
    parts[i] = P(vec4f(pos, heat), vec4f(vel, seed));
    return;
  }

  let emberEnd = torchCount + u32(u.viewport.w);
  if (i >= emberEnd) {
    let f = hillFire(i - emberEnd);
    let flick = 0.6 + 0.25 * sin(t * (7.0 + fract(seed) * 6.0) + seed * 31.0) + 0.15 * sin(t * 17.0 + seed * 3.0);
    parts[i] = P(vec4f(f + vec3f(0.0, fract(seed * 9.1) * 0.5, 0.0), flick), vec4f(0.0, 0.0, 0.0, seed));
    return;
  }

  // embers rise from the summit fire; a sent message (flare) throws a burst
  var life = p.pos.w - dt * (0.22 + 0.3 * fract(seed * 3.7)) * (1.0 + u.extra.x);
  if (life <= 0.0) {
    let r = vec2f(hash21(vec2f(seed, t)) - 0.5, hash21(vec2f(t, seed * 1.3)) - 0.5);
    let base = SUMMIT_XZ + r * 2.4;
    pos = vec3f(base.x, heightAt(base) + 0.4, base.y);
    let lift = 1.6 + 2.4 * hash11(seed + t) + u.extra.x * 5.0;
    vel = vec3f(r.x * (1.2 + u.extra.x * 4.0), lift, r.y * (1.2 + u.extra.x * 4.0));
    life = 1.0;
  } else {
    let sway = vec3f(sin(t * 0.7 + pos.y * 0.5 + seed * 6.0), 0.0, cos(t * 0.5 + pos.y * 0.4 + seed * 4.0));
    vel += (sway * 0.9 + vec3f(0.35, 0.6, 0.05)) * dt;
    vel *= 1.0 - 0.35 * dt;
    pos += vel * dt;
  }
  parts[i] = P(vec4f(pos, life), vec4f(vel, seed));
}
`;

const DRAW = /* wgsl */ `
${COMMON}
@group(0) @binding(2) var<storage, read> parts: array<P>;

struct SkyOut { @builtin(position) pos: vec4f, @location(0) ndc: vec2f };
@vertex fn vsSky(@builtin(vertex_index) vi: u32) -> SkyOut {
  let xy = vec2f(f32((vi << 1u) & 2u), f32(vi & 2u)) * 2.0 - 1.0;
  return SkyOut(vec4f(xy, 1.0, 1.0), xy);
}
@fragment fn fsSky(in: SkyOut) -> @location(0) vec4f {
  let dir = normalize(u.camFwd.xyz + in.ndc.x * u.camRight.w * u.camRight.xyz + in.ndc.y * u.camUp.w * u.camUp.xyz);
  var c = skyColor(dir, u.env.x);
  // stars, strongest at midnight
  let night = pow(1.0 - abs(u.env.x - 0.5) * 2.0, 1.5);
  if (dir.y > 0.02 && night > 0.01) {
    let g = dir.xz / (dir.y + 0.35) * 160.0;
    let cell = floor(g);
    let h = hash21(cell);
    if (h > 0.985) {
      let f = fract(g) - 0.5;
      let tw = 0.6 + 0.4 * sin(u.camPos.w * (1.0 + h * 3.0) + h * 40.0);
      c += vec3f(0.85, 0.88, 1.0) * smoothstep(0.12, 0.0, length(f)) * night * tw * (h - 0.985) * 55.0;
    }
  }
  return vec4f(c, 1.0);
}

struct GroundOut {
  @builtin(position) pos: vec4f,
  @location(0) world: vec3f,
  @location(1) normal: vec3f,
};
@vertex fn vsGround(@builtin(vertex_index) vi: u32) -> GroundOut {
  let n = gridN();
  let ix = i32(vi) % n; let iz = i32(vi) / n;
  let x = X0 + f32(ix) / f32(n - 1) * SX;
  let z = Z0 + f32(iz) / f32(n - 1) * SZ;
  let y = hAt(ix, iz);
  let dx = SX / f32(n - 1); let dz = SZ / f32(n - 1);
  let nrm = normalize(vec3f((hAt(ix - 1, iz) - hAt(ix + 1, iz)) / (2.0 * dx), 1.0, (hAt(ix, iz - 1) - hAt(ix, iz + 1)) / (2.0 * dz)));
  var out: GroundOut;
  out.pos = u.viewProj * vec4f(x, y, z, 1.0);
  out.world = vec3f(x, y, z);
  out.normal = nrm;
  return out;
}

fn laneGlow(p: vec3f) -> f32 {
  let s = (10.0 - p.z) / (10.0 - (SUMMIT_XZ.y + 1.5));
  if (s < -0.02 || s > 1.02) { return 0.0; }
  let sc = clamp(s, 0.0, 1.0);
  var g = 0.0;
  for (var l = 0u; l < 3u; l++) {
    let x0 = laneX(l, sc);
    let slope = (laneX(l, min(sc + 0.002, 1.0)) - laneX(l, max(sc - 0.002, 0.0))) / (0.004 * 34.5);
    let d = abs(p.x - x0) / sqrt(1.0 + slope * slope);
    g += exp(-d * d * 0.7);
  }
  return g;
}

@fragment fn fsGround(in: GroundOut) -> @location(0) vec4f {
  let h = u.env.x;
  let n = normalize(in.normal);
  // limestone strata, the Zagros signature: banded by height, broken by slope
  let band = 0.5 + 0.5 * sin(in.world.y * 3.1 + sin(in.world.x * 0.35) * 0.8);
  let steep = 1.0 - n.y;
  var albedo = vec3f(0.56, 0.53, 0.49) * (0.86 + 0.14 * band * (0.4 + steep));
  albedo = mix(vec3f(0.29, 0.31, 0.24), albedo, smoothstep(1.0, 5.0, in.world.y + steep * 4.0));
  let dusk = 1.0 - smoothstep(0.0, 0.35, h);
  let dawn = smoothstep(0.62, 1.0, h);
  let sky = skyColor(normalize(vec3f(n.x, max(n.y, 0.05), n.z)), h);
  var light = sky * 0.42 * (0.55 + 0.45 * n.y);
  light += vec3f(0.95, 0.5, 0.28) * max(dot(n, normalize(vec3f(-0.8, 0.35, -0.5))), 0.0) * 0.55 * dusk;
  light += vec3f(1.0, 0.78, 0.55) * max(dot(n, normalize(vec3f(0.7, 0.42, -0.55))), 0.0) * 1.25 * dawn;
  let fs = fireStrength(h);
  let fire = laneGlow(in.world) * 0.5 + 2.2 * exp(-dot(in.world.xz - SUMMIT_XZ, in.world.xz - SUMMIT_XZ) / 18.0);
  light += EMBER * fire * fs * (1.0 + u.extra.x * 1.5);
  // the visitor's torch lights the ground under it
  let pd = distance(in.world.xz, u.pointer.xz);
  light += SAFFRON * exp(-pd * pd / 6.0) * 0.6 * u.pointer.w;
  var c = albedo * light;
  let dist = distance(in.world, u.camPos.xyz);
  let fog = 1.0 - exp(-dist * u.extra.y);
  let view = normalize(in.world - u.camPos.xyz);
  c = mix(c, skyColor(normalize(vec3f(view.x, 0.02, view.z)), h), clamp(fog, 0.0, 1.0));
  return vec4f(c, 1.0);
}

struct FireOut {
  @builtin(position) pos: vec4f,
  @location(0) uv: vec2f,
  @location(1) col: vec3f,
};
@vertex fn vsFire(@builtin(vertex_index) vi: u32, @builtin(instance_index) ii: u32) -> FireOut {
  let p = parts[ii];
  let corner = vec2f(f32(vi & 1u), f32(vi >> 1u)) * 2.0 - 1.0;
  let ember = ii >= u32(u.env.z) && ii < u32(u.env.z) + u32(u.viewport.w);
  let hill = ii >= u32(u.env.z) + u32(u.viewport.w);
  let life = clamp(p.pos.w, 0.0, 1.6);
  let radius = select(select(0.11, 0.24, hill), 0.05 * life, ember);
  let clip = u.viewProj * vec4f(p.pos.xyz, 1.0);
  let rpx = max(radius * u.camFwd.w / max(clip.w, 0.05), select(0.9, 0.55, ember) * u.extra.w);
  let q = rpx * 3.0;
  var out: FireOut;
  out.pos = clip + vec4f(corner * q * 2.0 / u.viewport.xy * clip.w, 0.0, 0.0);
  out.uv = corner;
  let fs = fireStrength(u.env.x);
  // dim when the torch is far: keeps distant switchbacks a line, not a wall
  let fade = clamp(1.4 - clip.w / 110.0, 0.4, 1.0) * u.viewport.z;
  var heat = select(clamp(life, 0.0, 1.6), life * life, ember);
  out.col = mix(EMBER, SAFFRON, clamp(heat - 0.45, 0.0, 1.0)) * heat * fs * fade * select(select(1.0, 0.55, hill), 0.9, ember);
  if (clip.w < 0.3) { out.pos = vec4f(2.0, 2.0, 2.0, 1.0); }
  return out;
}
@fragment fn fsFire(in: FireOut) -> @location(0) vec4f {
  let d2 = dot(in.uv, in.uv) * 9.0;
  let core = exp(-d2 * 2.2);
  let halo = exp(-d2 * 0.3) * 0.07;
  let c = in.col * (core * 1.25 + halo) + SAFFRON * core * core * 0.25 * length(in.col);
  return vec4f(c, 1.0);
}
`;

async function checked(device, label, code) {
  const module = device.createShaderModule({ label, code });
  const info = await module.getCompilationInfo();
  const errors = info.messages.filter((m) => m.type === 'error');
  if (errors.length) throw new Error(errors.map((m) => `${label}:${m.lineNum}:${m.linePos} ${m.message}`).join('\n'));
  return module;
}

export async function createRenderer({ canvas, lowPower, state, noPresent = false }) {
  if (!navigator.gpu) throw new Error('no navigator.gpu');
  const adapter = await navigator.gpu.requestAdapter({ powerPreference: lowPower ? 'low-power' : 'high-performance' });
  if (!adapter) throw new Error('no adapter');
  const device = await adapter.requestDevice({ label: 'ridge' });
  // noPresent: tests render only to offscreen textures (headless browsers without a compositor)
  const context = noPresent ? null : canvas.getContext('webgpu');
  if (!context && !noPresent) throw new Error('no webgpu context');
  const format = navigator.gpu.getPreferredCanvasFormat();
  context?.configure({ device, format, alphaMode: 'opaque', usage: GPUTextureUsage.RENDER_ATTACHMENT | GPUTextureUsage.COPY_SRC });
  device.addEventListener('uncapturederror', (e) => console.error('WebGPU:', e.error.message));

  const fallbackAdapter = adapter.info?.isFallbackAdapter === true;
  const low = lowPower || (fallbackAdapter && !noPresent);
  const counts = torchCounts(low, 'WebGPU');
  const total = counts.torches + counts.embers + counts.hill;
  const n = GRID(low);
  const sampleCount = low ? 1 : 4;

  const heights = await bakeHeights(n);
  const heightBuf = device.createBuffer({ label: 'heights', size: heights.byteLength, usage: GPUBufferUsage.STORAGE | GPUBufferUsage.COPY_DST });
  device.queue.writeBuffer(heightBuf, 0, heights);

  // particles: 8 floats each; embers start with staggered life so the fire never "starts"
  const seed = new Float32Array(total * 8);
  const [sx, , sz] = WORLD.SUMMIT;
  const summitY = sampleHeights(heights, n, sx, sz);
  for (let i = 0; i < total; i++) {
    const o = i * 8;
    seed[o + 7] = Math.random() * 100;
    if (i < counts.torches || i >= counts.torches + counts.embers) { seed[o + 1] = -1000; seed[o + 3] = 1; continue; }  // torches snap onto their lane on the first step
    const life = Math.random();
    seed[o] = sx + (Math.random() - 0.5) * 2.4;
    seed[o + 1] = summitY + 0.4 + (1 - life) * 6;
    seed[o + 2] = sz + (Math.random() - 0.5) * 2.4;
    seed[o + 3] = life;
    seed[o + 5] = 1.5;
  }
  const partBuf = device.createBuffer({ label: 'particles', size: seed.byteLength, usage: GPUBufferUsage.STORAGE | GPUBufferUsage.COPY_DST });
  device.queue.writeBuffer(partBuf, 0, seed);

  const uniBuf = device.createBuffer({ label: 'uniforms', size: UNIFORM_FLOATS * 4, usage: GPUBufferUsage.UNIFORM | GPUBufferUsage.COPY_DST });
  const uni = new Float32Array(UNIFORM_FLOATS);

  // terrain index buffer (triangle list over the n×n grid)
  const idx = new Uint32Array((n - 1) * (n - 1) * 6);
  let k = 0;
  for (let z = 0; z < n - 1; z++) for (let x = 0; x < n - 1; x++) {
    const a = z * n + x, b = a + 1, c = a + n, d = c + 1;
    idx[k++] = a; idx[k++] = c; idx[k++] = b; idx[k++] = b; idx[k++] = c; idx[k++] = d;
  }
  const idxBuf = device.createBuffer({ label: 'ground-index', size: idx.byteLength, usage: GPUBufferUsage.INDEX | GPUBufferUsage.COPY_DST });
  device.queue.writeBuffer(idxBuf, 0, idx);

  const [simMod, drawMod] = await Promise.all([checked(device, 'sim', SIM), checked(device, 'draw', DRAW)]);

  const simLayout = device.createBindGroupLayout({ label: 'sim', entries: [
    { binding: 0, visibility: GPUShaderStage.COMPUTE, buffer: { type: 'uniform' } },
    { binding: 1, visibility: GPUShaderStage.COMPUTE, buffer: { type: 'read-only-storage' } },
    { binding: 2, visibility: GPUShaderStage.COMPUTE, buffer: { type: 'storage' } },
  ] });
  const drawLayout = device.createBindGroupLayout({ label: 'draw', entries: [
    { binding: 0, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: { type: 'uniform' } },
    { binding: 1, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: { type: 'read-only-storage' } },
    { binding: 2, visibility: GPUShaderStage.VERTEX, buffer: { type: 'read-only-storage' } },
  ] });
  const entries = [{ binding: 0, resource: { buffer: uniBuf } }, { binding: 1, resource: { buffer: heightBuf } }, { binding: 2, resource: { buffer: partBuf } }];
  const simGroup = device.createBindGroup({ label: 'sim', layout: simLayout, entries });
  const drawGroup = device.createBindGroup({ label: 'draw', layout: drawLayout, entries });
  const simPL = device.createPipelineLayout({ bindGroupLayouts: [simLayout] });
  const drawPL = device.createPipelineLayout({ bindGroupLayouts: [drawLayout] });

  const ms = { count: sampleCount };
  const depthFormat = 'depth24plus';
  const [simPipe, skyPipe, groundPipe, firePipe] = await Promise.all([
    device.createComputePipelineAsync({ label: 'simulate', layout: simPL, compute: { module: simMod, entryPoint: 'simulate' } }),
    device.createRenderPipelineAsync({ label: 'sky', layout: drawPL,
      vertex: { module: drawMod, entryPoint: 'vsSky' },
      fragment: { module: drawMod, entryPoint: 'fsSky', targets: [{ format }] },
      depthStencil: { format: depthFormat, depthWriteEnabled: false, depthCompare: 'always' }, multisample: ms }),
    device.createRenderPipelineAsync({ label: 'ground', layout: drawPL,
      vertex: { module: drawMod, entryPoint: 'vsGround' },
      fragment: { module: drawMod, entryPoint: 'fsGround', targets: [{ format }] },
      primitive: { topology: 'triangle-list', cullMode: 'none' },
      depthStencil: { format: depthFormat, depthWriteEnabled: true, depthCompare: 'less' }, multisample: ms }),
    device.createRenderPipelineAsync({ label: 'fire', layout: drawPL,
      vertex: { module: drawMod, entryPoint: 'vsFire' },
      fragment: { module: drawMod, entryPoint: 'fsFire', targets: [{ format,
        blend: { color: { srcFactor: 'one', dstFactor: 'one', operation: 'add' }, alpha: { srcFactor: 'one', dstFactor: 'one', operation: 'add' } } }] },
      primitive: { topology: 'triangle-strip' },
      depthStencil: { format: depthFormat, depthWriteEnabled: false, depthCompare: 'less' }, multisample: ms }),
  ]);

  let depthTex = null, msaaTex = null, texW = 0, texH = 0;
  const ensureTargets = (w, h) => {
    if (w === texW && h === texH && depthTex) return;
    depthTex?.destroy(); msaaTex?.destroy();
    depthTex = device.createTexture({ label: 'depth', size: [w, h], format: depthFormat, sampleCount, usage: GPUTextureUsage.RENDER_ATTACHMENT });
    msaaTex = sampleCount > 1 ? device.createTexture({ label: 'msaa', size: [w, h], format, sampleCount, usage: GPUTextureUsage.RENDER_ATTACHMENT }) : null;
    texW = w; texH = h;
  };

  let drawCount = total, torchCount = counts.torches, lost = null, dead = false, captureTex = null;
  device.lost.then((info) => { dead = true; if (info.reason !== 'destroyed' && lost) lost(info); });

  return {
    backend: 'WebGPU',
    debug: { device, context },
    zeroToOne: true,
    heights, gridN: n,
    torches: () => torchCount,
    resize: () => {},
    reduce() {
      if (torchCount <= 4096) return false;
      torchCount = Math.floor(torchCount / 2);
      drawCount = torchCount + counts.embers;
      // embers live after the torches in the buffer: keep them by drawing from a shifted range
      return true;
    },
    onLost: (fn) => { lost = fn; },
    /** Render one frame into an offscreen texture and read it back (tests and poster capture). */
    async capture(cam) {
      const w = canvas.width, h = canvas.height;
      captureTex = device.createTexture({ size: [w, h], format, usage: GPUTextureUsage.RENDER_ATTACHMENT | GPUTextureUsage.COPY_SRC });
      this.render(cam, 0, true);
      const bpr = Math.ceil((w * 4) / 256) * 256;
      const buf = device.createBuffer({ size: bpr * h, usage: GPUBufferUsage.COPY_DST | GPUBufferUsage.MAP_READ });
      const enc = device.createCommandEncoder();
      enc.copyTextureToBuffer({ texture: captureTex }, { buffer: buf, bytesPerRow: bpr }, [w, h]);
      device.queue.submit([enc.finish()]);
      await buf.mapAsync(GPUMapMode.READ);
      const src = new Uint8Array(buf.getMappedRange()), out = new Uint8ClampedArray(w * h * 4);
      const bgra = format.startsWith('bgra');
      for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
        const i = y * bpr + x * 4, o = (y * w + x) * 4;
        out[o] = src[i + (bgra ? 2 : 0)]; out[o + 1] = src[i + 1]; out[o + 2] = src[i + (bgra ? 0 : 2)]; out[o + 3] = 255;
      }
      buf.destroy(); captureTex.destroy(); captureTex = null;
      return new ImageData(out, w, h);
    },
    render(cam, dt, paused) {
      if (dead || (!context && !captureTex)) return;
      const w = canvas.width, h = canvas.height;
      ensureTargets(w, h);
      uni.set(cam.viewProj, 0);
      uni.set([cam.eye[0], cam.eye[1], cam.eye[2], state.time], 16);
      uni.set([cam.right[0], cam.right[1], cam.right[2], cam.tanX], 20);
      uni.set([cam.up[0], cam.up[1], cam.up[2], cam.tanY], 24);
      uni.set([cam.fwd[0], cam.fwd[1], cam.fwd[2], cam.focal * (h / Math.max(1, canvas.clientHeight || h))], 28);
      uni.set(state.pointer, 32);
      uni.set([state.hour, paused ? 0 : dt, counts.torches, total], 36);
      uni.set([state.flare, 0.0105, n, w / Math.max(1, canvas.clientWidth || w)], 40);
      uni.set([w, h, 0.5, counts.embers], 44);
      device.queue.writeBuffer(uniBuf, 0, uni);

      const enc = device.createCommandEncoder({ label: 'frame' });
      if (!paused || dt === 0) {
        const cp = enc.beginComputePass({ label: 'simulate' });
        cp.setPipeline(simPipe);
        cp.setBindGroup(0, simGroup);
        cp.dispatchWorkgroups(Math.ceil(total / 64));
        cp.end();
      }
      const view = (captureTex ?? context.getCurrentTexture()).createView();
      const rp = enc.beginRenderPass({
        label: 'scene',
        colorAttachments: [{ view: msaaTex ? msaaTex.createView() : view, resolveTarget: msaaTex ? view : undefined, clearValue: [0, 0, 0, 1], loadOp: 'clear', storeOp: msaaTex ? 'discard' : 'store' }],
        depthStencilAttachment: { view: depthTex.createView(), depthClearValue: 1, depthLoadOp: 'clear', depthStoreOp: 'discard' },
      });
      rp.setBindGroup(0, drawGroup);
      rp.setPipeline(skyPipe); rp.draw(3);
      rp.setPipeline(groundPipe); rp.setIndexBuffer(idxBuf, 'uint32'); rp.drawIndexed(idx.length);
      rp.setPipeline(firePipe);
      rp.draw(4, torchCount);                                   // torches
      rp.draw(4, counts.embers + counts.hill, 0, counts.torches);   // embers, then the far bonfires
      rp.end();
      device.queue.submit([enc.finish()]);
      void drawCount;
    },
  };
}
