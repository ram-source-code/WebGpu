<?php
$band = ['id' => 'about-title', 'title' => 'About', 'hour' => '0.3', 'progress' => '0.25', 'poster' => 'dusk',
         'lede' => 'Ramyar Salah builds real-time 3D for the web.'];
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
<section class="ground ground--night" aria-labelledby="craft-title">
  <div class="wrap split">
    <h2 id="craft-title" class="section-title">How this site is made</h2>
    <div class="prose">
      <p>The pages are rendered on the server in plain PHP, so everything reads, links and works before any script runs. The scene is an enhancement on top: WebGPU where the browser has it, WebGL2 where it doesn’t, and a still image everywhere else.</p>
      <p>No frameworks, no build step, nothing loaded from other servers. The whole site, fonts included, comes from one place.</p>
      <p><a class="link-arrow" href="<?= e(url('/work/newroz-on-the-ridge')) ?>">Read how the scene works<?= icon('arrow') ?></a></p>
    </div>
  </div>
</section>
<?php
return [
    'title' => 'About',
    'description' => 'About Ramyar Salah, who builds real-time 3D for the web, and how this WebGPU portfolio is made.',
    'path' => '/about',
    'scene' => 'band',
    'current' => '/about',
    'bodyClass' => 'page-inner',
];
