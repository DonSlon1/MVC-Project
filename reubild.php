<?php

require 'vendor/autoload.php';
use DI\Container as DIContainer;
use Core\ORM\Database;
use Core\Utils\File\Manager as FileManager;


$container = new DIContainer();
$db = $container->get(Database::class);
$fileManager = $container->get(FileManager::class);
$sql = "SHOW TABLES";

$stm = $db->query($sql);
$resoult = $db->fetch($stm,3);
$resoult = array_map(function ($value) {
    return $value[0];
}, $resoult);
$tables = [];
foreach ($resoult as $table) {
    $sql = "
    SELECT
        `TABLE_NAME` as 'tableName',                            -- Foreign key table
        `COLUMN_NAME` as 'columnName',                           -- Foreign key column
        `REFERENCED_TABLE_NAME` as 'referencedTableName',                 -- Origin key table
        `REFERENCED_COLUMN_NAME` as 'referencedColumnName'               -- Origin key column
    FROM
        `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`  -- Will fail if user don't have privilege
    WHERE
        `TABLE_SCHEMA` = SCHEMA()                -- Detect current schema in USE
    AND `REFERENCED_TABLE_NAME` IS NOT NULL
    AND (TABLE_NAME = '$table' OR REFERENCED_TABLE_NAME = '$table'); -- On
";
    $stm = $db->query($sql);
    $resoult = $db->fetch($stm);
    foreach ($resoult as $key => $value) {
        if ($value['tableName'] === $table) {
            $resoult[$key]['type'] = '1:n';
        } else {
            $resoult[$key]['type'] = 'n:1';
        }
    }
    $tableInfo = [
        'relations' => $resoult
    ];
    $sql = "
    SELECT
        COLUMN_NAME as 'columnName',
        DATA_TYPE as 'dataType',
        IS_NULLABLE as 'isNullable',
        COLUMN_TYPE as 'columnType'
    FROM information_schema.COLUMNS WHERE TABLE_NAME = '$table'";
    $stm = $db->query($sql);
    $columns = $db->fetch($stm);
    foreach ($columns as $key => $value) {
        foreach ($tableInfo['relations'] as $relation) {
            if ($relation['columnName'] === $value['columnName'] && $relation['tableName'] === $table) {
                $columns[$key]['type'] = 'link';
            }
        }
    }
    $tableInfo['columns'] = $columns;
    $tables[$table] = $tableInfo;
}
foreach ($tables as $key => $value) {
    $fileManager->putJsonContents("/var/httpd/dummy-host2/App/Resources/entityDef1/$key.json", $value);
}
// print_r($resoult);

