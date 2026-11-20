<?php
/** Header strip with the live scene at a fixed hour. Expects $band = ['hour' => '0.5', 'title' => '...', 'id' => '...', 'lede' => '...', 'slider' => bool] */
$icons = asset('assets/img/icons.svg');
?>
<section class="band" aria-labelledby="<?= e($band['id']) ?>">
  <div class="scene scene--band" data-scene="band" data-hour="<?= e($band['hour']) ?>" data-progress="<?= e($band['progress'] ?? '0.3') ?>">
    <picture class="scene__poster">
      <source type="image/avif" srcset="<?= e(asset('assets/img/poster-' . $band['poster'] . '-960.avif')) ?> 960w, <?= e(asset('assets/img/poster-' . $band['poster'] . '-1920.avif')) ?> 1920w" sizes="100vw">
      <img src="<?= e(asset('assets/img/poster-' . $band['poster'] . '-1920.webp')) ?>" srcset="<?= e(asset('assets/img/poster-' . $band['poster'] . '-960.webp')) ?> 960w, <?= e(asset('assets/img/poster-' . $band['poster'] . '-1920.webp')) ?> 1920w" sizes="100vw" width="1920" height="1080" alt="" fetchpriority="high">
    </picture>
    <canvas aria-hidden="true"></canvas>
  </div>
  <div class="band__copy">
    <h1 id="<?= e($band['id']) ?>" class="band__title"><?= $band['title'] ?></h1>
<?php if (!empty($band['lede'])): ?>
    <p class="band__lede"><?= $band['lede'] ?></p>
<?php endif ?>
  </div>
  <div class="scene-bar">
    <p class="scene-bar__status" data-scene-status aria-live="polite">A still from the scene.</p>
<?php if (!empty($band['slider'])): ?>
    <label class="hour" hidden data-hour-control>
      <span>Hour</span>
      <input type="range" min="0" max="1" step="0.01" value="<?= e($band['hour']) ?>" data-scene-hour aria-valuetext="Dusk">
    </label>
<?php endif ?>
    <button class="scene-bar__toggle" type="button" data-scene-toggle hidden
      data-icon-play="<?= e($icons) ?>#play" data-icon-pause="<?= e($icons) ?>#pause" aria-label="Pause the scene">
      <svg class="icon" width="20" height="20" aria-hidden="true"><use href="<?= e($icons) ?>#pause"/></svg><span data-label>Pause</span>
    </button>
  </div>
</section>
