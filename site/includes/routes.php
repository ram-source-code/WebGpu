<?php
// Every URL of the site. 'METHOD[|METHOD] /path' => page file in includes/pages/ (HEAD is served as GET).
return [
    'GET /'             => 'home',
    'GET /work'         => 'work',
    'GET /work/{slug}'  => 'project',
    'GET /about'        => 'about',
    'GET|POST /contact' => 'contact',
    'GET /privacy'      => 'privacy',
    'GET /sitemap.xml'  => 'sitemap',
    'GET /robots.txt'   => 'robots',
];
