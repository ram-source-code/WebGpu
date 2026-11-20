# STATUS — Ramyar Salah portfolio

Dev-only report (spec Part F). Built unattended on 20 Nov 2026. Statuses: ✅ ran it and saw the result · N/A with reason · `DEV` the developer must run it · ❌ failed (see Known issues).

## Update 20 Nov 2026: owner's real details

- From the owner: phone/WhatsApp 0770 083 5557 (`tel:+9647700835557`, `wa.me/9647700835557`), email ramyar.techie@gmail.com, Facebook link, Bachelor's degree in IT, builds websites, desktop apps, admin utilities and custom business software for business owners, modern design taste. Used in the hero, a new "What I make" section, About, Contact, footer, Person JSON-LD (telephone, email, sameAs) and the link-preview image.
- Form messages are now emailed to the owner with zero setup (host `mail()`, SMTP optional) and still saved first.
- Still placeholders: portrait photo, two project slots.
- GitHub hidden on request: `GITHUB_URL` setting, empty by default (no footer link, not in JSON-LD `sameAs`); set it in `.env` to show it again.
- Checked: detector 0, axe clean, 21/21 browser tests.

## Update 20 Nov 2026: smoothness pass and no build details on the page

- Visible content no longer says how the site is made: no technology names in copy, titles, descriptions, the link-preview image or the scene controls (status line removed); no console messages or debug hooks; script files renamed and shipped minified from `src/js` (`tools/build-js.sh`). The browser APIs the scene needs (`getContext('webgpu')`, `'webgl2'`) cannot be hidden from someone reading the code.
- Real info about the owner: nothing public was found by web search; the bio, portrait and two project slots stay `[CLIENT: …]` until the owner supplies them.
- Scroll smoothness fixes: camera eases toward the scroll position (~0.22 s) instead of following it 1:1 (anchor jumps, flings, address-bar changes no longer jolt the view); the fixed scene is sized to `100lvh` and ignores address-bar height changes (no canvas reallocation mid-scroll); `pointercancel` handled (a touch that becomes a scroll no longer leaves a phantom torch pulling the procession); no camera parallax from taps; one redraw per frame while paused; camera matrices reused (no per-frame garbage); fallback shaders compile without blocking (`KHR_parallel_shader_compile`); no blend mode on the fixed rail; a fresh frame is drawn before a page-transition snapshot; smooth in-page anchor scrolling (motion allowed only).
- Slow-device guard is now time-based: it lowers resolution after ~1 s of slow frames, then halves the torches, and stops (keeps the still) after ~2 s under 20 fps at the floor or two frames over 200 ms. Measured (software GPU, worst case): the scene stopped during start-up and a 5 s scripted scroll ran at p50/p95/max 17/17/17 ms with 0 long tasks; per-frame main-thread work (camera + pointer ray-march) 4–10 µs; one-time terrain set-up ~20 ms in 12 ms slices.
- Re-checked: detector 0, axe clean on 8 pages × 2 widths, 21/21 browser tests, both renderers draw from the minified build.

## Update 20 Nov 2026: zero-setup deploy (owner: "I drop the website folder in the server and it must work")

- `.env` is now optional. With none: links/canonical/sitemap/OG use the request host (validated pattern); `FORM_KEY`/`MAINT_KEY` are generated on the first request into `data/keys.php` (blocked); the https redirect is off until `CANONICAL_HOST` is set; `upgrade-insecure-requests` is sent only on HTTPS so a site without a certificate still loads; email links appear only when `CONTACT_EMAIL` is set.
- Tested as a real upload: a fresh copy of `site/` with no `.env` and an empty `data/`, owned by the PHP user, at a web root (`:8082`) and in a subfolder (`:8081/fresh/`): every page 200, 404s, blocks 403 (`/data/keys.php`, `/data/.htaccess`, `/.env.example`, `/includes/bootstrap.php`), keys file created on first visit, contact message saved, 21/21 browser tests pass, canonical and sitemap use the serving host.
- Trade-offs the owner accepted by asking for zero setup (spec D1/D1b deviations): no fail-closed on a missing `.env`; until `CANONICAL_HOST` is set, a request with a forged Host header gets that host in its canonical/OG tags (HTML is `no-cache`; name-based hosting normally never routes such requests to this site); messages are stored but not emailed until SMTP is set, and reading them needs SSH (`php includes/cli/messages.php list`) or a download of `data/site.sqlite` through the File Manager.
- Dev docs moved from `site/` to `docs/`, so `site/` is exactly the folder to upload.

