<?php
$slug = (string)($params['slug'] ?? '');
if (!preg_match('/^[a-z0-9-]{1,64}$/', $slug)) error_page(404);
$p = project($slug);
if (!$p) error_page(404);

$all = projects();
$i = array_search($slug, array_column($all, 'slug'), true);
$next = $all[($i + 1) % count($all)];

if ($p['placeholder']):
    $band = ['id' => 'project-title', 'title' => client('project name'), 'hour' => $p['hour'], 'progress' => '0.4', 'poster' => 'night',
             'lede' => client('one sentence: what it is and who it was for')];
    require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--night" aria-label="Case study">
  <div class="wrap split">
    <dl class="facts">
      <div><dt>Year</dt><dd><?= client('year') ?></dd></div>
      <div><dt>Role</dt><dd><?= client('your role') ?></dd></div>
      <div><dt>Made for</dt><dd><?= client('client or audience') ?></dd></div>
    </dl>
    <div class="prose">
      <figure class="shot"><div class="shot__slot"><?= client('screenshot or short screen recording, 16:9') ?></div></figure>
      <h2>The brief</h2>
      <p><?= client('what the client or project needed, in their terms') ?></p>
      <h2>What I built</h2>
      <p><?= client('the solution and the hard part you solved') ?></p>
      <h2>Result</h2>
      <p><?= client('a real, checkable outcome: a link, a launch, a quote with permission. No invented numbers.') ?></p>
    </div>
  </div>
</section>
<?php else:
    $band = ['id' => 'project-title', 'title' => e($p['title']), 'hour' => $p['hour'], 'progress' => '0.2', 'poster' => 'dusk',
             'slider' => !empty($p['slider']), 'lede' => e($p['lede'])];
    require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--night" aria-label="Case study">
  <div class="wrap split">
    <div class="facts-col">
      <dl class="facts">
<?php foreach ($p['facts'] as $dt => $dd): ?>
        <div><dt><?= e($dt) ?></dt><dd><?= e($dd) ?></dd></div>
<?php endforeach ?>
      </dl>
<?php if (!empty($p['link'])): ?>
      <p class="facts__link"><a class="link-arrow" href="<?= e($p['link'][0]) ?>" rel="noopener noreferrer" target="_blank"><?= e($p['link'][1]) ?><?= icon('external') ?><span class="visually-hidden"> (opens in a new tab)</span></a></p>
<?php endif ?>
    </div>
    <div class="prose">
<?php if (!empty($p['image'])): [$src, $w, $h, $alt] = $p['image']; ?>
      <figure class="shot shot--app">
        <picture>
          <source type="image/avif" srcset="<?= e(asset($src . '.avif')) ?>">
          <img src="<?= e(asset($src . '.webp')) ?>" width="<?= (int)$w ?>" height="<?= (int)$h ?>" alt="<?= e($alt) ?>" loading="lazy" decoding="async">
        </picture>
      </figure>
<?php endif ?>
<?php foreach ($p['sections'] as [$heading, $paras]): ?>
      <h2><?= e($heading) ?></h2>
<?php foreach ($paras as $para): ?>
      <p><?= e($para) ?></p>
<?php endforeach ?>
<?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>
<nav class="ground ground--dawn next" aria-label="Next project">
  <div class="wrap">
    <a class="next__link" href="<?= e(url('/work/' . $next['slug'])) ?>">
      <span class="next__label">Next fire up the slope</span>
      <span class="next__title"><?= $next['placeholder'] ? client('project name') : e($next['title']) ?><?= icon('arrow') ?></span>
    </a>
  </div>
</nav>
<?php
$title = $p['placeholder'] ? 'Case study (to be written)' : $p['title'];
return [
    'title' => $title,
    'description' => $p['placeholder'] ? 'A case study by Ramyar Salah, to be published.' : $p['summary'],
    'path' => '/work/' . $slug,
    'scene' => 'band',
    'current' => '/work',
    'noindex' => $p['placeholder'],
    'og_type' => 'article',
    'bodyClass' => 'page-inner',
    'jsonld' => $p['placeholder'] ? [] : [[
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $p['title'],
        'description' => $p['summary'],
        'dateCreated' => $p['year'],
        'url' => abs_url('/work/' . $slug),
        'sameAs' => empty($p['link']) ? [] : [$p['link'][0]],
        'creator' => ['@type' => 'Person', 'name' => 'Ramyar Salah', 'url' => abs_url('/')],
    ]],
];
