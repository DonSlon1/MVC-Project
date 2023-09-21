<?php
return[
    'database' => [
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'port' => '',
        'charset' => 'utf8mb4',
        'dbname' => 'protein_devcrm_cz',
        'user' => 'protein_devcrm_cz',
        'password' => 'TaUY1foK6zGtqGvGcl'
    ],
    'logDir' => dirname($_SERVER['DOCUMENT_ROOT']).'/data/log/',
    'defaultPermissions' => [
        'dir' => 0775,
        'file' => 0664
    ],
];