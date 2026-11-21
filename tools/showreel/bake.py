# Bake the realistic fingertip / cursor into the raw site captures, write PNGs for ffmpeg.
import sys, os
sys.argv = ['x', '/dev/null']
exec(open('compose3.py').read().split('# ---------- frames ----------')[0])
from PIL import Image
mode, out = os.environ['MODE'], os.environ['OUT']
os.makedirs(out, exist_ok=True)
fl = PF if mode == 'p' else DF
for f in fl:
    if mode == 'p':
        c = img(P_DIR, f).copy()
        fingertip(c, f, 0, 0, 1.0)
    else:
        c = img(D_DIR, f).copy()
        rec = DP.get(f)
        if rec and 'x' in rec:
            cursor(c, rec['x'] * 2, rec['y'] * 2, 2.0)
    c.save(f'{out}/{f:04d}.png', compress_level=1)
