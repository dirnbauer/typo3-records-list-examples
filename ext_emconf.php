<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Records List Examples',
    'description' => 'Example view types for the Records List Types extension: Timeline, Catalog, Address Book, Event List, Photo Gallery, and Dashboard views.',
    'category' => 'be',
    'author' => 'Webconsulting',
    'author_email' => 'office@webconsulting.at',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'records_list_types' => '1.0.0-1.99.99',
        ],
    ],
];
