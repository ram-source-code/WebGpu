# Product

<!-- impeccable:product-schema 1 -->

> Init ran unattended: the owner asked for no questions ("go on, no asking"). Every line marked *(inferred)* comes from the request, the GitHub account `ram-source-code` (display name "Ramyar") and the build spec's regional defaults, not from an interview. Confirm or correct these before launch.

## Platform

web

## Stack

delegated: PHP 8.4+ front controller, vanilla HTML/CSS/JS, raw WebGPU (WGSL) with a WebGL2 and a static fallback, SQLite for contact messages, Apache/LiteSpeed `.htaccess`. No build step, no CDN, no framework (the owner's WEBSITE-BUILD-SPEC.md pins this; the owner said "if backend needed stay in php").

## Users

- Primary *(inferred)*: people deciding whether to hire or collaborate with Ramyar Salah: studio leads, agency producers, product teams, and recruiters. They arrive from a shared link (WhatsApp, Telegram, LinkedIn, GitHub) and most open it first on a phone, often on a mid-range Android on mobile data in Iraq/KRG, later on a desktop to look closer.
- Their job: in under a minute, decide whether Ramyar can build the kind of real-time 3D web work they need, then get in touch.

## Product Purpose

A personal portfolio for Ramyar Salah. Success = a qualified visitor sends a message (contact form) or copies the email. The site is also the first exhibit: it has to *be* a modern WebGPU site, not describe one.

## Positioning

The portfolio itself is the demonstration: a GPU-computed scene running in the visitor's own browser, on their own phone, with fallbacks that still look intended. A neighbouring portfolio can claim "WebGPU"; this one proves it in the first viewport and degrades honestly.

## Operating Context

- Links are shared in chat apps; the link preview (OG image) is the first impression.
- Visitors on phones with weak networks; WebGPU availability varies (Chrome/Edge, Safari 26+, Firefox on some platforms). A visitor without WebGPU must still get a complete, designed page.
- *(inferred)* Based in the Kurdistan Region of Iraq; timezone Asia/Baghdad.

## Capabilities and Constraints

- Pages: home, work index, case-study pages, about, contact, privacy, error pages.
- Contact form: name, email, message; stored in SQLite; optional SMTP notification. Rate-limited, honeypot, minimum fill time.
- English only for launch *(inferred)*; structure keeps a Kurdish (Sorani, RTL) version possible later.
- Undecided: real project list, bio, photo, email address, social profiles, domain. All are `[CLIENT: …]` placeholders.

## Brand Commitments

None given. Name: Ramyar Salah.

## Evidence on Hand

- No real projects, clients, testimonials, metrics or photos were supplied. None may be invented. Case studies carry structure and `[CLIENT: …]` placeholders; the only demonstrable work on the site is the site's own WebGPU scene, which is real.
- GitHub account: `ram-source-code` (repository `WebGpu`).

## Product Principles

1. Prove, don't claim: the GPU scene is the credential; copy never says "cutting-edge".
2. Every visitor gets a finished page: WebGPU, WebGL2, or no GPU at all.
3. Phone first: the 360 px viewport on mobile data is the primary case, not an afterthought.
4. One action: get in touch.

## Accessibility & Inclusion

WCAG 2.2 AA (the owner's spec). Motion is decorative: everything works with `prefers-reduced-motion`, with JS off, and with the canvas absent.
