<?php

namespace App\Core;

class Router
{
    //resolve controller and action from request
    public function resolve(): array
    {
        //prefer query param r like home/index, fallback to path
        $route = isset($_GET['r']) ? (string)$_GET['r'] : $this->fromPath();
        $route = trim($route, "/ ");
        if ($route === '') {
            $route = 'landing/index';
        }

        $parts = explode('/', $route);
        $controller = $this->toPascal($parts[0] ?? 'landing') . 'Controller';
        $action = $parts[1] ?? 'index';
        $params = array_slice($parts, 2);
        return [$controller, $action, $params];
    }

    //parse from request uri when r is not provided
    private function fromPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        //strip base url if defined
        if (defined('BASE_URL') && str_starts_with($path, BASE_URL)) {
            $path = substr($path, strlen(BASE_URL));
        }

        return $path;
    }

    //convert kebab-case to PascalCase
    private function toPascal(string $name): string
    {
        $name = strtolower($name);
        $name = str_replace('-', ' ', $name);
        $name = str_replace(' ', '', ucwords($name));
        return $name;
    }
}
