<?php
// Portfolio entries, in the order they climb the page. Real entries carry their case study here;
// a 'placeholder' entry is a slot for a project still to be written up (noindex, not in the sitemap).
return [
    [
        'slug'        => 'newroz-on-the-ridge',
        'title'       => 'Newroz on the ridge',
        'year'        => '2026',
        'role'        => 'Concept, design and development',
        'summary'     => 'The night scene that opens this site: a Zagros ridge and a torch procession climbing to a summit fire.',
        'placeholder' => false,
        'hour'        => '0.18',
        'lede'        => 'The night scene behind this site. Drag the hour to move through it.',
        'slider'      => true,
        'facts'       => [
            'Year' => '2026',
            'Role' => 'Concept, design and development',
            'Where' => 'Behind every page of this site',
        ],
        'sections'    => [
            ['The idea', [
                'On the eve of Newroz, the Kurdish new year on 21 March, fires are lit on the mountains. In towns like Akre, people carry torches up the hillside in a line that turns the whole slope into a road of light.',
                'The scene recreates that night: one ridge, one road climbing in switchbacks to a summit fire, and bonfires burning on the ranges behind. Every point of light is a walker with a place in the line.',
            ]],
            ['Things to try', [
                'Move your pointer over the ridge, or touch it: the walkers closest to you turn your way.',
                'Scroll the home page: the night passes from dusk to midnight to first light, and the view climbs with it.',
                'On the home page, point at a project: its stretch of the road lights up.',
                'Here, drag the hour above to move through the night yourself.',
            ]],
            ['Made to be gentle', [
                'A pause button sits under every scene, and with reduced motion turned on in your system settings the scene starts paused. On devices where it would not run smoothly, or with data saver on, you see a still of the same night instead, and every page reads and works without it.',
            ]],
        ],
    ],
    [
        'slug'        => 'case-study-two',
        'title'       => null,
        'year'        => null,
        'role'        => null,
        'summary'     => null,
        'placeholder' => true,
        'hour'        => '0.5',
    ],
    [
        'slug'        => 'case-study-three',
        'title'       => null,
        'year'        => null,
        'role'        => null,
        'summary'     => null,
        'placeholder' => true,
        'hour'        => '0.8',
    ],
];
