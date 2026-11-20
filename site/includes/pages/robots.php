<?php
// Served at BASE/robots.txt. Crawlers only read /robots.txt at the host root: in a subfolder deploy,
// copy these lines into the root robots.txt (spec D1a).
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: public, max-age=0, must-revalidate');
echo "User-agent: *\nAllow: /\n\nSitemap: ", abs_url('/sitemap.xml'), "\n";
return null;
