<?php

$EM_CONF = is_array($EM_CONF ?? null) ? $EM_CONF : [];
$EM_CONF['records_list_examples'] = [
    'title' => 'Records List Examples',
    'description' => 'Example view types for the Records List Types extension: Timeline, Catalog, Address Book, Event List, Photo Gallery, and Dashboard views.',
    'category' => 'be',
    'author' => 'Webconsulting',
    'author_email' => 'office@webconsulting.at',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '14.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.5.99',
            'typo3' => '14.3.0-14.99.99',
            'backend' => '14.3.0-14.99.99',
            'fluid' => '14.3.0-14.99.99',
            'records_list_types' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
