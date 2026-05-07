<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    //singleton twig environment
    private static ?Environment $twig = null;

    //get twig environment, initialize if needed
    public static function twig(): Environment
    {
        if (self::$twig === null) {
            //setup filesystem loader pointing to templates directory
            $loader = new FilesystemLoader(__DIR__ . '/../views/templates');
            //enable autoescape for html by default
            self::$twig = new Environment($loader, [
                'cache' => false,
                'autoescape' => 'html',
            ]);

            //compute base url like /web_semestralka/
            //works for subfolder deployments in xampp
            $script = $_SERVER['SCRIPT_NAME'] ?? '/';
            $dir = str_replace('\\', '/', dirname($script));
            $dir = $dir === '/' || $dir === '\\' || $dir === '.' ? '' : $dir;
            $base = ($dir ? $dir : '') . '/';

            //expose base and year globally to all templates
            self::$twig->addGlobal('base', $base);
            self::$twig->addGlobal('year', date('Y'));
        }
        return self::$twig;
    }
}
