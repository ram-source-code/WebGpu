# Ramyar Salah — portfolio site

Dev-only file. Never upload it (the `.htaccess` also refuses to serve `*.md`).

A server-rendered PHP portfolio with a live WebGPU scene: a Zagros ridge on the night of Newroz, with a torch procession climbing switchbacks to a summit fire. Scroll is the hour (dusk → first light). The scene falls back to WebGL2, then to a still image; every page works without JavaScript.

## Requirements

- PHP 8.4 or 8.5 on the host (8.3 works too; this build was tested on 8.3.6, see STATUS.md). Extensions: `pdo_sqlite`, `mbstring`, `intl` (optional: Unicode normalisation), `openssl` (only if SMTP is used).
- Apache or LiteSpeed with `.htaccess` (`mod_rewrite`, `mod_headers`, `mod_deflate` on Apache). PHP's built-in server ignores `.htaccess`: use XAMPP/Apache locally.
- No Node, no build step, no CDN.

## First run

```sh
cd site
php includes/cli/setup-env.php      # writes .env with fresh random keys; never overwrites
```

Open `.env` and fill every `[CLIENT: …]` in `[production]`. The site refuses to start (styled 500, log line naming the key) while one is left. `[local]` is chosen automatically when the folder path matches its `when` globs (`*/htdocs/*` covers XAMPP); add your own folder if you build elsewhere.

`data/` is created on first request. It must be writable by PHP and is blocked from the web.

## File map

```text
site/
  index.php              the only PHP file the web can reach (front controller)
  .htaccess              routing, blocks, security headers, caching, compression
  .env / .env.example    settings + secrets for every environment (.env is never committed)
  robots.txt, sitemap    generated routes (includes/pages/robots.php, sitemap.php)
  favicon.ico icon.svg apple-touch-icon.png og-default.jpg
  assets/css/main.css    all styles (tokens at the top)
  assets/js/scene.js     entry: starts the scene
  assets/js/scene-core.js     terrain, camera, input, loop, fallbacks
  assets/js/scene-webgpu.js   WebGPU: compute pass + render pass (WGSL)
  assets/js/scene-webgl.js    WebGL2 fallback (GLSL)
  assets/js/site.js      copy-email, form error announcements
  assets/img/            posters (AVIF/WebP), icon sprite, ridge edge, arrow mask
  assets/fonts/          Anybody + Atkinson Hyperlegible Next (WOFF2) + OFL.txt
  includes/bootstrap.php fail-closed handler, CSP nonce, .env, canonical redirect, maintenance
  includes/routes.php    every URL, one line each
  includes/functions.php helpers: url(), asset(), e(), route(), load_env(), open_db(), rate_limit(), og_tags(), smtp_send()
  includes/content.php   the portfolio entries  ← edit projects here
  includes/pages/        one file per route     ← edit page copy here
  includes/partials/     layout.php, scene-band.php
  includes/errors/       403 404 405 429 500 503
  includes/cli/          messages.php (owner tool), setup-env.php
  data/                  site.sqlite, php-error.log, maintenance.flag, backups/  (state, never overwritten by a deploy)
```

## Where content lives

- Projects: `includes/content.php`. Set `placeholder` to `false` and fill `title`, `year`, `role`, `summary`, `stack` once a project is real. A placeholder project is `noindex` and stays out of the sitemap. Its case-study body is in the placeholder branch of `includes/pages/project.php`.
- Copy: `includes/pages/*.php`. Every `client('…')` call renders a visible `[CLIENT: …]` slot; search for `client(` to find them all.
- Contact email shown on the site: `CONTACT_EMAIL` in `.env`.

## Contact messages (no admin panel by design)

```sh
php includes/cli/messages.php list
php includes/cli/messages.php show 12
php includes/cli/messages.php delete 12
php includes/cli/messages.php forget person@example.com   # privacy request: delete one person's data
php includes/cli/messages.php export > messages.json
php includes/cli/messages.php backup                      # VACUUM INTO data/backups/
```

