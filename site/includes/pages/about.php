<?php
$band = ['id' => 'about-title', 'title' => 'About', 'hour' => '0.3', 'progress' => '0.25', 'poster' => 'dusk',
         'lede' => client('one line: who you are and what you make')];
require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--ember" aria-labelledby="bio-title">
  <div class="wrap split">
    <h2 id="bio-title" class="section-title">In short</h2>
    <div class="prose">
      <figure class="portrait">
        <div class="portrait__slot"><?= client('portrait photo, 4:5, your own') ?></div>
      </figure>
      <p><?= client('who you are and where you work from, two sentences') ?></p>
      <p><?= client('what you build and for whom, two or three sentences') ?></p>
      <p><?= client('how you like to work with people: availability, languages, time zone') ?></p>
    </div>
  </div>
</section>
<section class="ground ground--night" aria-labelledby="site-title">
  <div class="wrap split">
    <h2 id="site-title" class="section-title">About this site</h2>
    <div class="prose">
      <p>The look of this site comes from Newroz, from fires carried up the Zagros mountains on the eve of the new year, not from a template.</p>
      <p>It is made to work for everyone: it reads on an old phone and a weak connection, with or without motion, and with the keyboard alone.</p>
      <p><a class="link-arrow" href="<?= e(url('/work/newroz-on-the-ridge')) ?>">See the night scene<?= icon('arrow') ?></a></p>
    </div>
  </div>
</section>
<?php
return [
    'title' => 'About',
    'description' => 'About Ramyar Salah, and the Newroz night that this site is made from.',
    'path' => '/about',
    'scene' => 'band',
    'current' => '/about',
    'bodyClass' => 'page-inner',
];
