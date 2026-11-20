<?php
/** Error page shell: no .env value, no database, no page code, so it renders even when those fail. */
/** @var int $code @var string $heading @var string $text */
global $nonce;
$home = function_exists('url') && defined('BASE') ? url('/') : '/';
$css = function_exists('asset') && defined('ROOT') ? asset('assets/css/main.css') : '';
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="color-scheme" content="dark">
<title><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?> · Ramyar Salah</title>
<?php if ($css): ?><link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>"><?php endif ?>
<style nonce="<?= htmlspecialchars((string)$nonce, ENT_QUOTES, 'UTF-8') ?>">
.err{min-height:100svh;display:grid;align-content:center;gap:1rem;padding:2rem max(1rem,6vw);background:oklch(0.2 0.055 272);color:oklch(0.95 0.02 85);font-family:system-ui,sans-serif}
.err a{color:oklch(0.87 0.15 85)}
</style>
</head>
<body>
<main class="err">
  <p class="err__code"><?= (int)$code ?></p>
  <h1 class="err__title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="err__text"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></p>
  <p><a class="link-arrow" href="<?= htmlspecialchars($home, ENT_QUOTES, 'UTF-8') ?>">Back to the ridge</a></p>
  <p>Need a hand? <a href="<?= htmlspecialchars(rtrim($home, '/') . '/contact', ENT_QUOTES, 'UTF-8') ?>">Get in touch</a></p>
</main>
</body>
</html>
