# Compose the vertical showreel from the captured phone/desktop frames.
# python3 compose.py <outdir> [first] [last]
import json, math, os, sys
from functools import lru_cache
from PIL import Image, ImageDraw, ImageFilter, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = sys.argv[1]
W, H, FPS = 1080, 1920, 30
BEAT = 60 / 140
BAR = BEAT * 4
T1, T3, T4, T5 = BAR * 2, BAR * 5, BAR * 7, BAR * 8
END = BAR * 10
N = round(END * FPS)

NIGHT = (14, 19, 47)
DUSK = (31, 39, 80)
EMBER = (241, 131, 54)
SAFFRON = (255, 204, 79)
LIME = (245, 238, 224)
INK = (17, 23, 54)

PH_DIR, DK_DIR = os.path.join(HERE, 'phone'), os.path.join(HERE, 'desk')
PH_FILES = sorted(int(f[:4]) for f in os.listdir(PH_DIR) if f[:4].isdigit())
DK_FILES = sorted(int(f[:4]) for f in os.listdir(DK_DIR) if f[:4].isdigit())


def load_ptr(d):
    out = {}
    for f in os.listdir(d):
        if f.startswith('pointer-'):
            for r in json.load(open(os.path.join(d, f))):
                out[r['f']] = r
    return out


PH_PTR, DK_PTR = load_ptr(PH_DIR), load_ptr(DK_DIR)


@lru_cache(maxsize=6)
def phone(f):
    f = min(max(f, PH_FILES[0]), PH_FILES[-1])
    return Image.open(os.path.join(PH_DIR, f'{f:04d}.png')).convert('RGB')


@lru_cache(maxsize=6)
def desk(f):
    f = min(max(f, DK_FILES[0]), DK_FILES[-1])
    return Image.open(os.path.join(DK_DIR, f'{f:04d}.png')).convert('RGB')


def font(name, size, axes):
    f = ImageFont.truetype(os.path.join(HERE, name), size)
    f.set_variation_by_axes(axes)
    return f


CAP = font('anybody.ttf', 74, [76, 800])
NAME = font('anybody.ttf', 250, [60, 830])
TAG = font('atkinson.ttf', 46, [700])
PILL = font('atkinson.ttf', 46, [800])

clamp = lambda x, a=0.0, b=1.0: max(a, min(b, x))
seg = lambda t, a, b: clamp((t - a) / (b - a))
lerp = lambda a, b, k: a + (b - a) * k
expo = lambda k: 1 if k >= 1 else 1 - 2 ** (-10 * k)
inout = lambda k: 4 * k ** 3 if k < 0.5 else 1 - (-2 * k + 2) ** 3 / 2


@lru_cache(maxsize=16)
def rmask(w, h, r):
    m = Image.new('L', (w * 2, h * 2), 0)
    ImageDraw.Draw(m).rounded_rectangle((0, 0, w * 2 - 1, h * 2 - 1), r * 2, fill=255)
    return m.resize((w, h), Image.LANCZOS)


@lru_cache(maxsize=1)
def background():
    bg = Image.new('RGB', (W, H), NIGHT)
    glow = Image.new('L', (W, H), 0)
    ImageDraw.Draw(glow).ellipse((-300, H - 700, W + 300, H + 500), fill=90)
    glow = glow.filter(ImageFilter.GaussianBlur(160))
    bg.paste(Image.new('RGB', (W, H), (120, 52, 30)), (0, 0), glow)
    top = Image.new('L', (W, H), 0)
    ImageDraw.Draw(top).ellipse((-400, -600, W + 400, 700), fill=70)
    top = top.filter(ImageFilter.GaussianBlur(180))
    bg.paste(Image.new('RGB', (W, H), DUSK), (0, 0), top)
    return bg


