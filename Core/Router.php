<?php

namespace Core;

use Core\Enums\HttpMethods;


class Router
{
    private const NO_ROUTE = 404;
    private array $routes = [];

    private array $params = [];
    public function __construct()
    {
    }
    private function match(string $url, HttpMethods $methode): bool
    {
        foreach ($this->routes as $route => $value) {
            if (preg_match($route, $url, $matches) && $value->methode === $methode->value) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $value->params[$key] = $match;
                    }
                }

                $this->params = $value->params;
                return true;
            }
        }
        return false;
    }
    public function add(string $route,?array $params=[],HttpMethods $method=HttpMethods::GET): void
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case-insensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = (object)[
            "methode" => $method->value,
            "params" => $params,
        ];
    }


    public function dispatch($action, HttpMethods $method=HttpMethods::GET):void
    {
        if (!$this->match($action, $method)) {
            http_response_code(404);
            return;
        }
        $controller = $this->getNamespace() . $this->params['controller'];
        if (!class_exists($controller)) {
            http_response_code(404);
            return;
        }

        $controller = new $controller($this->params);
        $action = $this->params['action'];
        if (!method_exists($controller, $action)) {
            http_response_code(404);
            return;
        }
        $controller->$action();

    }

    private function getNamespace(): string
    {
        $namespace = 'App\Controllers\\';
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }
}