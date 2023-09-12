<?php
require '../vendor/autoload.php';


$router = new Core\Router();
$router->add( '{controller}');
$router->add( 'api/{controller}',['action'=>'send']);
$router->add( '{controller}/{action}/{id:\d+}');

$router->dispatch($_SERVER['QUERY_STRING'], Core\Enums\HttpMethods::from($_SERVER['REQUEST_METHOD']));