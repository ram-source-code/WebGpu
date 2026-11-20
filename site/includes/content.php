<?php
// Portfolio entries, in the order they climb the page. Only the first is real; the others are
// slots for the owner's own projects (spec: never invent content). A slot page is noindex and
// stays out of the sitemap until 'placeholder' is removed.
return [
    [
        'slug'        => 'newroz-on-the-ridge',
        'title'       => 'Newroz on the ridge',
        'year'        => '2026',
        'role'        => 'Design and WebGPU development',
        'summary'     => 'The scene behind this site: a Zagros ridge and a torch procession climbing to a summit fire, simulated on your GPU.',
        'stack'       => ['WebGPU', 'WGSL compute', 'WebGL2 fallback', 'PHP'],
        'placeholder' => false,
        'hour'        => '0.18',
    ],
    [
        'slug'        => 'case-study-two',
        'title'       => null,
        'year'        => null,
        'role'        => null,
        'summary'     => null,
        'stack'       => [],
        'placeholder' => true,
        'hour'        => '0.5',
    ],
    [
        'slug'        => 'case-study-three',
        'title'       => null,
        'year'        => null,
        'role'        => null,
        'summary'     => null,
        'stack'       => [],
        'placeholder' => true,
        'hour'        => '0.8',
    ],
];
