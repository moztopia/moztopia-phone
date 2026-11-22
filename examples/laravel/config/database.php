<?php

declare(strict_types=1);

return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => storage_path('example.sqlite'),
            'foreign_key_constraints' => true,
        ],
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => false],
];
