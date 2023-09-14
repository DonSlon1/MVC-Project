<?php

namespace Core;

abstract class Controller
{
    private array $route_params;

    public function __construct(array $route_params)
    {
        $this->route_params = $route_params;

    }

    public function getRouteParams(): array
    {
        return $this->route_params;
    }


}