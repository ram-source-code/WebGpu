<?php
/** The ways to reach Ramyar, from .env or the defaults in functions.php. Optional: $reachCopy = true adds a copy-email button. */
$ext = '<span class="visually-hidden"> (opens in a new tab)</span>';
?>
<ul class="reach">
<?php if (CONTACT_PHONE !== ''): ?>
  <li><a class="reach__link" href="<?= e(whatsapp_url(CONTACT_PHONE)) ?>" rel="noopener noreferrer" target="_blank"><?= icon('chat') ?><span class="reach__label">WhatsApp</span><span class="reach__value" dir="ltr"><?= e(phone_local(CONTACT_PHONE)) ?></span><?= $ext ?></a></li>
  <li><a class="reach__link" href="tel:<?= e(CONTACT_PHONE) ?>"><?= icon('phone') ?><span class="reach__label">Call</span><span class="reach__value" dir="ltr"><?= e(phone_local(CONTACT_PHONE)) ?></span></a></li>
<?php endif ?>
<?php if (CONTACT_EMAIL !== ''): ?>
  <li><a class="reach__link" href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= icon('mail') ?><span class="reach__label">Email</span><span class="reach__value"><?= e(CONTACT_EMAIL) ?></span></a></li>
<?php endif ?>
<?php if (FACEBOOK_URL !== ''): ?>
  <li><a class="reach__link" href="<?= e(FACEBOOK_URL) ?>" rel="noopener noreferrer" target="_blank"><?= icon('people') ?><span class="reach__label">Facebook</span><span class="reach__value">Ramyar Salah</span><?= $ext ?></a></li>
<?php endif ?>
</ul>
<?php if (!empty($reachCopy) && CONTACT_EMAIL !== ''): ?>
<button class="button button--line reach__copy" type="button" data-copy="<?= e(CONTACT_EMAIL) ?>" hidden><?= icon('copy') ?><span data-copy-label>Copy email address</span></button>
<p class="visually-hidden" aria-live="polite" data-copy-status></p>
<?php endif ?>
