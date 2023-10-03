<?php
return[
    'database' => [
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.4',
        'port' => '',
        'charset' => 'utf8mb4',
        'dbname' => 'mvc-projekt',
        'user' => 'mvc-projekt',
        'password' => 'mvc-projekt'
    ],
    'logDir' => dirname(__DIR__, 2).'data/log/',
    'rootDir' => dirname(__DIR__, 2),
    'defaultPermissions' => [
        'dir' => 0775,
        'file' => 0664
    ],
];