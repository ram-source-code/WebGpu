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
      <div><dt>Built with</dt><dd><?= client('tools') ?></dd></div>
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
    $band = ['id' => 'project-title', 'title' => e($p['title']), 'hour' => $p['hour'], 'progress' => '0.2', 'poster' => 'dusk', 'slider' => true,
             'lede' => 'The live scene behind this site. Drag the hour to move through the night.'];
    require ROOT . '/includes/partials/scene-band.php';
?>
<section class="ground ground--night" aria-label="Case study">
  <div class="wrap split">
    <dl class="facts">
      <div><dt>Year</dt><dd><?= e($p['year']) ?></dd></div>
      <div><dt>Role</dt><dd><?= e($p['role']) ?></dd></div>
      <div><dt>Built with</dt><dd><?= e(implode(', ', $p['stack'])) ?></dd></div>
      <div><dt>Runs on</dt><dd data-backend-fact>Your device’s GPU, when the browser allows it</dd></div>
    </dl>
    <div class="prose">
      <h2>The idea</h2>
      <p>On the eve of Newroz, the Kurdish new year on 21 March, fires are lit on the mountains. In towns like Akre, people carry torches up the hillside in a line that turns the whole slope into a road of light. The scene recreates that night: one ridge, one road, and every walker on it simulated.</p>

      <h2>How it works</h2>
      <ol class="steps">
        <li><strong>A ridge, baked once.</strong> The terrain is layered noise shaped into a Zagros-like massif with a range behind it. It is computed on the CPU in small slices so the page never freezes, then uploaded to the GPU as a height grid that every shader samples.</li>
        <li><strong>A compute pass per frame.</strong> Walkers move in small groups, so the line has gaps and clusters like a real procession. Each torch has a place on one of three valley approaches that merge into a single switchback road. A WGSL compute shader springs every torch toward its place, keeps it on the ground, and pulls it toward your pointer when you come close. Embers rise from the summit fire on a light wind, and bonfires flicker on the ranges behind.</li>
        <li><strong>One render pass.</strong> Sky, ridge and fire draw together. The ground is lit by the road itself: each fragment measures its distance to the switchbacks, so the slope glows where the people are.</li>
        <li><strong>Scroll is the hour.</strong> On the home page the night moves from dusk to first light as you read, and the camera climbs with it.</li>
      </ol>

      <h2>Built to degrade well</h2>
      <ul class="tiers">
        <li><span class="tiers__name">WebGPU</span> 14,336 torches, 6,144 embers and 64 far bonfires on a desktop GPU; 6,144 torches and 2,048 embers on phones. All of it moves in a compute pass.</li>
        <li><span class="tiers__name">WebGL2</span> The same scene, 10,240 torches on desktop and 4,096 on phones, with motion computed in the vertex shader instead of a compute pass.</li>
        <li><span class="tiers__name">No GPU</span> A still image of the same ridge, also used when the graphics chip only renders in software, when data saver is on, or with JavaScript off. Every page reads and works without the scene.</li>
      </ul>
      <p>If frames start running long, the scene lowers its resolution first, then halves the torches. A pause button sits under every scene, and with reduced motion turned on in your system settings the scene starts paused.</p>
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
        'creator' => ['@type' => 'Person', 'name' => 'Ramyar Salah', 'url' => abs_url('/')],
    ]],
];