def shadow(canvas, box, r, strength=150, blur=40, dy=30):
    x0, y0, x1, y1 = [int(v) for v in box]
    pad = blur * 3
    w, h = x1 - x0 + pad * 2, y1 - y0 + pad * 2
    if w <= 0 or h <= 0:
        return
    m = Image.new('L', (w, h), 0)
    ImageDraw.Draw(m).rounded_rectangle((pad, pad, w - pad, h - pad), r, fill=strength)
    m = m.filter(ImageFilter.GaussianBlur(blur))
    canvas.paste(Image.new('RGB', (w, h), (4, 6, 16)), (x0 - pad, y0 - pad + dy), m)


def paste_round(canvas, img, x, y, r, alpha=1.0):
    w, h = img.size
    m = rmask(w, h, int(r))
    if alpha < 1:
        m = m.point(lambda v: int(v * alpha))
    canvas.paste(img, (int(round(x)), int(round(y))), m)


# ---------- device mocks ----------

def phone_device(canvas, f, cx, top, width, alpha=1.0, crop_top=0, ptr=True, full=False):
    """Phone screen of `width` px (scaled from the 1080 px capture) inside a thin bezel; full=True draws no bezel."""
    src = phone(f)
    s = width / 1080
    vis_h = int(round((src.height - crop_top) * s)) if not full else int(round(min(src.height - crop_top, (H - top) / s) * s))
    scr = src.crop((0, crop_top, 1080, crop_top + int(vis_h / s))).resize((int(width), vis_h), Image.LANCZOS)
    x = cx - width / 2
    if full:
        canvas.paste(scr, (int(x), int(top)))
    else:
        bez = max(6, int(14 * s * 2.2))
        rr = int(70 * s * 2.2)
        shadow(canvas, (x - bez, top - bez, x + width + bez, top + vis_h + bez), rr, int(170 * alpha))
        frame = Image.new('RGB', (int(width + bez * 2), int(vis_h + bez * 2)), (6, 8, 20))
        paste_round(canvas, frame, x - bez, top - bez, rr + bez, alpha)
        paste_round(canvas, scr, x, top, rr, alpha)
    if ptr:
        draw_touch(canvas, f, x, top - crop_top * s, s, alpha)


def desktop_window(canvas, f, x, y, width, alpha=1.0, ptr=True):
    src = desk(f)
    s = width / 1280
    scr = src.resize((int(width), int(round(800 * s))), Image.LANCZOS)
    bar = int(round(46 * s / 0.78))
    win = Image.new('RGB', (int(width), bar + scr.height), (22, 27, 58))
    d = ImageDraw.Draw(win)
    for i, c in enumerate([(241, 131, 54), (255, 204, 79), (188, 195, 220)]):
        cx = int(bar * 0.62 + i * bar * 0.55)
        d.ellipse((cx - bar * 0.16, bar / 2 - bar * 0.16, cx + bar * 0.16, bar / 2 + bar * 0.16), fill=c)
    d.rounded_rectangle((width * 0.3, bar * 0.24, width * 0.7, bar * 0.76), bar * 0.26, fill=(14, 19, 47))
    win.paste(scr, (0, bar))
    r = int(22 * s / 0.78)
    shadow(canvas, (x, y, x + width, y + win.height), r, int(180 * alpha), 46, 36)
    paste_round(canvas, win, x, y, r, alpha)
    if ptr:
        rec = DK_PTR.get(f)
        if rec and rec.get('kind') == 'mouse':
            draw_cursor(canvas, x + rec['x'] * s, y + bar + rec['y'] * s, 1.25 * s / 0.78, alpha)