On shared hosting without SSH, run them through hPanel's cron ("run once") or ask for a copy of `data/site.sqlite` via the File Manager *after* a `backup` (never copy the live file).
Email notifications: fill the `SMTP_*` and `MAIL_*` keys. Leave `SMTP_HOST = ""` to only save messages. If SMTP fails, the message is still saved (`mailed = 0`).
Messages older than 12 months are deleted automatically on each new message (privacy page promise).

## Deploy (Hostinger / any Apache or LiteSpeed host)

1. Zip the live site files and run `php includes/cli/messages.php backup` (rollback copy).
2. Maintenance on: create `data/maintenance.flag` (visitors get 503 + `Retry-After`). Set a cookie `maint=<MAINT_KEY of production>` in your browser to bypass it.
3. Upload the code (SFTP/File Manager) into `public_html/` or any subfolder. Never upload `data/`, and don't overwrite the server's `.env` unless you changed it.
4. Upload nothing from this list: `README.md`, `CREDITS.md`, `STATUS.md`, `PRODUCT.md`, `DESIGN.md`, `.impeccable/`, `.claude/`, the spec, zips, backups.
5. Migrations run by themselves on the first request (`PRAGMA user_version`).
6. Smoke test with the bypass cookie: home, a case study, the contact form, a 404.
7. Clear LiteSpeed cache (hPanel), then delete `data/maintenance.flag`.

Rollback = restore the zip + `data/site.sqlite` from the backup.

Permissions on the server: files 644, folders 755, `.env` 600, `data/` writable by PHP only.
Subfolder deploys work unchanged (BASE is detected). The host root still owns `/robots.txt` and `/favicon.ico`: copy this site's robots lines into the root file.

Canonical host: pages 301 to `https://CANONICAL_HOST` in PHP. To also redirect static files, uncomment the block in `.htaccess` and put the real host in it.

## Backups and restore

- Backup: `php includes/cli/messages.php backup` → `data/backups/YYYYMMDD-HHMMSS.sqlite` (consistent, WAL-safe). Keep one copy off the server.
- Restore: maintenance on, replace `data/site.sqlite` with the backup file (delete any `-wal`/`-shm` next to it), maintenance off.
- A backup counts only after a test restore (see STATUS.md for the one done during the build).
- Suggested schedule (no `backup_targets` given in the brief): weekly hPanel cron running the backup command, keep the last 8.

## DNS sheet (fill at launch)

| Record | Value |
|---|---|
| A / AAAA | `[CLIENT: host IP]` |
| MX | `[CLIENT: mail provider]` |
| SPF (TXT) | `v=spf1 include:[CLIENT: provider] ~all` |
| DKIM | from the mail provider |
| DMARC (TXT `_dmarc`) | `v=DMARC1; p=quarantine; rua=mailto:[CLIENT]` |
| CAA (optional) | `0 issue "letsencrypt.org"` (must include the host's CA) |

## Monitoring and maintenance

- Uptime: a free external monitor (5-minute checks) on `/` emailing the owner. SSL and domain expiry alerts on; domain auto-renew + registrar lock on.
- Monthly: read `data/php-error.log`, click through every page, check the PHP version in hPanel (8.2 loses security support 31 Dec 2026), run a backup and a test restore.
- After each browser release that matters (Chrome/Safari): open the home page and confirm the status line says WebGPU.

## Incident steps

Rotate `FORM_KEY`, `MAINT_KEY` and the SMTP password in `.env`; restore the last good backup; tell the people whose messages were affected.

## Libraries

None. Fonts: Anybody (Etcetera Type Co) and Atkinson Hyperlegible Next (Braille Institute), both SIL Open Font License 1.1, self-hosted, license in `assets/fonts/OFL.txt`.

## Ownership

The owner (Ramyar Salah) owns the content and the site. Reusable snippets from the build spec stay the developer's. Confirm in the contract.
