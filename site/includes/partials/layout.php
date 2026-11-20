<?php
/** @var array $meta  title, description, path; optional: scene, noindex, og_type, jsonld, current */
/** @var string $content */
$title = $meta['title'];
$fullTitle = $meta['path'] === '/' ? $title : $title . ' · ' . SITE_NAME;
$current = $meta['current'] ?? '';
$nav = ['/work' => 'Work', '/about' => 'About', '/contact' => 'Contact'];
$person = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => 'Ramyar Salah',
    'url' => abs_url('/'),
    'sameAs' => ['https://github.com/ram-source-code'],
];
$ld = $meta['jsonld'] ?? [$person];
// data saver on (Save-Data header) or ?still: keep the still image, skip the scene's JavaScript
$sceneJs = !empty($meta['scene']) && strtolower($_SERVER['HTTP_SAVE_DATA'] ?? '') !== 'on' && !isset($_GET['still']);
if (!empty($meta['scene'])) header('Vary: Save-Data');
?>
<!doctype html>
<html lang="en" dir="ltr" data-base="<?= e(BASE) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($meta['description']) ?>">
<link rel="canonical" href="<?= e(abs_url($meta['path'])) ?>">
<?php if (!empty($meta['noindex'])): ?>
<meta name="robots" content="noindex">
<?php endif ?>
<meta name="color-scheme" content="dark">
<meta name="theme-color" content="#151a3a">
<link rel="icon" href="<?= e(url('/favicon.ico')) ?>" sizes="32x32">
<link rel="icon" href="<?= e(url('/icon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(url('/apple-touch-icon.png')) ?>">
<?= og_tags($meta + ['lang' => 'en']) ?>
<link rel="preload" href="<?= e(asset('assets/fonts/anybody-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(asset('assets/fonts/atkinson-next-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
<?php if ($sceneJs): ?>
<script type="importmap" nonce="<?= $nonce ?>"><?= json_script(['imports' => [
    'rs/scene-core' => asset('assets/js/scene-core.js'),
    'rs/scene-webgpu' => asset('assets/js/scene-webgpu.js'),
    'rs/scene-webgl' => asset('assets/js/scene-webgl.js'),
]]) ?></script>
<link rel="modulepreload" href="<?= e(asset('assets/js/scene-core.js')) ?>">
<?php endif ?>
<script type="module" src="<?= e(asset('assets/js/site.js')) ?>" nonce="<?= $nonce ?>"></script>
<?php if ($sceneJs): ?>
<script type="module" src="<?= e(asset('assets/js/scene.js')) ?>" nonce="<?= $nonce ?>"></script>
<?php endif ?>
<?php foreach ($ld as $block): ?>
<script type="application/ld+json" nonce="<?= $nonce ?>"><?= json_script($block) ?></script>
<?php endforeach ?>
<script type="speculationrules" nonce="<?= $nonce ?>"><?= json_script(['prefetch' => [[
    'where' => ['and' => [
        ['href_matches' => BASE . '/*'],
        ['not' => ['href_matches' => '/*\\?*']],
        ['not' => ['selector_matches' => '.no-prefetch']],
        ['not' => ['selector_matches' => '[rel~=nofollow]']],
    ]],
    'eagerness' => 'moderate',
]]]) ?></script>
</head>
<body class="<?= e($meta['bodyClass'] ?? '') ?>">
<a class="skip" href="#main">Skip to content</a>
<?php if (($meta['scene'] ?? '') === 'full'): ?>
<div class="scene scene--full" data-scene="full">
  <picture class="scene__poster">
    <source media="(orientation: portrait)" type="image/avif" srcset="<?= e(asset('assets/img/poster-dusk-portrait-720.avif')) ?>">
    <source media="(orientation: portrait)" type="image/webp" srcset="<?= e(asset('assets/img/poster-dusk-portrait-720.webp')) ?>">
    <source type="image/avif" srcset="<?= e(asset('assets/img/poster-dusk-960.avif')) ?> 960w, <?= e(asset('assets/img/poster-dusk-1920.avif')) ?> 1920w" sizes="100vw">
    <img src="<?= e(asset('assets/img/poster-dusk-1920.webp')) ?>" srcset="<?= e(asset('assets/img/poster-dusk-960.webp')) ?> 960w, <?= e(asset('assets/img/poster-dusk-1920.webp')) ?> 1920w" sizes="100vw" width="1920" height="1080" alt="" fetchpriority="high">
  </picture>
  <canvas aria-hidden="true"></canvas>
</div>
<div class="altitude" aria-hidden="true"><span class="altitude__mark"></span><span>Dusk</span><span>Midnight</span><span>First light</span></div>
<?php endif ?>
<header class="site-header">
  <a class="site-mark" href="<?= e(url('/')) ?>"<?= $current === '/' ? ' aria-current="page"' : '' ?>><span translate="no">Ramyar Salah</span></a>
  <nav aria-label="Primary">
    <ul class="site-nav">
<?php foreach ($nav as $href => $label): ?>
      <li><a href="<?= e(url($href)) ?>"<?= $current === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach ?>
    </ul>
  </nav>
</header>
<main id="main" tabindex="-1">
<?= $content ?>
</main>
<footer class="site-footer">
  <div class="site-footer__inner">
    <p class="site-footer__ask">Have something to build? <a href="<?= e(url('/contact')) ?>">Get in touch</a></p>
    <ul class="site-footer__links">
      <li><a href="https://github.com/ram-source-code" rel="noopener noreferrer" target="_blank">GitHub<?= icon('external') ?><span class="visually-hidden"> (opens in a new tab)</span></a></li>
      <li><a href="<?= e(url('/privacy')) ?>"<?= $current === '/privacy' ? ' aria-current="page"' : '' ?>>Privacy</a></li>
    </ul>
    <p class="site-footer__legal">© <?= date('Y') ?> <span translate="no">Ramyar Salah</span>. Fonts: Anybody and Atkinson Hyperlegible Next, <a href="<?= e(url('/assets/fonts/OFL.txt')) ?>">SIL Open Font License</a>.</p>
  </div>
</footer>
</body>
</html>
