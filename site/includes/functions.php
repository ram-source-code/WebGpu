<?php
declare(strict_types=1);

/* ---------- output encoding ---------- */

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_script(mixed $v): string
{
    return json_encode($v, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

/* ---------- URLs (spec E.a) ---------- */

// Pass paths un-encoded and site-relative; append "#fragment" after the call.
function url(string $path = '/', array $query = []): string
{
    $p = implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))));
    return BASE . '/' . $p . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
}

function abs_url(string $path = '/', array $query = []): string
{
    $https = CANONICAL_HOST !== '' && ENV !== 'local' ? true : is_https();
    return ($https ? 'https://' : 'http://') . site_host() . url($path, $query);
}

function asset(string $p): string
{
    $f = ROOT . '/' . ltrim($p, '/');
    return url($p) . '?v=' . (is_file($f) ? filemtime($f) : 0);
}

/** Inline SVG icon from the sprite; decorative unless a label is given. */
function icon(string $name, string $label = ''): string
{
    $a11y = $label === '' ? ' aria-hidden="true"' : ' role="img" aria-label="' . e($label) . '"';
    return '<svg class="icon" width="20" height="20"' . $a11y . '><use href="' . e(asset('assets/img/icons.svg')) . '#' . e($name) . '"/></svg>';
}

/* ---------- routing (spec E.i) ---------- */

function route(array $routes, string $method, string $path): array
{
    $allow = [];
    foreach ($routes as $def => $page) {
        [$methods, $pattern] = explode(' ', $def, 2);
        $re = '#^' . preg_replace('#\\\\\{(\w+)\\\\\}#', '(?<$1>[^/]+)', preg_quote($pattern, '#')) . '$#u';
        if (!preg_match($re, $path, $m)) continue;
        $list = explode('|', $methods);
        if (in_array('GET', $list, true) && !in_array('HEAD', $list, true)) $list[] = 'HEAD';
        if (in_array($method, explode('|', $methods), true)) {
            return [$page, array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY)];
        }
        array_push($allow, ...$list);
    }
    $allow ? error_page(405, ['Allow: ' . implode(', ', array_unique($allow))]) : error_page(404);
}

/* ---------- .env (spec E.j) ---------- */

/**
 * Settings. Everything is optional so the folder works the moment it is uploaded:
 * a .env file (see .env.example) only overrides these defaults. Returns 'local' or 'production'.
 */
const SETTING_DEFAULTS = [
    'SITE_NAME' => 'Ramyar Salah',
    'CANONICAL_HOST' => '',      // empty: use the host the site is served from
    'CONTACT_EMAIL' => 'ramyar.techie@gmail.com',      // shown on the site; receives form messages
    'CONTACT_PHONE' => '+9647700835557',               // E.164; also the WhatsApp number
    'FACEBOOK_URL'  => 'https://www.facebook.com/share/1JKBxYyGV9/',
    'GITHUB_URL'    => '',                              // hidden; set to https://github.com/ram-source-code to show it again
    'SMTP_HOST' => '', 'SMTP_PORT' => '587', 'SMTP_USER' => '', 'SMTP_PASS' => '', 'MAIL_FROM' => '', 'MAIL_TO' => '',
];

function load_env(string $file): string
{
    $name = 'production';
    $values = [];
    if (is_file($file)) {
        $ini = parse_ini_file($file, true, INI_SCANNER_RAW);
        if ($ini === false) throw new RuntimeException('.env is unreadable');
        $here = strtolower(str_replace('\\', '/', ROOT)) . '/';
        foreach ($ini as $section => $keys) {
            if (!is_array($keys) || !isset($keys['when'])) continue;
            foreach (explode(',', strtolower($keys['when'])) as $glob) {
                if (fnmatch(trim($glob), $here)) { $name = (string)$section; break 2; }
            }
        }
        $values = ($ini[$name] ?? []) + array_filter($ini, 'is_string');
        unset($values['when']);
    }
    foreach ($values as $key => $value) {
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', (string)$key) || str_contains((string)$value, '[CLIENT:')) {
            throw new RuntimeException(".env [$name] $key: bad name, or still a placeholder");   // the key, never the value
        }
    }
    foreach ($values + SETTING_DEFAULTS as $key => $value) defined($key) || define((string)$key, (string)$value);
    // secret keys: from .env if given, otherwise generated once and kept in data/ (blocked from the web)
    $keys = null;
    if (!isset($values['FORM_KEY'], $values['MAINT_KEY'])) {
        $kf = ROOT . '/data/keys.php';
        $keys = is_file($kf) ? include $kf : null;
        if (!is_array($keys) || empty($keys['FORM_KEY']) || empty($keys['MAINT_KEY'])) {
            $keys = ['FORM_KEY' => bin2hex(random_bytes(32)), 'MAINT_KEY' => bin2hex(random_bytes(16))];
            $tmp = $kf . '.' . bin2hex(random_bytes(4));
            if (@file_put_contents($tmp, "<?php return " . var_export($keys, true) . ";\n") !== false) {
                @chmod($tmp, 0600);
                @rename($tmp, $kf);
                $keys = include $kf;   // another request may have won the race: use the file's keys
            } else {
                error_log('data/ is not writable: keys could not be saved, the contact form will not work');
            }
        }
    }
    defined('FORM_KEY') || define('FORM_KEY', (string)$keys['FORM_KEY']);
    defined('MAINT_KEY') || define('MAINT_KEY', (string)$keys['MAINT_KEY']);
    return $name;
}

