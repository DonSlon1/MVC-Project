<?php
require '../reubild.php';
return;
require '../vendor/autoload.php';

use Core\Enums\HttpMethods;
use DI\Container;
$fileManager = new Core\Utils\File\Manager(new Core\Utils\Config\Manager());
$entityDef = new Core\ORM\EntityDef('m:n');
echo(json_encode($entityDef->getTableInfo(), JSON_PRETTY_PRINT));
/*$entityRepository = new Core\ORM\EntityRepository('test2');
$entity = $entityRepository->getEntityById(2);
$entity->set('test', 'test2');
$entityRepository->saveEntity($entity);
print_r($entity->getId());*/

return;

$jsonRoute = $fileManager->getJsonContents('../App/Resources/routes.json');
$router = new Core\Router();
foreach ($jsonRoute as $route) {

    if (HttpMethods::tryFrom($route['method']) !== null)
        $method = HttpMethods::from($route['method']);
    else
        $method = HttpMethods::GET;
    $router->add($route['route'], $route['params'], $method);
}

$router->dispatch($_SERVER['QUERY_STRING'], Core\Enums\HttpMethods::from($_SERVER['REQUEST_METHOD']));