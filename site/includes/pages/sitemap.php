<?php
// Public, canonical, indexable URLs only; lastmod changes only when the page's source changes.
$urls = [
    ['/', lastmod('pages/home.php')],
    ['/about', lastmod('pages/about.php')],
    ['/contact', lastmod('pages/contact.php')],
    ['/privacy', lastmod('pages/privacy.php')],
];
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=0, must-revalidate');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n", '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as [$path, $mod]) {
    echo '  <url><loc>', e(abs_url($path)), '</loc><lastmod>', e($mod), "</lastmod></url>\n";
}
echo "</urlset>\n";
return null;
