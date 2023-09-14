<?php
require '../vendor/autoload.php';


$router = new Core\Router();
$router->add( '{controller}');
$router->add( 'api/{controller}',['action'=>'send']);
$router->add( '{controller}/{action}');
$router->add( '{controller}/{action}/{year}/{month}');

$router->dispatch($_SERVER['QUERY_STRING'], Core\Enums\HttpMethods::from($_SERVER['REQUEST_METHOD']));