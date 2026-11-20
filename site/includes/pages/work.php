<?php
$band = ['id' => 'work-title', 'title' => 'Work', 'hour' => '0.5', 'progress' => '0.45', 'poster' => 'night',
         'lede' => 'Projects in the order they climbed the slope.'];
require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--night" aria-label="Projects">
  <div class="wrap">
    <ol class="climb climb--full">
<?php foreach (projects() as $p): ?>
      <li class="climb__item<?= $p['placeholder'] ? ' climb__item--slot' : '' ?>">
        <a class="climb__link" href="<?= e(url('/work/' . $p['slug'])) ?>">
          <span class="climb__title"><?= $p['placeholder'] ? client('project name') : e($p['title']) ?></span>
          <span class="climb__meta"><?= $p['placeholder'] ? client('year, your role') : e($p['year'] . ' · ' . $p['role']) ?></span>
          <span class="climb__summary"><?= $p['placeholder'] ? client('one sentence: what it is and what you did') : e($p['summary']) ?></span>
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
