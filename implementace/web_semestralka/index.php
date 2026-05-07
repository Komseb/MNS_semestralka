<?php
namespace App;
use App\Core\Router;

//utf-8 everywhere
mb_internal_encoding('UTF-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//start output buffering to prevent early output
ob_start();

//start session for auth
session_start();

//define base url constant
define('BASE_URL', getenv('BASE_URL') ?: '/web_semestralka/');

//try to load composer autoloader for twig and classmap
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

\App\Models\Post::attach(new \App\Core\Events\ActionLogger());
\App\Models\Comment::attach(new \App\Core\Events\ActionLogger());

//bootstrap router and dispatch controller
$router = new Router();
[$controllerName, $action, $params] = $router->resolve();

//prepend namespace to controller class name
$controllerClass = 'App\\Controllers\\' . $controllerName;

//when controller class does not exist, use error controller
if (!class_exists($controllerClass)) {
    $controllerClass = 'App\\Controllers\\ErrorController';
}

/** @var App\Core\BaseController $controller */
$controller = new $controllerClass();

//map action to method: for simplicity we use handle() always, params contain action
//controllers can switch based on $action if they need multiple actions
$controller->handle(array_merge([$action], $params));
