<?php
declare(strict_types=1);
// The only PHP file the web can reach.
require __DIR__ . '/includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'HEAD') $method = 'GET';

$raw = rawurldecode((string)strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
if (!mb_check_encoding($raw, 'UTF-8') || str_contains($raw, "\0")) error_page(404);
if (BASE !== '' && str_starts_with($raw, BASE)) $raw = substr($raw, strlen(BASE));
if ($raw === '') $raw = '/';
$clean = '/' . trim(preg_replace('#/+#', '/', $raw), '/');
$path = $clean;

[$page, $params] = route(require ROOT . '/includes/routes.php', $method, $path);
if ($raw !== $clean && $method === 'GET') {
    header('Location: ' . url($clean, $_GET), true, 301);
    exit;
}

ob_start();
$meta = require ROOT . "/includes/pages/$page.php";
$content = ob_get_clean();
if (is_array($meta)) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache');
    require ROOT . '/includes/partials/layout.php';
} else {
    echo $content;   // the page sent its own response (XML, plain text)
}
// HEAD: Apache sends the same headers and drops the body itself.
