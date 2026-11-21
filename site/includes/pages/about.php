<?php
$band = ['id' => 'about-title', 'title' => 'About', 'hour' => '0.3', 'progress' => '0.25', 'poster' => 'dusk',
         'lede' => 'I make business owners’ dreams come true.'];
require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--ember" aria-labelledby="bio-title">
  <div class="wrap split">
    <h2 id="bio-title" class="section-title">In short</h2>
    <div class="prose">
      <p>I am Ramyar Salah. I build the software a business runs on: websites, desktop apps, admin utilities and custom business software, made for the way each owner actually works.</p>
      <p>I hold a Bachelor’s degree in IT. I also care about how things look: my designs are modern and current, never stuck in old, legacy styles.</p>
      <p>If you have an idea for your business, a website you are not proud of, or a job your team still does by hand, I would like to hear about it.</p>
    </div>
  </div>
</section>
<section class="ground ground--night" aria-labelledby="reach-title">
  <div class="wrap split">
    <h2 id="reach-title" class="section-title">Reach me</h2>
    <div>
<?php $reachCopy = true; require ROOT . '/includes/partials/reach.php'; ?>
      <p class="reach__form">Or <a href="<?= e(url('/contact')) ?>">send a message from the site</a>.</p>
    </div>
  </div>
</section>
<?php
return [
    'title' => 'About',
    'description' => 'Ramyar Salah builds websites, desktop apps, admin utilities and custom business software for business owners. Bachelor’s degree in IT, modern design.',
    'path' => '/about',
    'scene' => 'band',
    'current' => '/about',
    'bodyClass' => 'page-inner',
];
