<?php
const NAME_MAX = 100;
const EMAIL_MAX = 254;
const MSG_MIN = 10;
const MSG_MAX = 5000;
const MIN_FILL_SECONDS = 3;

$sign = fn(string $t): string => $t . '.' . substr(hash_hmac('sha256', 'contact|' . $t, FORM_KEY), 0, 32);
$values = ['name' => '', 'email' => '', 'message' => ''];
$errors = [];
$status = 200;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // same-site posts only (no session here, so no CSRF token; the Origin check still stops cross-site posting)
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $site = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
    if (($origin !== '' && strcasecmp((string)parse_url($origin, PHP_URL_HOST) . (parse_url($origin, PHP_URL_PORT) ? ':' . parse_url($origin, PHP_URL_PORT) : ''), (string)($_SERVER['HTTP_HOST'] ?? '')) !== 0)
        || $site === 'cross-site') {
        error_page(403);
    }

    $db = open_db();
    rate_limit($db, 'contact-10m', 6, 600);      // generous: phones on CGNAT share addresses
    rate_limit($db, 'contact-day', 30, 86400);

    foreach (['name', 'email', 'message', 'website', 'ts'] as $k) {
        $v = clean_input($_POST[$k] ?? '');
        if ($v === null) { $errors['form'] = 'Something in the form could not be read. Please type it again.'; $v = ''; }
        if (isset($values[$k])) $values[$k] = $v;
        $$k = $v;
    }

    // bots: a filled honeypot or a form sent faster than a person can type gets a quiet "thank you" and nothing is stored
    $tsOk = false;
    if (preg_match('/^(\d{9,11})\.[0-9a-f]{32}$/', $ts, $m) && hash_equals($sign($m[1]), $ts)) {
        $age = time() - (int)$m[1];
        $tsOk = $age >= MIN_FILL_SECONDS && $age <= 2 * 86400;
    }
    if ($website !== '' || !$tsOk) {
        header('Location: ' . url('/contact', ['sent' => '1']), true, 303);
        exit;
    }

    $nameLen = mb_strlen($values['name']);
    if ($nameLen === 0) $errors['name'] = 'Enter your name, so I know who to reply to.';
    elseif ($nameLen > NAME_MAX) $errors['name'] = 'Keep the name under ' . NAME_MAX . ' characters.';
    elseif (preg_match('/[\x00-\x1F\x7F]/u', $values['name'])) $errors['name'] = 'The name contains characters that cannot be used.';

    if ($values['email'] === '') $errors['email'] = 'Enter your email address, so I can reply.';
    elseif (strlen($values['email']) > EMAIL_MAX || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter an email address like name@example.com.';

    $msgLen = mb_strlen($values['message']);
    if ($msgLen < MSG_MIN) $errors['message'] = 'Write at least ' . MSG_MIN . ' characters about what you want to build.';
    elseif ($msgLen > MSG_MAX) $errors['message'] = 'Keep the message under ' . number_format(MSG_MAX) . ' characters (it has ' . number_format($msgLen) . ').';

    if (!$errors) {
        $db->prepare('INSERT INTO messages (created_at, name, email, message) VALUES (?, ?, ?, ?)')
           ->execute([date('c'), $values['name'], $values['email'], $values['message']]);
        $id = (int)$db->lastInsertId();
        $db->prepare('DELETE FROM messages WHERE created_at < ?')->execute([date('c', strtotime('-12 months'))]);   // retention (privacy page)
        $body = "New message from the website\n\nName: {$values['name']}\nEmail: {$values['email']}\n\n{$values['message']}\n";
        if (smtp_send('Website message from ' . mb_substr($values['name'], 0, 60), $body, $values['email'])) {
            $db->prepare('UPDATE messages SET mailed = 1 WHERE id = ?')->execute([$id]);
        }
        header('Location: ' . url('/contact', ['sent' => '1']), true, 303);
        exit;
    }
    $status = 422;
}
http_response_code($status);

$sent = ($_GET['sent'] ?? '') === '1' && $_SERVER['REQUEST_METHOD'] === 'GET';
$band = ['id' => 'contact-title', 'title' => $sent ? 'Message sent' : 'Get in touch', 'hour' => '0.84', 'progress' => '0.75', 'poster' => 'dawn',
         'lede' => $sent ? 'Thank you. The summit fire is a little brighter for it. I reply by email.' : 'Tell me what you want to build. I reply by email.'];
