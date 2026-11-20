<section class="page-head ground ground--night" aria-labelledby="privacy-title">
  <div class="wrap">
    <h1 id="privacy-title" class="page-head__title">Privacy</h1>
    <p class="page-head__lede">Plain language, one screen.</p>
  </div>
</section>
<section class="ground ground--dawn" aria-label="Privacy notice">
  <div class="wrap">
    <div class="prose prose--narrow">
      <h2>Who runs this site</h2>
      <p>Ramyar Salah. Contact: <?php if (CONTACT_EMAIL !== ''): ?><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a><?php else: ?>the <a href="<?= e(url('/contact')) ?>">contact form</a><?php endif ?>.</p>
      <h2>What is collected, and why</h2>
      <p>Only what you type into the contact form: your name, your email address and your message. They are used for one thing: replying to you.</p>
      <p>Reading the site sets no cookies and runs no analytics, ads or tracking. If you pause the 3D scene, your browser remembers that choice in its own storage on your device; nothing is sent anywhere.</p>
      <h2>Who receives it</h2>
      <p>Messages are stored in a database on this site’s web host and sent to my own email inbox so I see them quickly. Nobody else receives them. Nothing is sold or shared.</p>
      <p>The server keeps short-lived records to stop spam: the time of each form submission and a scrambled form of your network address that cannot be turned back into the address. They are deleted after 24 hours.</p>
      <h2>How long it is kept</h2>
      <p>Messages are kept for up to 12 months, then deleted automatically.</p>
      <h2>Your choices</h2>
      <p>Write to me (address above) to get a copy of what you sent, to correct it, or to have it deleted. I answer within 30 days.</p>
      <p class="muted">Last updated: <?= e(date('j F Y', filemtime(__FILE__))) ?></p>
    </div>
  </div>
</section>
<?php
return [
    'title' => 'Privacy',
    'description' => 'What this site collects (only what you send through the contact form), why, who receives it and how long it is kept.',
    'path' => '/privacy',
    'current' => '/privacy',
    'bodyClass' => 'page-inner page-plain',
];
