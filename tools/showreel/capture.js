// node capture.js <phone|desktop> <outdir> [fromFrame] [toFrame]
// Deterministic frame-by-frame capture: the page's requestAnimationFrame runs on a virtual clock we advance.
const { chromium } = require('playwright');
const fs = require('fs');
const FPS = 30, BEAT = 60 / 140, BAR = BEAT * 4;
const [mode, out, fromArg, toArg] = process.argv.slice(2);
fs.mkdirSync(out, { recursive: true });
const ease = (t) => (t < 0 ? 0 : t > 1 ? 1 : t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const lerp = (a, b, t) => a + (b - a) * t;
const seg = (t, a, b) => Math.min(1, Math.max(0, (t - a) / (b - a)));

// storyboard (seconds); scroll is a fraction of the page's scroll range
const plan = {
  phone: { from: 0, to: Math.round(BAR * 8 * FPS), // 0 .. 13.71 s
    scroll: (t) => t < BAR * 2 ? 0 : t < BAR * 5 ? lerp(0, 0.3, ease(seg(t, BAR * 2, BAR * 5))) : t < BAR * 7 ? lerp(0.3, 1, ease(seg(t, BAR * 5, BAR * 7))) : 1,
    pointer: (t) => {
      // tap on the ridge, then a drag across the procession, then a tap on WhatsApp (bottom phase)
      if (t >= 0.25 && t < 0.55) return { type: 'touch', x: 250, y: 560, down: true };
      if (t >= BAR && t < BAR * 2 - 0.05) { const k = ease(seg(t, BAR, BAR * 2 - 0.1)); return { type: 'touch', x: lerp(90, 320, k), y: lerp(640, 470, k) + Math.sin(k * 6) * 25, down: true }; }
      if (t >= BAR * 7 + BEAT * 1 && t < BAR * 7 + BEAT * 1.6) return { type: 'touch', target: 'whatsapp', down: true };
      return null;
    } },
  desktop: { from: Math.round((BAR * 2 - 0.5) * FPS), to: Math.round(BAR * 7 * FPS), // 2.93 .. 12.0 s
    scroll: (t) => t < BAR * 3.5 ? 0 : t < BAR * 5 ? lerp(0, 0.3, ease(seg(t, BAR * 3.5, BAR * 5))) : t < BAR * 7 ? lerp(0.3, 1, ease(seg(t, BAR * 5, BAR * 7))) : 1,
    pointer: (t) => {
      // the mouse sweeps over the ridge (walkers follow), circles the summit, then rests on "Get in touch"
      if (t < BAR * 2) return { type: 'mouse', x: 1050, y: 640 };
      if (t < BAR * 3) { const k = ease(seg(t, BAR * 2, BAR * 3)); return { type: 'mouse', x: lerp(1050, 520, k), y: lerp(640, 560, k) + Math.sin(k * 7) * 40 }; }
      if (t < BAR * 3.5) { const k = seg(t, BAR * 3, BAR * 3.5); return { type: 'mouse', x: 600 + Math.cos(k * 6.28) * 90, y: 360 + Math.sin(k * 6.28) * 50 }; }
      if (t < BAR * 4.2) { const k = ease(seg(t, BAR * 3.5, BAR * 4.2)); return { type: 'mouse', x: lerp(690, 900, k), y: lerp(360, 520, k) }; }
      return { type: 'mouse', x: 900, y: 520 };
    } },
};
const P = plan[mode];

(async () => {
  const b = await chromium.launch({ args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const ctx = mode === 'phone'
    ? await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2.77, isMobile: true, hasTouch: true, bypassCSP: true })
    : await b.newContext({ viewport: { width: 1280, height: 800 }, deviceScaleFactor: 1, bypassCSP: true });
  await ctx.addInitScript(() => {
    delete Navigator.prototype.gpu;
    let q = [], id = 0;
    window.__vt = 0;
    window.requestAnimationFrame = (cb) => { q.push([++id, cb]); return id; };
    window.cancelAnimationFrame = (x) => { q = q.filter(([i]) => i !== x); };
    window.__step = (ms) => { window.__vt += ms; const cbs = q; q = []; for (const [, cb] of cbs) cb(window.__vt); };
    // capture only: a tap on WhatsApp shows its pressed state but does not leave the page
    document.addEventListener('click', (e) => { if (e.target.closest('a[href^="https://wa.me"]')) e.preventDefault(); }, true);
  });
  const page = await ctx.newPage();
  page.on('pageerror', (e) => console.log('pageerror', e.message));
  await page.goto('http://localhost:8080/?rsforce');
  await page.addStyleTag({ content: 'html{scroll-behavior:auto!important}' });
  for (let i = 0; i < 400; i++) {
    await page.evaluate(() => window.__step(16.67));
    if (await page.evaluate(() => document.querySelector('[data-scene]').classList.contains('is-live'))) break;
    await page.waitForTimeout(20);
  }
  for (let i = 0; i < 90; i++) await page.evaluate(() => window.__step(16.67));   // let the procession settle
  await page.waitForTimeout(1000);                                                  // canvas fade-in finished
  const cdp = await ctx.newCDPSession(page);
  const from = fromArg ? +fromArg : P.from, to = toArg ? +toArg : P.to;
  const log = [];
  let touching = false;
  const maxScroll = await page.evaluate(() => document.documentElement.scrollHeight - innerHeight);
  // advance the virtual clock to the first frame's time
  for (let f = 0; f < from; f++) await page.evaluate(() => window.__step(1000 / 30));
  for (let f = from; f <= to; f++) {
    const t = f / FPS;
    await page.evaluate((y) => window.scrollTo({ top: y, behavior: 'instant' }), Math.round(P.scroll(t) * maxScroll));
    const ptr = P.pointer(t);
    let rec = null;
    if (ptr && ptr.target) {
      const box = await page.locator('.close a[href^="https://wa.me"]').boundingBox();
      if (box) { ptr.x = box.x + box.width * 0.35; ptr.y = box.y + box.height / 2; }
    }
    if (ptr && ptr.type === 'mouse') { await page.mouse.move(ptr.x, ptr.y); rec = { kind: 'mouse', x: ptr.x, y: ptr.y }; }
    if (ptr && ptr.type === 'touch') {
      await cdp.send('Input.dispatchTouchEvent', { type: touching ? 'touchMove' : 'touchStart', touchPoints: [{ x: ptr.x, y: ptr.y, radiusX: 10, radiusY: 10, force: 1, id: 1 }] });
      touching = true; rec = { kind: 'touch', x: ptr.x, y: ptr.y };
    } else if (touching) {
      await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
      touching = false;
    }
    await page.evaluate(() => window.__step(1000 / 30));
    await page.screenshot({ path: `${out}/${String(f).padStart(4, '0')}.png` });
    log.push({ f, ...(rec || {}) });
    if (f % 30 === 0) console.log(mode, 'frame', f);
  }
  fs.writeFileSync(`${out}/pointer-${from}-${to}.json`, JSON.stringify(log));
  await b.close();
})();