require ROOT . '/includes/partials/scene-band.php';
$field = function (string $k) use ($errors): string {
    return isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="' . $k . '-error"' : ' aria-invalid="false"';
};
?>
<section class="ground ground--dawn" aria-label="<?= $sent ? 'What happens next' : 'Contact form' ?>">
  <div class="wrap split">
    <div class="contact-aside">
<?php if (CONTACT_EMAIL !== ''): ?>
      <p>Prefer email?</p>
      <p><a class="link-arrow" href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
      <button class="button button--line" type="button" data-copy="<?= e(CONTACT_EMAIL) ?>" hidden><?= icon('copy') ?><span data-copy-label>Copy email address</span></button>
      <p class="visually-hidden" aria-live="polite" data-copy-status></p>
<?php else: ?>
      <p>A few lines are enough: what you are building, for whom, and when you need it.</p>
<?php endif ?>
    </div>
<?php if ($sent): ?>
    <div class="sent" data-flare>
      <p class="sent__title">Your message is on its way.</p>
      <p>It is saved and I will answer at the email address you gave.<?php if (CONTACT_EMAIL !== ''): ?> If it is urgent, write to <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a> directly.<?php endif ?></p>
      <p><a class="link-arrow" href="<?= e(url('/work')) ?>">Back to the work<?= icon('arrow') ?></a></p>
    </div>
<?php else: ?>
    <form class="form" method="post" action="<?= e(url('/contact')) ?>" novalidate data-form>
      <p class="form__hint">All three fields are required.</p>
<?php if ($errors): ?>
      <div class="form__summary" role="alert" tabindex="-1" data-error-summary>
        <p>The message was not sent. Please fix <?= count($errors) === 1 ? 'one thing' : count($errors) . ' things' ?>:</p>
        <ul><?php foreach ($errors as $k => $msg): ?><li><a href="#<?= $k === 'form' ? 'name' : e($k) ?>"><?= e($msg) ?></a></li><?php endforeach ?></ul>
      </div>
<?php endif ?>
      <div class="field">
        <label for="name">Your name</label>
        <input id="name" name="name" type="text" autocomplete="name" required maxlength="<?= NAME_MAX ?>" dir="auto" enterkeyhint="next" value="<?= e($values['name']) ?>" data-error="name-error"<?= $field('name') ?>>
        <p class="field__error" id="name-error"<?= isset($errors['name']) ? '' : ' hidden' ?>><?= e($errors['name'] ?? 'Enter your name, so I know who to reply to.') ?></p>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required maxlength="<?= EMAIL_MAX ?>" autocapitalize="none" autocorrect="off" spellcheck="false" enterkeyhint="next" value="<?= e($values['email']) ?>" data-error="email-error"<?= $field('email') ?>>
        <p class="field__error" id="email-error"<?= isset($errors['email']) ? '' : ' hidden' ?>><?= e($errors['email'] ?? 'Enter an email address like name@example.com.') ?></p>
      </div>
      <div class="field">
        <label for="message">What do you want to build?</label>
        <textarea id="message" name="message" rows="7" required minlength="<?= MSG_MIN ?>" maxlength="<?= MSG_MAX ?>" dir="auto" data-error="message-error"<?= $field('message') ?>><?= e($values['message']) ?></textarea>
        <p class="field__error" id="message-error"<?= isset($errors['message']) ? '' : ' hidden' ?>><?= e($errors['message'] ?? 'Write at least ' . MSG_MIN . ' characters about what you want to build.') ?></p>
      </div>
      <div class="hp" aria-hidden="true">
        <label for="website">Leave this empty</label>
        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="ts" value="<?= e($sign((string)time())) ?>">
      <button class="button button--ink" type="submit" data-submit>Send message<?= icon('arrow') ?></button>
      <p class="form__note">Your name and email are used only to reply to you. <a href="<?= e(url('/privacy')) ?>">Privacy</a></p>
    </form>
<?php endif ?>
  </div>
</section>
<?php
return [
    'title' => $sent ? 'Message sent' : 'Contact',
    'description' => 'Get in touch with Ramyar Salah about real-time 3D and WebGPU work on the web.',
    'path' => '/contact',
    'scene' => 'band',
    'current' => '/contact',
    'noindex' => $sent || $errors,
    'bodyClass' => 'page-inner',
];