def draw_cursor(canvas, x, y, k, alpha=1.0):
    pts = [(0, 0), (0, 30), (7.5, 23), (12.5, 35), (17.5, 33), (12.5, 21.5), (22, 21.5)]
    pts = [(x + px * k, y + py * k) for px, py in pts]
    layer = Image.new('RGBA', canvas.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(layer)
    d.polygon([(px + 2 * k, py + 3 * k) for px, py in pts], fill=(0, 0, 0, int(90 * alpha)))
    layer = layer.filter(ImageFilter.GaussianBlur(2.5 * k))
    d = ImageDraw.Draw(layer)
    d.polygon(pts, fill=(255, 255, 255, int(255 * alpha)), outline=(10, 10, 14, int(255 * alpha)), width=max(2, int(2.2 * k)))
    canvas.paste(layer, (0, 0), layer)


TOUCH_STARTS = sorted(f for f, r in PH_PTR.items() if r.get('kind') == 'touch' and PH_PTR.get(f - 1, {}).get('kind') != 'touch')


def draw_touch(canvas, f, ox, oy, s, alpha=1.0):
    """Finger dot + expanding ring at touch start; positions are CSS px * 2.77 in the 1080 capture."""
    layer = Image.new('RGBA', canvas.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(layer)
    rec = PH_PTR.get(f)
    if rec and rec.get('kind') == 'touch':
        cx, cy = ox + rec['x'] * 2.77 * s, oy + rec['y'] * 2.77 * s
        r = 62 * s
        d.ellipse((cx - r, cy - r, cx + r, cy + r), fill=(255, 255, 255, int(150 * alpha)), outline=(255, 255, 255, int(245 * alpha)), width=max(3, int(7 * s)))
    for f0 in TOUCH_STARTS:
        k = (f - f0) / (0.45 * FPS)
        if 0 <= k <= 1:
            r0 = PH_PTR[f0]
            cx, cy = ox + r0['x'] * 2.77 * s, oy + r0['y'] * 2.77 * s
            r = (62 + 150 * expo(k)) * s
            d.ellipse((cx - r, cy - r, cx + r, cy + r), outline=(255, 255, 255, int(220 * (1 - k) * alpha)), width=max(2, int(6 * s)))
    canvas.paste(layer, (0, 0), layer)


# ---------- captions ----------

CAPTIONS = [
    (0.12, BAR - 0.05, 'this website is alive', 'low'),
    (BAR, T1 - 0.28, 'touch it. the fire follows', 'low'),
    (T1 + BAR + 0.15, T1 + BAR * 2.4, 'your mouse is a torch', 'top'),
    (T1 + BAR * 2.4, T3 - 0.1, 'scroll and the night passes', 'top'),
    (T3 + 0.2, T4 - 0.1, 'same night. every screen.', 'mid'),
    (T4 + 0.25, T5 - 0.05, 'one tap to WhatsApp', 'high'),
]
CAP_Y = {'low': 1430, 'top': 300, 'mid': 905, 'high': 1580}


def caption(canvas, t):
    for a, b, text, where in CAPTIONS:
        if a <= t < b:
            k = expo(seg(t, a, a + 0.18))
            out = seg(t, b - 0.1, b)
            alpha = k * (1 - out)
            sc = lerp(0.88, 1.0, k)
            tw = CAP.getbbox(text)
            w, h = tw[2] - tw[0], tw[3] - tw[1]
            padx, pady = 34, 22
            im = Image.new('RGBA', (w + padx * 2, h + pady * 2 + 10), (0, 0, 0, 0))
            d = ImageDraw.Draw(im)
            d.rounded_rectangle((0, 0, im.width - 1, im.height - 1), 26, fill=(14, 19, 47, 200))
            d.text((padx - tw[0], pady - tw[1] + 2), text, font=CAP, fill=LIME)
            im = im.resize((int(im.width * sc), int(im.height * sc)), Image.LANCZOS)
            if alpha < 1:
                im.putalpha(im.getchannel('A').point(lambda v: int(v * alpha)))
            canvas.paste(im, (int(W / 2 - im.width / 2 - 30), int(CAP_Y[where] - im.height / 2)), im)


# ---------- end card ----------

@lru_cache(maxsize=1)
def end_bg():
    img = phone(PH_FILES[0]).crop((0, 0, 1080, 1920)).filter(ImageFilter.GaussianBlur(22))
    dark = Image.new('RGB', img.size, NIGHT)
    return Image.blend(img, dark, 0.45)


def end_card(t):
    k = seg(t, T5, END)
    bg = end_bg()
    z = lerp(1.04, 1.12, k)
    big = bg.resize((int(W * z), int(H * z)), Image.LANCZOS)
    canvas = big.crop(((big.width - W) // 2, (big.height - H) // 2, (big.width - W) // 2 + W, (big.height - H) // 2 + H))
    layer = Image.new('RGBA', (W, H), (0, 0, 0, 0))

    def slam(text, fnt, y, start, fill, x=96):
        a = expo(seg(t, start, start + 0.16))
        if a <= 0:
            return
        sc = lerp(1.22, 1.0, a)
        bb = fnt.getbbox(text)
        im = Image.new('RGBA', (bb[2] - bb[0] + 20, bb[3] - bb[1] + 30), (0, 0, 0, 0))
        ImageDraw.Draw(im).text((10 - bb[0], 10 - bb[1]), text, font=fnt, fill=fill)
        im = im.resize((int(im.width * sc), int(im.height * sc)), Image.LANCZOS)
        im.putalpha(im.getchannel('A').point(lambda v: int(v * a)))
        layer.paste(im, (int(x - (im.width - im.width / sc) / 2), int(y - (im.height - im.height / sc) / 2)), im)

    slam('Ramyar', NAME, 520, T5, LIME)
    slam('Salah', NAME, 735, T5 + BEAT, LIME)
    slam('websites · desktop apps', TAG, 1010, T5 + BEAT * 2, LIME)
    slam('custom business software', TAG, 1075, T5 + BEAT * 2.5, LIME)
    a = expo(seg(t, T5 + BEAT * 4, T5 + BEAT * 4 + 0.16))
    if a > 0:
        text = 'WhatsApp  0770 083 5557'
        bb = PILL.getbbox(text)
        pw, ph = bb[2] - bb[0] + 76, bb[3] - bb[1] + 48
        pill = Image.new('RGBA', (pw, ph), (0, 0, 0, 0))
        d = ImageDraw.Draw(pill)
        d.rounded_rectangle((0, 0, pw - 1, ph - 1), ph // 2, fill=EMBER + (255,))
        d.text((38 - bb[0], 24 - bb[1]), text, font=PILL, fill=NIGHT)
        sc = lerp(1.2, 1, a)
        pill = pill.resize((int(pw * sc), int(ph * sc)), Image.LANCZOS)
        pill.putalpha(pill.getchannel('A').point(lambda v: int(v * a)))
        layer.paste(pill, (96, 1200), pill)
    canvas.paste(layer, (0, 0), layer)
    return canvas


# ---------- timeline ----------

def flash(canvas, amount, color=(255, 255, 255)):
    if amount > 0:
        return Image.blend(canvas, Image.new('RGB', canvas.size, color), clamp(amount))
    return canvas


def frame(t):
    f = int(round(t * FPS))
    pulse = 1 + 0.025 * math.exp(-((t % BAR) / 0.09))      # small push on every bar line
    if t < T1 - 0.25:                                        # A: phone full-bleed (hook)
        c = Image.new('RGB', (W, H), NIGHT)
        z = lerp(1.0, 1.05, t / T1) * pulse
        wpx = 1080 * z
        phone_device(c, f, W / 2, -(1920 * z - 1920) / 2, wpx, full=True)
        c = flash(c, 0.55 * (1 - seg(t, 0, 0.22)))
    elif t < T1 + 0.3:                                       # A->C: phone shrinks away, desktop flies in
        k = inout(seg(t, T1 - 0.25, T1 + 0.3))
        c = background().copy()
        wpx = lerp(1080 * 1.05, 360, k)
        phone_device(c, f, lerp(W / 2, -260, k ** 1.6), lerp(-40, 760, k), wpx, alpha=1 - seg(k, 0.7, 1), full=k < 0.08)
        desktop_window(c, f, lerp(W + 40, 40, expo(seg(t, T1 - 0.05, T1 + 0.3))), 560, 1000, alpha=seg(t, T1 - 0.1, T1))
    elif t < T3 - 0.2:                                       # C: desktop
        c = background().copy()
        z = lerp(1.0, 1.05, seg(t, T1, T3)) * pulse
        wpx = 1000 * z
        desktop_window(c, f, W / 2 - wpx / 2, 560 - (z - 1) * 300, wpx)
    elif t < T3 + 0.3:                                       # C->D: desktop rises, phone arrives
        k = inout(seg(t, T3 - 0.2, T3 + 0.3))
        c = background().copy()
        desktop_window(c, f, W / 2 - 500, lerp(560, 150, k), 1000)
        phone_device(c, f, W / 2, lerp(H + 60, 1000, expo(seg(t, T3 - 0.05, T3 + 0.3))), 340)
    elif t < T4 - 0.2:                                       # D: both screens, same night
        c = background().copy()
        desktop_window(c, f, W / 2 - 500, 150, 1000 * pulse if False else 1000)
        phone_device(c, f, W / 2, 1000, 340 * pulse)
    elif t < T4 + 0.25:                                      # D->E: phone grows to full screen
        k = inout(seg(t, T4 - 0.2, T4 + 0.25))
        c = background().copy()
        desktop_window(c, f, W / 2 - 500, lerp(150, -900, k), 1000, alpha=1 - k)
        wpx = lerp(340, 1080, k)
        crop = crop_for_tap()
        phone_device(c, f, W / 2, lerp(1000, -crop * (wpx / 1080), k), wpx, full=k > 0.98)
    elif t < T5:                                             # E: phone full, tap WhatsApp
        c = Image.new('RGB', (W, H), NIGHT)
        crop = crop_for_tap()
        phone_device(c, f, W / 2, 0, 1080 * pulse, crop_top=crop, full=True)
    else:                                                    # F: end card
        c = end_card(t)
        c = flash(c, 0.6 * (1 - seg(t, T5, T5 + 0.2)))
        c = flash(c, 0.9 * (1 - seg(t, T5 + BEAT * 6, T5 + BEAT * 6 + 0.12)) * (t >= T5 + BEAT * 6))
    caption(c, t)
    return c


@lru_cache(maxsize=1)
def crop_for_tap():
    taps = [r for f, r in PH_PTR.items() if r.get('kind') == 'touch' and f > T4 * FPS]
    if not taps:
        return 418
    y = taps[0]['y'] * 2.77
    return int(clamp(y - 1150, 0, 418))


TRANSITIONS = [(T1 - 0.25, T1 + 0.3), (T3 - 0.2, T3 + 0.3), (T4 - 0.2, T4 + 0.25)]

if __name__ == '__main__':
    os.makedirs(OUT, exist_ok=True)
    first = int(sys.argv[2]) if len(sys.argv) > 2 else 0
    last = int(sys.argv[3]) if len(sys.argv) > 3 else N - 1
    for fi in range(first, last + 1):
        t = fi / FPS
        if any(a <= t <= b for a, b in TRANSITIONS):
            # motion blur: average sub-frames across the shutter
            import numpy as np
            subs = [np.asarray(frame(t + d), dtype=np.float32) for d in (-0.008, -0.004, 0, 0.004, 0.008)]
            img = Image.fromarray(np.clip(sum(subs) / len(subs), 0, 255).astype(np.uint8))
        else:
            img = frame(t)
        img.save(os.path.join(OUT, f'{fi:04d}.png'), compress_level=1)
        if fi % 30 == 0:
            print('composed', fi, flush=True)
