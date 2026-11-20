<?php
$work = projects();
?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero__copy">
    <h1 id="hero-title" class="hero__name"><span>Ramyar</span> <span>Salah</span></h1>
    <p class="hero__lede">I make business owners’ dreams come true: websites, desktop apps and custom business software.</p>
    <div class="hero__actions">
      <a class="button button--fire" href="<?= e(url('/contact')) ?>">Get in touch<?= icon('arrow') ?></a>
      <a class="link-quiet" href="#work">See the work</a>
    </div>
  </div>
  <div class="scene-bar">
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

<section class="ground ground--ember ridge-top ridge-bottom" aria-labelledby="make-title">
  <div class="wrap split">
    <div>
      <h2 id="make-title" class="section-title">What I make</h2>
      <p class="make__intro">For business owners who want software that fits the way they work, and looks like it was made this year.</p>
    </div>
    <div>
      <ul class="make">
        <li><span class="make__name">Websites</span> Modern, fast sites with a design that looks current, not left behind.</li>
        <li><span class="make__name">Desktop apps</span> Software that runs on your own computers, in your office or shop.</li>
        <li><span class="make__name">Admin utilities</span> Custom tools for the jobs behind the scenes: managing records, users and daily tasks.</li>
        <li><span class="make__name">Custom business software</span> Built around how your business actually runs, instead of bending your business to fit a product.</li>
      </ul>
      <p class="make__more">Bachelor’s degree in IT. <a class="link-arrow" href="<?= e(url('/about')) ?>">More about Ramyar<?= icon('arrow') ?></a></p>
    </div>
  </div>
</section>

<div class="window window--dawn"><p class="window__caption">Before dawn. The fire is still burning.</p></div>

<section class="ground ground--dawn ridge-top" aria-labelledby="close-title">
  <div class="wrap close">
    <h2 id="close-title" class="close__title">Light the next fire</h2>
    <p class="close__lede">Tell me about your business and what you want it to do. Message me here, or call or WhatsApp.</p>
    <div class="close__actions">
      <a class="button button--ink" href="<?= e(url('/contact')) ?>">Write a message<?= icon('arrow') ?></a>
    </div>
<?php $reachCopy = true; require ROOT . '/includes/partials/reach.php'; ?>
  </div>
</section>
<?php
return [
    'title' => 'Ramyar Salah · Websites, desktop apps and business software',
    'description' => 'Ramyar Salah builds websites, desktop apps, admin utilities and custom business software for business owners, with a modern design eye. Call or WhatsApp 0770 083 5557.',
    'path' => '/',
    'scene' => 'full',
    'current' => '/',
    'bodyClass' => 'page-home',
];