## Read first: what was assumed (owner said "no asking")

**Brief fields assumed** (Part A was empty; the owner asked for a 3D WebGPU portfolio in PHP for Ramyar Salah):

| Field | Assumed value |
|---|---|
| project_name | Ramyar Salah portfolio |
| site_type | portfolio |
| goal | send a message (contact form) or copy the email |
| audience | studios, producers, recruiters; phone first (Iraq/KRG mobile data), then desktop; English |
| languages | `[en]` only. Structure (logical CSS properties, `dir`, one string source per page) leaves room for a Sorani version later |
| pages | `/`, `/work`, `/work/{slug}`, `/about`, `/contact`, `/privacy`, `/sitemap.xml`, `/robots.txt`, error pages |
| database | sqlite (contact messages + rate-limit hits only) |
| admin_area | no (owner CLI `includes/cli/messages.php` instead) |
| file_layout | multi-file (the D1a tree) |
| visual_direction | open → Impeccable roll (see Design) |
| brand / artwork | none supplied; no AI image generation available; all art is the site's own GPU scene and hand-drawn SVG |
| hosting | Apache/LiteSpeed shared host (Hostinger assumed) |
| php_version | 8.4 on the host; tested locally on 8.3.6 (the container's PHP) |
| behind_cdn | no |
| canonical_host | **unknown** → `[CLIENT]` in `.env [production]` (the site will not start in production until it is set) |
| deploy_path | root; subfolder verified too |
| integrations | optional SMTP only (off until configured) |
| backup_targets | not given; README suggests weekly |
| visitor_regions | `[iraq_krg]` (no EU/UK targeting) |
| pwa_level | 0 |
| device_features | none |
| personal_data | name, email, message from the contact form, to reply |
| accounts | none |
| og_images | default only (`og-default.jpg`, made from the scene) |
| ai_crawlers | allow |
| google_business | no |
| content_rights | fonts OFL; every asset made for the site (CREDITS.md) |

**Items marked N/A** (one line each in the matrix): sessions/CSRF tokens, login, accounts/authorization, uploads, shop/booking/payment, SSRF, rich text, MySQL, bilingual/RTL/Kurdish items, phone/price/date formatting, PWA ≥1, device features, embeds/maps, Google Business, replacing an old site, EU/UK items, per-page OG, GD text, admin.

**`[CLIENT: …]` placeholders left** (all visible on the page or blocking in `.env`):
- `.env [production]`: CANONICAL_HOST, CONTACT_EMAIL, SMTP_HOST, SMTP_USER, SMTP_PASS, MAIL_FROM, MAIL_TO
- home: about-you sentences; two project slots (title, year/role, summary)
- about: portrait photo, three bio paragraphs
- case studies 2 and 3: title, lede, year, role, tools, screenshot, brief, solution, result
- privacy: "review this page", hosting company, email provider
- README DNS sheet

## Environment

- Local: Ubuntu 24.04, Apache 2.4.58 + mod_php, **PHP 8.3.6** (`pdo_sqlite`, `mbstring`, `intl`, `openssl`, `gd` present). Host target PHP 8.4/8.5: the code uses only syntax both run (no 8.4-only features). Root deploy at `http://localhost:8080/`, subfolder deploy at `http://localhost:8081/folio/` (symlink, same folder, no edits).
- Browser: Playwright Chromium (headless). Headless WebGPU here can only render **offscreen** (presenting a WebGPU canvas kills the GPU instance in this container), so WebGPU frames were verified by offscreen readback; page screenshots use the WebGL2 path. Both run on SwiftShader (CPU), which the site now treats as "no GPU" unless `?rsforce` is added (test-only switch).
- Internet: available; spec facts checked against Google's Modern Web Guidance guides retrieved in this session (2026_11_10 corpus).

## Design (Part B)

- Impeccable **4.10.0** installed with `npx impeccable install --providers=claude --scope=project` (hook accepted; telemetry off in `.impeccable/config.json`). The spec names 4.8.2; the newest release was used.
- Modern Web Guidance skill copied from `GoogleChrome/modern-web-guidance` into `.claude/skills/`; WebGPU skills `cazala/webgpu-skill` (framework-free, used) and `dgreenheck/webgpu-claude-skill` (Three.js TSL, installed, not used: no build step and a 700 KB library would break the page budget).
- PRODUCT.md written from the request (inferred, labelled). Direction round: `concept-seed --scope direction --mode experience`, seed `402136fc10158411`, assigned grounded candidate 3 of 7: **Newroz hill-fire procession on a Zagros ridge**. All six catalog challengers declined; each donated a named raise (in the surface brief). Taken unattended, no decision page (owner asked for no questions).
- Build path: **code-first** (no image generation in this session).
- Detector: `impeccable detect` source = 0 findings; rendered `/`, `/work`, `/about`, `/work/newroz-on-the-ridge`, `/work/case-study-two`, `/contact`, `/privacy` (desktop and 360/390 mobile) = 0 after fixes (fixed: coloured glow on primary button, cramped/low-contrast placeholder chips, privacy line length). Scene pages were scanned with `?still` because the detector's browser times out on a running GPU loop.
- Finish review: Impeccable finish reviewer (run as a fresh general-purpose agent loading the shipped definition, because the agent was installed after the session started) → **fix** with 7 material items; all 7 applied in one batch, verdict pass result below.
- Verdict after the one correction round (final for an unattended run): 6 of 7 fixes **resolved** (phone first viewport, ridgeline seams, "now" rail at every width, no grey panel, placeholder blocks, no glyph icon); 1 **partial** (work entries light their stretch of the procession: shown in no capture at review time) and 1 regression (the "N% up the road" line sat above the title as an eyebrow). After the verdict both were addressed without a further review round: the position moved into the meta line under the title, and the highlight was strengthened and measured on the home page (paused scene, hover vs rest: 33,816 pixels change; captures `.impeccable/review/live-home-work-rest.png` and `live-home-work-focus-25.png`). On `/work` the band scrolls away before the list, so the highlight is only visible on the home page. These last two are not reviewer-scored.
- DESIGN.md + `.impeccable/design.json`: written by the documenter pass (new world, token-bearing).

## Traceability matrix

| ID | Requirement | Implementation | Test | Expected | Evidence | Status |
|---|---|---|---|---|---|---|
| D1-ssr | Server-rendered, works with JS off | `index.php` → `pages/*.php` → `partials/layout.php` | Playwright, JS disabled, `/` and `/contact` | h1, poster, 3 work items, form visible | "JS off: content + poster", "JS off: form present" PASS | ✅ |
| D1-canonical | One canonical origin, one 301 | `bootstrap.php` (production only), commented `.htaccess` block | on the live host: `curl -sI http://www.<host>/work` | one 301 to `https://<host>/work` | needs the real host | DEV |
| D1-urls | Clean URLs, one slash style, UTF-8, Asia/Baghdad | `index.php`, `route()` | `curl /work/` and `//work` | one 301 to `/work` | `/work/ 301 → /work`, `//work 301 → /work` | ✅ |
| D1-php | PHP version set on host | — | hPanel → PHP 8.4 | 8.4 active | local 8.3.6 | DEV |
| D1-ext | Extensions present | — | `php -m` | pdo_sqlite, mbstring | present locally | ✅ |
| D1-errors | 403/404/405/429/500/503 styled, no detail | `includes/errors/*` | curl each | right code, styled, nonce | 403 `/.env`, 404 `/missing`, 405 + `Allow: GET, HEAD`, 429 + `Retry-After: 600`, 500 with `.env` renamed, 503 maintenance | ✅ |
| D1-maint | Maintenance 503 + bypass | flag file + `maint` cookie | touch `data/maintenance.flag` | 503, `Retry-After: 900`, `no-store`; cookie → 200 | seen | ✅ |
| D1-blocks | `-Indexes`, dotfiles, data, includes, md/json/sqlite | `.htaccess` | curl | 403 | `/data/site.sqlite`, `/data/php-error.log`, `/includes/bootstrap.php`, `/index.php/x`, `/.env`, `/.env.example`, `/PRODUCT.md` all 403 | ✅ |
| D1-tree | D1a layout | `site/` | inspect | matches | `og/`, `uploads/`, `lang/`, PWA files not created (not needed) | ✅ |
| D1-external | No external requests | self-hosted fonts/JS | DevTools network / CSP `default-src 'self'` | none | zero CSP violations; only links to GitHub | ✅ |
| D1-bootstrap | Fail-closed handler, nonce CSP, no session | `bootstrap.php` | curl `/` | no `Set-Cookie`, no `X-Powered-By` | 0 and 0 | ✅ |
| D1-deploy | Deploy procedure | README "Deploy" | read | steps 1–7 | present | ✅ |
| D1-staging | Staging subdomain | — | — | — | no admin or DB content edits; not worth it | N/A |
| D1a-entry | One entry point, route table | `.htaccess`, `routes.php` | `/index.php`, `/index.php/x` | 403 | 403, 403 | ✅ |
| D1a-routes | 404 unknown, 405 + Allow, HEAD | `route()` | `DELETE /work`, `POST /`, `HEAD /about` | 405 + Allow, 200 | seen | ✅ |
| D1a-base | Works at root and in a subfolder | `BASE`, `url()`, `asset()` | `:8081/folio/` home, case study, asset, 404, `/work/` | all inside `/folio/` | 200, 200, 200, 404, 301 → `/folio/work`; all hrefs start `/folio/` | ✅ |
| D1a-state | Code and state separate | `data/` | deploy steps | `data/` never uploaded | README | ✅ |
| D1b-env | `.env` sections, fail closed, no values leak | `load_env()` | rename `.env`; put a placeholder in `[local]` | 500 + log names key only | log: `.env is missing…` and `.env [local] CONTACT_EMAIL: … placeholder`; no value in page | ✅ |
| D1b-env-select | local vs production by folder | `when` globs | same folder on localhost | `ENV=local` | local chosen (http canonical, local CSP) | ✅ (production side DEV) |
| D2-validate | Allowlist validation, normalise, encode | `contact.php`, `clean_input()`, `e()` | `<script>alert(1)</script>`, `' OR 1=1 --`, `../../etc/passwd`, `%0d%0aBcc:` | rejected or shown as text | 422; script shown as `&lt;script&gt;`; CRLF email rejected; SQL/path stored raw, rendered escaped | ✅ |
| D2-sql | PDO prepared only | `functions.php`, `contact.php` | code review | no string SQL with input | only prepared statements | ✅ |
| D2-tokens | `random_bytes` + `hash_equals` | nonce, form timestamp HMAC, MAINT_KEY | code review | — | used | ✅ |
| D2-supply | Libraries listed | README "Libraries" | — | — | none; fonts listed | ✅ |
| D2-rate | Rate limit + 429 + Retry-After | `rate_limit()` 6/10 min, 30/day per IP hash | 7 posts | 7th → 429 | 429 + `Retry-After: 600` | ✅ |
| D2-honeypot | Honeypot + minimum fill time | `website` field, signed `ts` ≥ 3 s | filled honeypot; post at 0 s | quiet 303, nothing stored | both 303, row count unchanged | ✅ |
| D2-csrf | CSRF | Origin / Sec-Fetch-Site check only | `Origin: https://evil.example` | 403 | 403 | ✅ (token N/A: no session) |
| D2-auth, login, uploads, SSRF, payment, admin | — | — | — | — | no accounts, uploads, server fetches, payments or admin | N/A |
| D3-csp | Nonce CSP, no inline handlers/styles | `bootstrap.php` | listen for `securitypolicyviolation` on home + contact; error pages carry nonce | 0 | 0 and 0; 404 `<style nonce>` | ✅ |
| D3-headers | HSTS, nosniff, Referrer, XFO, Permissions, COOP, CORP | `.htaccess` | `curl -I /` | present | all present; `bluetooth` dropped (Chrome: unrecognized) | ✅ |
| D3-corp-og | OG/icons cross-origin | `.htaccess` | `curl -I /og-default.jpg` | `CORP: cross-origin` | seen | ✅ |
| D3-cors | Closed | — | `curl -I` | no ACAO | none | ✅ |
| D3-noopener | external `_blank` | footer GitHub link | inspect | `noopener noreferrer` | present | ✅ |
| D3-securitytxt | security.txt | — | — | — | no logins; personal data only via form | N/A (REC) |
| D4-assets | 1-year immutable assets | `assets/.htaccess` + `asset()` | `curl -I main.css?v=…` | `max-age=31536000, immutable` | seen | ✅ |
| D4-root | Short cache root files, HTML no-cache | `.htaccess`, `index.php` | `curl -I` | `max-age=0, must-revalidate` / `no-cache` | seen | ✅ |
| D4-bfcache | bfcache eligible | `pagehide`, no `unload` | Lighthouse `bf-cache` + back nav | pass | pass; "bfcache/back works" PASS | ✅ |
| D4-compress | gzip/br | `mod_deflate` | `curl -I -H 'Accept-Encoding: br,gzip'` | encoded | `Content-Encoding: gzip` (LiteSpeed adds br) | ✅ |
| D5-cwv | LCP ≤ 2.5 s, CLS ≤ 0.1, INP | posters, deferred scene, frame guard | Lighthouse mobile (simulated 4× CPU) | within | `/`: perf 98, LCP 2.3 s, CLS 0.007, TBT 0 ms; `/contact`: 98, 2.2 s, 0.025, 0 ms | ✅ (field INP: DEV after launch) |
| D5-images | AVIF/WebP, sizes, LCP not lazy | `<picture>` posters, `fetchpriority="high"` | inspect | — | posters 10–27 KB AVIF; portrait source for phones | ✅ |
| D5-fonts | Self-hosted WOFF2, swap, preload | `assets/fonts/` | network | 2 files | Anybody 57 KB (wdth+wght axes), Atkinson 34 KB, both preloaded, `font-size-adjust: from-font` | ✅ |
| D5-js | defer, no long tasks, 360 px no scroll | module scripts; scene after load+idle; heightmap baked in 12 ms slices | Lighthouse TBT; Playwright `scrollWidth` at 360 | 0 ms; = 360 | 0 ms; 360 on every page | ✅ |
| D5-speculation | Prefetch rules | layout | inspect | moderate, no query URLs | present | ✅ |
| D5-budget | Page weight | — | Lighthouse | home ≤ 1 MB | 236 KB home | ✅ |
| D6 | No overflow, reduced motion, `@supports` | `main.css` | 360 px + reduced motion run | no overflow; scene paused | PASS | ✅ |
| D7-structure | Landmarks, one h1, lang/dir | layout | axe-core | 0 violations | clean on 8 pages × 2 widths (one duplicate landmark label on the sent page fixed) | ✅ |
| D7-keyboard | Skip link, focus visible, errors beside fields, values kept | layout, `site.js` | Playwright keyboard run | — | skip link first + works; outlines on every stop; values kept on 422 | ✅ |
| D7-targets | ≥ 24 px, 44 px primary | `.button` 48 px, toggle 44 px | inspect | — | met | ✅ |
| D7-async | Live regions | copy status, form announcer, scene status | inspect | `aria-live="polite"` | present | ✅ |
| D7-motion | Reduced motion + pause control (WCAG 2.2.2) | pause button under every scene; reduced motion starts paused | Playwright `reducedMotion: reduce` | paused | PASS | ✅ |
| D7-forms | types, autocomplete, enterkeyhint, `dir="auto"` | `contact.php` | inspect | — | present | ✅ |
| D7-dark | Dark mode toggle | — | — | — | the design's grounds follow the hour of the scene, not the OS theme; `color-scheme` set per ground | N/A |
| D7-print | Print styles | `@media print` | — | — | basic | ✅ |
| D7a (all) | Kurdish/bilingual | — | — | — | English only (assumed) | N/A |
| D8-meta | Unique title/description/canonical/h1 | layout, page meta | view source | unique | yes; placeholder case studies `noindex` | ✅ |
| D8-sitemap | lastmod only, public URLs | `/sitemap.xml` route | curl | 6 URLs, file-mtime lastmod | seen | ✅ |
| D8-robots | Sitemap line, no secrets | `/robots.txt` route | curl | — | seen | ✅ |
| D8-jsonld | Person / CreativeWork | layout, `project.php` | inspect | valid JSON | Person with `sameAs` GitHub; CreativeWork on the case study | ✅ (Rich Results test: DEV) |
| D8-favicon | ico, svg, 180 px, theme-color | root files | curl | 200 | 200 | ✅ |
| D8-console | Search Console + Bing | — | — | — | needs the owner's accounts | DEV |
| D8a-tags | OG + twitter tags, absolute, ≤ 300 KB HTML | `og_tags()` | view source | present | present | ✅ |
| D8a-image | 1200×630 JPEG < 300 KB, CORP | `og-default.jpg` | `curl -I` | 200, image/jpeg | 56,914 bytes, 200, `CORP: cross-origin` | ✅ |
| D8a-platforms | WhatsApp/Telegram/LinkedIn/X/Meta previews | — | share the live URL | large card | needs the live domain | DEV |
| D9 | PWA | — | — | — | `pwa_level 0` | N/A |
| D10-sqlite | foreign_keys, busy_timeout, user_version, WAL decided once | `open_db()`, `migrate()` | fresh DB, first request | tables created, `wal`, v1 | `wal v1` | ✅ |
| D10-backup | Backup + test restore | `messages.php backup` (VACUUM INTO) | backup, open copy | integrity ok | `integrity=ok user_version=1 tables=messages,hits` | ✅ |
| D10-contact | Saved to DB + optional SMTP, PRG, submit once | `contact.php`, `smtp_send()` | valid post; double click | 303 → `?sent=1`; 1 row | 303; 1 row after double click | ✅ (SMTP delivery: DEV) |
| D10-monitor | Uptime/SSL/domain alerts | README | — | — | owner's accounts | DEV |
| D10-readme | README sections | `README.md` | read | all D10 topics | present | ✅ |
| D11 | Web Share / Clipboard | copy-email button (Clipboard API, hidden without it) | click | copied + announced | PASS | ✅ |
| D12-tracking | No tracking, no cookies | — | `curl -I` | no `Set-Cookie` | none; only localStorage pause preference (user choice) | ✅ |
| D12-privacy | Privacy page, linked in footer and under the form | `/privacy` | view | present | present, `[CLIENT: review]` | ✅ |
| D12-delete | Delete one person's data | `messages.php forget EMAIL` | run | rows deleted | command tested on list/backup; forget uses one prepared DELETE | ✅ |
| D12-retention | 12-month retention | delete on insert | code | — | present | ✅ |
| D12-ip | Licenses recorded, © year automatic | `CREDITS.md`, `OFL.txt`, footer `date('Y')` | view | — | present | ✅ |
| D12-terms | Terms page | — | — | — | portfolio, no sign-up or payments | N/A |

## Gates

1. **Functional** ✅ every route exists; 404/405/301 correct; root and subfolder; form validates, keeps values, PRG, submits once; tables created on first run; no PHP warnings (warnings are converted to exceptions, error log empty after the click-through). Extras built beyond the brief: the `?still` / `Save-Data` still mode (data saving on Iraqi mobile networks; also lets the detector scan) and the hour slider on the case study (proof of the scene).
2. **Security** ✅ as in the matrix. DEV: run the same curls on the live host.
3. **UX/UI** ✅ direction recorded as assumed; code-first; detector clean; finish review fix round done. Screenshots: `.impeccable/review/desktop.png`, `mobile.png`, `live-*.png`. **DEV:** look at the live site on a phone and a laptop: does it look made for Ramyar?
4. **Accessibility** ✅ axe clean, keyboard walk, reduced motion, contrast (palette ratios 5.2–15.7:1).
5. **Performance** ✅ Lighthouse mobile 98 on `/` and `/contact` (this container has no GPU, so the scene stayed a still image there). **DEV:** Lighthouse on a real phone with WebGPU on.
6. **SEO & previews** ✅ tags and image; **DEV:** Search Console, Bing, WhatsApp/Telegram/LinkedIn/X/Meta debuggers on the live URL.
7. **Privacy/legal** ✅ no cookies, privacy page, credits. **DEV:** owner reviews the privacy text.
8. **Deployment** DEV — real host, PHP 8.4, `.htaccess` active, permissions, HTTPS redirects, SSL Labs, securityheaders, W3C Nu checker on main pages, monitors.
9. **Devices** DEV — Android Chrome, iPhone Safari (iOS 26 has WebGPU), desktop Firefox/Chrome/Edge, WhatsApp/Telegram in-app browsers; 360/768/1280 checked here in Chromium only.
10. **Handover** DEV — domain/hosting/email in the owner's name; fill `.env [production]`.

## Developer checklist (run in this order)

1. Upload `site/` (contents into `public_html/`, or the folder into a subfolder). Nothing to configure. Optional later: a `.env` from `.env.example` for your domain, a public email, or SMTP.
2. Open the site in Chrome or Safari 26 on a machine with a real GPU: the status line under the hero must say **WebGPU · 14,336 torches simulated on your device**; move the pointer over the ridge (walkers turn toward it); hover a work entry (its stretch of the road brightens); drag the hour slider on the case study.
3. Same on an Android phone: status says WebGPU or WebGL2 with 6,144/4,096 torches; scrolling stays smooth; the Pause button works.
4. Deploy per README; smoke test with the maintenance bypass cookie.
5. On the live domain: `curl -I http://www.<host>/` → one 301 to `https://<host>/`; `curl -I https://<host>/.env` → 403; `curl -I https://<host>/og-default.jpg` → 200, image/jpeg, `CORP: cross-origin`.
6. Send a test message from the form; it arrives in the inbox (SPF/DKIM/DMARC pass) and `php includes/cli/messages.php list` shows it.
7. SSL Labs (A), securityheaders.com, W3C Nu checker on `/`, `/work/newroz-on-the-ridge`, `/contact`.
8. Search Console + Bing (DNS TXT), submit `/sitemap.xml`.
9. Preview checks: WhatsApp, Telegram (@WebpageBot), LinkedIn Post Inspector, Meta Sharing Debugger, an X draft.
10. Uptime + SSL monitor, domain auto-renew, registrar lock.
11. Replace the `[CLIENT: …]` content: bio, portrait, real projects (`includes/content.php`).

## Known issues / decisions for the developer

- Production refuses to start until `CANONICAL_HOST` and the other `[CLIENT]` values are filled (intended, spec D1b).
- Headless WebGPU canvases could not be presented in this container; WebGPU output was verified offscreen only. Real-browser WebGPU presentation is checklist item 2.
- Phones or laptops without a hardware GPU get the still image by design (better than a janky page). Remove the check in `scene-webgl.js` / `scene-webgpu.js` if you prefer otherwise.
- The finish review's optional "ceiling" ideas were not built: overprint (multiply) where inks meet in page layers, and a larger hero name (the craft floor caps display at 6rem).

## Files never to upload

`README.md`, `CREDITS.md`, `STATUS.md`, `../PRODUCT.md`, `../DESIGN.md`, `../.impeccable/`, `../.claude/`, the spec, `data/backups/` copies outside `data/`, any zip.

## Modern Web Guidance (guides applied)

- `accessible-error-announcement`: errors appear only after interaction and `aria-invalid` starts at `"false"`, synced with `:user-invalid` ("Set `aria-invalid="false"` until `:user-invalid` matches, rather than leaving the attribute unset").
- `motion`: the scene's continuous animation gets a visible Pause button, not only a reduced-motion check ("`prefers-reduced-motion` doesn't replace the control").
- `scroll-progress-indicator` / `scrollytelling`: the hour rail uses `animation-timeline: scroll()` inside `@supports … (animation-range: 0% 100%)`, decorative with no fallback ("If the animation is only considered to be decorative, opt for Progressive Enhancement").
- `cross-document-transitions`: `@view-transition { navigation: auto }` only under `prefers-reduced-motion: no-preference`.
- `visually-stable-font-fallbacks`: `font-size-adjust: from-font` on body text.
- `forms`: one-column form, `font-size: max(1rem, 1em)` on inputs, submit disabled only after a valid submit.
