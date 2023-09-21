<?php
require '../vendor/autoload.php';

use Core\Enums\HttpMethods;
$fileManager = new Core\Utils\File\Manager(new Core\Utils\Config\Manager());

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