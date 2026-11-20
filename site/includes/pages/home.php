<?php
$work = projects();
?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero__copy">
    <h1 id="hero-title" class="hero__name"><span>Ramyar</span> <span>Salah</span></h1>
    <p class="hero__lede">Real-time 3D for the web, built on WebGPU.</p>
    <div class="hero__actions">
      <a class="button button--fire" href="<?= e(url('/contact')) ?>">Get in touch<?= icon('arrow') ?></a>
      <a class="link-quiet" href="#work">See the work</a>
    </div>
  </div>
  <div class="scene-bar">
    <p class="scene-bar__status" data-scene-status aria-live="polite">A still from the scene: Newroz fires climbing a Zagros ridge at dusk.</p>
    <button class="scene-bar__toggle" type="button" data-scene-toggle hidden
      data-icon-play="<?= e(asset('assets/img/icons.svg')) ?>#play" data-icon-pause="<?= e(asset('assets/img/icons.svg')) ?>#pause" aria-label="Pause the scene">
      <svg class="icon" width="20" height="20" aria-hidden="true"><use href="<?= e(asset('assets/img/icons.svg')) ?>#pause"/></svg><span data-label>Pause</span>
    </button>
  </div>
</section>

<section class="ground ground--night ground--see ridge-top ridge-bottom" id="work" aria-labelledby="work-title">
  <div class="wrap">
    <h2 id="work-title" class="section-title">Selected work</h2>
    <p class="section-intro">Each project is a fire on the road up the ridge. Point at one and its stretch of the procession lights up behind the page.</p>
    <ol class="climb">
<?php foreach ($work as $i => $p): $at = climb_position($i, count($work)); ?>
      <li class="climb__item<?= $p['placeholder'] ? ' climb__item--slot' : '' ?>">
        <a class="climb__link" href="<?= e(url('/work/' . $p['slug'])) ?>" data-climb="<?= e((string)$at) ?>">
<?php if ($p['placeholder']): ?>
          <span class="climb__title">Case study, to be written</span>
          <span class="climb__meta">Fire <?= (int)round($at * 100) ?>% up the road</span>
          <?= client_block(['Title' => 'project name', 'Year and role' => 'e.g. 2025, lead developer', 'Summary' => 'one sentence: what it is and what you did']) ?>
<?php else: ?>
          <span class="climb__title"><?= e($p['title']) ?></span>
          <span class="climb__meta"><?= e($p['year'] . ' · ' . $p['role']) ?> · fire <?= (int)round($at * 100) ?>% up the road</span>
          <span class="climb__summary"><?= e($p['summary']) ?></span>
<?php endif ?>
        </a>
      </li>
<?php endforeach ?>
    </ol>
    <p class="more"><a class="link-arrow" href="<?= e(url('/work')) ?>">All work<?= icon('arrow') ?></a></p>
  </div>
</section>

<div class="window"><p class="window__caption">Midnight. The line reaches the summit.</p></div>

<section class="ground ground--ember ridge-top ridge-bottom" aria-labelledby="about-title">
  <div class="wrap split">
    <h2 id="about-title" class="section-title">Why a mountain on fire</h2>
    <div class="prose">
      <p>On the night before Newroz, the Kurdish new year, fires are lit on the mountains, and in towns like Akre people carry torches up the hillside until the whole slope is a moving line of light.</p>
      <p>This site rebuilds that night as a GPU simulation. Every point of light is a walker with a place in the line, a little wind, and a tendency to step toward you. Move your pointer, or touch the ridge, and the closest walkers turn your way.</p>
      <p><?= client('two or three sentences about you: where you work from, what you like to build, who you build it for') ?></p>
      <p><a class="link-arrow" href="<?= e(url('/about')) ?>">More about Ramyar<?= icon('arrow') ?></a></p>
    </div>
  </div>
</section>

<div class="window window--dawn"><p class="window__caption">Before dawn. The fire is still burning.</p></div>

<section class="ground ground--dawn ridge-top" aria-labelledby="close-title">
  <div class="wrap close">
    <h2 id="close-title" class="close__title">Light the next fire</h2>
    <p class="close__lede">Building something real-time, spatial, or simply fast on the web? Tell me about it.</p>
    <div class="close__actions">
      <a class="button button--ink" href="<?= e(url('/contact')) ?>">Write a message<?= icon('arrow') ?></a>
<?php if (CONTACT_EMAIL !== ''): ?>
      <button class="button button--line" type="button" data-copy="<?= e(CONTACT_EMAIL) ?>" hidden><?= icon('copy') ?><span data-copy-label>Copy email address</span></button>
      <a class="link-quiet" href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
<?php endif ?>
    </div>
    <p class="visually-hidden" aria-live="polite" data-copy-status></p>
  </div>
</section>
<?php
return [
    'title' => 'Ramyar Salah · Real-time 3D for the web',
    'description' => 'Portfolio of Ramyar Salah: real-time 3D for the web, built on WebGPU. The opening scene is a live GPU simulation of Newroz fires on a Zagros ridge.',
    'path' => '/',
    'scene' => 'full',
    'current' => '/',
    'bodyClass' => 'page-home',
];
