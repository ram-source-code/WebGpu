<?php
$band = ['id' => 'work-title', 'title' => 'Work', 'hour' => '0.5', 'progress' => '0.45', 'poster' => 'night',
         'lede' => 'Projects in the order they climbed the slope.'];
require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--night ground--see" aria-label="Projects">
  <div class="wrap">
    <ol class="climb climb--full">
<?php $work = projects(); foreach ($work as $i => $p): $at = climb_position($i, count($work)); ?>
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
<?php if ($p['stack']): ?>
          <span class="climb__stack"><?= e(implode(' · ', $p['stack'])) ?></span>
<?php endif ?>
        </a>
      </li>
<?php endforeach ?>
    </ol>
  </div>
</section>
<?php
return [
    'title' => 'Work',
    'description' => 'Selected work by Ramyar Salah: real-time 3D and WebGPU projects, starting with the live Newroz scene that opens this site.',
    'path' => '/work',
    'scene' => 'band',
    'current' => '/work',
    'bodyClass' => 'page-inner',
];