/** Is this request on HTTPS? Server variables only (forwarded headers can be faked). */
function is_https(): bool
{
    return (($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string)$_SERVER['HTTPS']) !== 'off') || (string)($_SERVER['SERVER_PORT'] ?? '') === '443';
}

/** The host links point at: CANONICAL_HOST when set, else the host this request came to (validated). */
function site_host(): string
{
    if (CANONICAL_HOST !== '') return CANONICAL_HOST;
    $h = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    if (preg_match('/^[a-z0-9]([a-z0-9.-]{0,251}[a-z0-9])?(:\d{1,5})?$/', $h)) return $h;
    $h = strtolower((string)($_SERVER['SERVER_NAME'] ?? 'localhost'));
    return preg_match('/^[a-z0-9.-]{1,253}$/', $h) ? $h : 'localhost';
}

/* ---------- SQLite (spec E.h) ---------- */

const MIGRATIONS = [
    1 => 'CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY,
            created_at TEXT NOT NULL,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            message TEXT NOT NULL,
            mailed INTEGER NOT NULL DEFAULT 0
        );
        CREATE INDEX IF NOT EXISTS messages_email ON messages (email);
        CREATE INDEX IF NOT EXISTS messages_created ON messages (created_at);
        CREATE TABLE IF NOT EXISTS hits (k TEXT NOT NULL, t INTEGER NOT NULL);
        CREATE INDEX IF NOT EXISTS hits_k_t ON hits (k, t);',
];

function migrate(PDO $db): void
{
    $db->exec('BEGIN IMMEDIATE');
    try {
        $v = (int)$db->query('PRAGMA user_version')->fetchColumn();
        foreach (MIGRATIONS as $n => $sql) {
            if ($n > $v) { $db->exec($sql); $db->exec("PRAGMA user_version = $n"); }
        }
        $db->exec('COMMIT');
    } catch (Throwable $e) {
        $db->exec('ROLLBACK');
        throw $e;
    }
}

function open_db(): PDO
{
    static $db = null;
    if ($db) return $db;
    $dir = ROOT . '/data';
    is_dir($dir) || mkdir($dir, 0700, true);
    $path = $dir . '/site.sqlite';
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    $db = new PDO('sqlite:' . $path, null, null, $opts);
    $db->exec('PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 5000;');
    $v = (int)$db->query('PRAGMA user_version')->fetchColumn();
    if ($v === 0) {
        $mode = $db->query('PRAGMA journal_mode = WAL')->fetchColumn();
        try {
            migrate($db);
        } catch (PDOException $e) {
            if ($mode !== 'wal') throw $e;
            $db = new PDO('sqlite:' . $path, null, null, $opts);
            $db->exec('PRAGMA journal_mode = DELETE; PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 5000;');
            migrate($db);
        }
    } elseif ($v < array_key_last(MIGRATIONS)) {
        migrate($db);
    }
    return $db;
}

/* ---------- rate limit (spec E.d) ---------- */

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';   // never forwarded headers (D2)
}

const RATE_KEEP = 86400;

function rate_limit(PDO $db, string $key, int $max, int $win): void
{
    $db->prepare('DELETE FROM hits WHERE t < ?')->execute([time() - RATE_KEEP]);
    // the IP is stored only as a keyed hash: the table never holds a readable address
    $k = $key . ':' . hash_hmac('sha256', client_ip(), FORM_KEY);
    $c = $db->prepare('SELECT COUNT(*) FROM hits WHERE k = ? AND t >= ?');
    $c->execute([$k, time() - $win]);
    if ((int)$c->fetchColumn() >= $max) error_page(429, ["Retry-After: $win"]);
    $db->prepare('INSERT INTO hits VALUES (?, ?)')->execute([$k, time()]);
}

/* ---------- input normalisation (spec D2, E.f) ---------- */

