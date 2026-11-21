# Mix the Mixkit sound effects onto the 140 BPM grid of the video. python3 mix.py out.wav
import sys, wave
import numpy as np

SR = 48000
BEAT = 60 / 140
BAR = BEAT * 4
T1, T3, T4, T5 = BAR * 2, BAR * 5, BAR * 7, BAR * 8
END = BAR * 10
import os
D = os.environ.get('SFX_DIR', 'sfx') + '/'


def load(name):
    with wave.open(D + name + '.wav') as w:
        a = np.frombuffer(w.readframes(w.getnframes()), dtype=np.int16).reshape(-1, 2).astype(np.float32) / 32768
    return a


def lead_in(a, thresh=0.05):
    """seconds from file start to its loudest transient (to land peaks on the beat)."""
    env = np.abs(a).max(axis=1)
    return int(np.argmax(env)) / SR


# (time the hit should LAND, sound, gain, align-to-peak?)
EVENTS = [
    (0.0, 'impact-zoom', 0.9, True),
    (0.0, 'bass-pulse', 0.35, False),
    (0.30, 'click-classic', 0.7, False),
    (BAR, 'whoosh-arrow', 0.55, True),
    (T1, 'whoosh-fast', 0.9, True),
    (T1, 'impact-blow', 0.7, True),
    (T1 + BAR * 0.5, 'click-double', 0.25, False),
    (T1 + BAR * 1.5, 'sweep-small', 0.5, True),
    (T1 + BAR * 2.4, 'whoosh-tunnel', 0.35, False),
    (T3, 'whoosh-cine', 0.9, True),
    (T3, 'impact-deep', 0.85, True),
    (T3 + BAR, 'whoosh-stutter', 0.45, True),
    (T4, 'whoosh-electric', 0.6, True),
    (T4, 'impact-zoom', 0.7, True),
    (T4 + BEAT * 1, 'click-classic', 0.8, False),
    (T5, 'impact-big', 0.95, True),
    (T5, 'bass-rumble', 0.4, False),
    (T5 + BEAT, 'impact-heartbeat', 0.5, True),
    (T5 + BEAT * 4, 'click-double', 0.45, False),
    (T5 + BEAT * 6, 'shutter', 0.8, True),
]

out = np.zeros((int(END * SR) + SR, 2), np.float32)
for t, name, gain, align in EVENTS:
    a = load(name)
    start = t - (lead_in(a) if align else 0)
    s = int(round(start * SR))
    if s < 0:
        a = a[-s:]
        s = 0
    n = min(len(a), len(out) - s)
    out[s:s + n] += a[:n] * gain
out = out[: int(END * SR)]
# short fade out at the very end, then normalise peak to -1 dBFS (the encoder adds a limiter + loudness pass)
fade = int(0.25 * SR)
out[-fade:] *= np.linspace(1, 0, fade)[:, None]
out *= 0.89 / max(1e-6, np.abs(out).max())
with wave.open(sys.argv[1], 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR)
    w.writeframes((out * 32767).astype(np.int16).tobytes())
print('mixed', len(out) / SR, 's')
