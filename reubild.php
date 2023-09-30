<?php

require 'vendor/autoload.php';
use DI\Container as DIContainer;
use Core\ORM\Database;
use Core\ORM\Entities\EntityDefs;
use Core\Utils\Config\Manager as ConfigManager;


$container = new DIContainer();
$db = $container->get(Database::class);
$entityDefs = $container->get(EntityDefs::class);
$configManager = new ConfigManager();
$sql = "SHOW TABLES";

$stm = $db->query($sql);
$resoult = $db->fetch($stm,3);
$resoult = array_map(function ($value) {
    return $value[0];
}, $resoult);
foreach ($resoult as $table) {
    $entityDefs->createEntityDefs($table);
}