function latin_digits(string $s): string
{
    return strtr($s, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
                      '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
}

/** trim + reject invalid UTF-8 + NFC. Returns null for invalid encoding. */
function clean_input(mixed $v): ?string
{
    if (!is_string($v)) return $v === null ? '' : null;
    if (!mb_check_encoding($v, 'UTF-8')) return null;
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    if (class_exists('Normalizer')) $v = Normalizer::normalize($v, Normalizer::FORM_C) ?: $v;
    return trim($v);
}

/* ---------- social preview tags (spec E.e / D8a) ---------- */

function og_tags(array $p): string
{
    $t = [
        'og:type' => $p['og_type'] ?? 'website',
        'og:site_name' => SITE_NAME,
        'og:title' => $p['title'],
        'og:description' => $p['description'],
        'og:url' => abs_url($p['path']),
        'og:image' => abs_url($p['og_image'] ?? '/og-default.jpg'),
        'og:image:type' => 'image/jpeg',
        'og:image:width' => 1200,
        'og:image:height' => 630,
        'og:image:alt' => $p['og_alt'] ?? 'Ramyar Salah, over a ridge lit by a procession of torches',
        'og:locale' => 'en_US',
    ];
    $o = '';
    foreach ($t as $k => $v) $o .= '<meta property="' . $k . '" content="' . e((string)$v) . "\">\n";
    return $o . "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
}

/** Last real edit of a page's source, for honest sitemap lastmod. */
function lastmod(string ...$files): string
{
    $t = 0;
    foreach ($files as $f) {
        $path = ROOT . '/includes/' . $f;
        if (is_file($path)) $t = max($t, filemtime($path));
    }
    return date('c', $t ?: time());
}

/* ---------- phone ---------- */

/** +9647700835557 -> 0770 083 5557 (Iraqi mobile display form) */
function phone_local(string $e164): string
{
    $n = '0' . substr($e164, 4);
    return substr($n, 0, 4) . ' ' . substr($n, 4, 3) . ' ' . substr($n, 7);
}
function whatsapp_url(string $e164): string
{
    return 'https://wa.me/' . ltrim($e164, '+');
}

/* ---------- mail: SMTP when configured, else the host's mail(); the form saves even if both fail ---------- */

function notify_owner(string $subject, string $body, string $replyTo): bool
{
    if (SMTP_HOST !== '') return smtp_send($subject, $body, $replyTo);
    $to = MAIL_TO !== '' ? MAIL_TO : CONTACT_EMAIL;
    $host = preg_replace('/^www\./', '', explode(':', site_host())[0]);
    // no mail server on a development machine; shared hosts (Hostinger, cPanel) provide mail()
    if ($to === '' || ENV === 'local' || $host === 'localhost' || !str_contains($host, '.') || !function_exists('mail')) return false;
    $strip = fn(string $s): string => str_replace(["\r", "\n"], ' ', $s);
    $from = MAIL_FROM !== '' ? MAIL_FROM : 'website@' . $host;
    $headers = [
        'From: ' . $strip(SITE_NAME) . ' website <' . $strip($from) . '>',
        'Reply-To: <' . $strip($replyTo) . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
    ];
    $ok = @mail($strip($to), '=?UTF-8?B?' . base64_encode($strip($subject)) . '?=', chunk_split(base64_encode($body)), implode("\r\n", $headers));
    if (!$ok) error_log('mail(): message not handed to the mail server');
    return $ok;
}

/* ---------- SMTP ---------- */

function smtp_send(string $subject, string $body, string $replyTo): bool
{
    if (SMTP_HOST === '') return false;   // email notifications switched off in .env
    $strip = fn(string $s): string => str_replace(["\r", "\n"], ' ', $s);
    $port = (int)SMTP_PORT;
    $remote = ($port === 465 ? 'ssl://' : 'tcp://') . SMTP_HOST . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => SMTP_HOST]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 6, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) { error_log("smtp: connect failed ($errno)"); return false; }
    stream_set_timeout($fp, 8);
    $read = function () use ($fp): string {
        $out = '';
        while (($line = fgets($fp, 515)) !== false) { $out .= $line; if (isset($line[3]) && $line[3] === ' ') break; }
        return $out;
    };
    $cmd = function (string $c, array $ok) use ($fp, $read): bool {
        if ($c !== '') fwrite($fp, $c . "\r\n");
        $r = $read();
        return in_array((int)substr($r, 0, 3), $ok, true);
    };
    $host = $strip(explode(':', site_host())[0]);
    $ok = $cmd('', [220]) && $cmd("EHLO $host", [250]);
    if ($ok && $port !== 465) {
        $ok = $cmd('STARTTLS', [220]) && stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT) && $cmd("EHLO $host", [250]);
    }
    $ok = $ok && $cmd('AUTH LOGIN', [334]) && $cmd(base64_encode(SMTP_USER), [334]) && $cmd(base64_encode(SMTP_PASS), [235])
        && $cmd('MAIL FROM:<' . $strip(MAIL_FROM) . '>', [250]) && $cmd('RCPT TO:<' . $strip(MAIL_TO) . '>', [250, 251]) && $cmd('DATA', [354]);
    if ($ok) {
        $headers = [
            'Date: ' . date('r'),
            'From: ' . $strip(SITE_NAME) . ' website <' . $strip(MAIL_FROM) . '>',
            'To: <' . $strip(MAIL_TO) . '>',
            'Reply-To: <' . $strip($replyTo) . '>',
            'Subject: =?UTF-8?B?' . base64_encode($strip($subject)) . '?=',
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $host . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $data = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body)) . "\r\n.";
        $ok = $cmd($data, [250]);
    }
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    if (!$ok) error_log('smtp: send failed');
    return $ok;
}
