<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
ini_set('display_errors', '0');
ini_set('log_errors', '1');
is_dir(ROOT . '/data') || @mkdir(ROOT . '/data', 0700, true);
ini_set('error_log', ROOT . '/data/php-error.log');
date_default_timezone_set('Asia/Baghdad');
mb_internal_encoding('UTF-8');

// '' at the web root, '/name' in a subfolder. Every request enters through ROOT/index.php.
defined('BASE') || define('BASE', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/'));

require ROOT . '/includes/functions.php';

header_remove('X-Powered-By');
ob_start();

$nonce = base64_encode(random_bytes(16));
$csp = "default-src 'self'; script-src 'nonce-$nonce' 'strict-dynamic'; style-src 'self' 'nonce-$nonce'; "
     . "img-src 'self' data:; font-src 'self'; connect-src 'self'; worker-src 'self'; manifest-src 'self'; "
     . "object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'";
header("Content-Security-Policy: $csp; upgrade-insecure-requests");

function error_page(int $code, array $headers = []): never
{
    global $nonce;
    while (ob_get_level()) ob_end_clean();
    if (!headers_sent()) {
        http_response_code($code);
        header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=UTF-8');
        foreach ($headers as $h) header($h);
    }
    $file = ROOT . "/includes/errors/$code.php";
    require is_file($file) ? $file : ROOT . '/includes/errors/500.php';
    exit;
}

set_exception_handler(function (Throwable $e): void {
    error_log((string)$e);
    error_page($e instanceof PDOException ? 503 : 500);
});
register_shutdown_function(function (): void {
    $e = error_get_last();
    if ($e && ($e['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) error_page(500);
});
// warnings and notices become exceptions: a half-working page never ships silently
set_error_handler(function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) return false;
    throw new ErrorException($str, 0, $no, $file, $line);
});

define('ENV', load_env(ROOT . '/.env'));
if (ENV === 'local') header("Content-Security-Policy: $csp");   // local is plain http: nothing to upgrade

// one 301 to https://CANONICAL_HOST with the same path (production only; local stays where it is)
if (ENV !== 'local' && PHP_SAPI !== 'cli') {
    $https = ($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    if (!$https || strcasecmp((string)($_SERVER['HTTP_HOST'] ?? ''), CANONICAL_HOST) !== 0) {
        header('Location: https://' . CANONICAL_HOST . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
        exit;
    }
}

// maintenance: create/delete data/maintenance.flag; developer bypass cookie "maint" = MAINT_KEY
if (PHP_SAPI !== 'cli' && is_file(ROOT . '/data/maintenance.flag') && !hash_equals(MAINT_KEY, (string)($_COOKIE['maint'] ?? ''))) {
    error_page(503, ['Retry-After: 900']);
}

// This site has no login, cart or admin, so it never starts a session (spec D1: public pages set no cookie).
