<section class="hero" aria-labelledby="hero-title">
  <div class="hero__copy">
    <h1 id="hero-title" class="hero__name"><span>Ramyar</span> <span>Salah</span></h1>
    <p class="hero__lede">I make business owners’ dreams come true: websites, desktop apps and custom business software.</p>
    <div class="hero__actions">
      <a class="button button--fire" href="<?= e(url('/contact')) ?>">Get in touch<?= icon('arrow') ?></a>
      <a class="link-quiet" href="#make">What I make</a>
    </div>
  </div>
  <div class="scene-bar">
    <button class="scene-bar__toggle" type="button" data-scene-toggle hidden
      data-icon-play="<?= e(asset('assets/img/icons.svg')) ?>#play" data-icon-pause="<?= e(asset('assets/img/icons.svg')) ?>#pause" aria-label="Pause the scene">
      <svg class="icon" width="20" height="20" aria-hidden="true"><use href="<?= e(asset('assets/img/icons.svg')) ?>#pause"/></svg><span data-label>Pause</span>
    </button>
  </div>
</section>

<section class="ground ground--ember ridge-top ridge-bottom" id="make" aria-labelledby="make-title">
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

<div class="window"><p class="window__caption">Midnight. The line reaches the summit.</p></div>

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
