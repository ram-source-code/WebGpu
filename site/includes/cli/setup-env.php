<?php
// Creates .env from .env.example with fresh random keys for every section. Never overwrites.
//   php includes/cli/setup-env.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
if (is_file("$root/.env")) exit(".env already exists; edit it by hand.\n");
$out = preg_replace_callback('/^(FORM_KEY|MAINT_KEY)(\s*=\s*)""/m',
    fn($m) => $m[1] . $m[2] . '"' . bin2hex(random_bytes($m[1] === 'FORM_KEY' ? 32 : 16)) . '"',
    file_get_contents("$root/.env.example"));
$out = preg_replace('/^SITE_NAME(\s*=\s*)""/m', 'SITE_NAME$1"Ramyar Salah"', $out);
$out = preg_replace('/^(CANONICAL_HOST|CONTACT_EMAIL|SMTP_USER|SMTP_PASS|MAIL_FROM|MAIL_TO)(\s*=\s*)""/m', '$1$2"[CLIENT: fill in]"', $out);
// local defaults that work on a fresh XAMPP install
$out = preg_replace_callback('/\[local\](.*?)\n\[/s', function ($m) {
    $s = str_replace(['CANONICAL_HOST = "[CLIENT: fill in]"', 'CONTACT_EMAIL  = "[CLIENT: fill in]"'], ['CANONICAL_HOST = "localhost"', 'CONTACT_EMAIL  = "owner@localhost.test"'], $m[0]);
    return preg_replace('/^(SMTP_USER|SMTP_PASS|MAIL_FROM|MAIL_TO)(\s*=\s*)"\[CLIENT: fill in\]"/m', '$1$2""', $s);
}, $out);
$out = preg_replace('/^SMTP_PORT(\s*=\s*)""/m', 'SMTP_PORT$1"587"', $out);
file_put_contents("$root/.env", $out);
@chmod("$root/.env", 0600);
echo "Wrote .env. Fill every [CLIENT: …] value in [production] before going live (the site refuses to start with one left).\n";
