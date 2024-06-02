<?php

return [
    'db' => [
        'db'       => 'sqlite', /* db type */
        'host'     => '127.0.0.1', /* db host address */
        'port'     => '3306', /* db host port */
        'login'    => 'root', /* db login name */
        'password' => 'root', /* db login password */
        'database' => __DIR__ . '/TalesOfSouls.db', /* db name */
        'weight'   => 1000, /* db table weight */
    ],
    'page' => [
        'root'  => '/',
        'https' => false,
    ],
    'app' => [
        'path'    => __DIR__,
        'default' => [
            'app'   => 'Frontend',
            'id'    => 'frontend',
            'lang'  => 'en',
            'theme' => 'Frontend',
            'org'   => 1,
        ],
        'domains' => [
            '127.0.0.3' => [
                'app'   => 'Frontend',
                'id'    => 'frontend',
                'lang'  => 'en',
                'theme' => 'Frontend',
                'org'   => 2,
            ],
        ],
    ],
    'language' => [
        'en'
    ],
    'log' => [
        'file' => [
            'path' => __DIR__ . '/Logs',
        ],
    ],
];