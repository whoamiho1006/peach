<?php

define ('PEACH_ROOT', dirname(__DIR__));
define ('PEACH_DATEFMT', 'Y-m-d\TH:i:s.v');

/*
 * Registers the autoload directory for current path.
 * */
spl_autoload_register(function($className) {
    $className = str_replace('\\', '/', $className);
    $classPath = __DIR__ . '/' . $className . '.php';
    
    if (file_exists($classPath) && is_readable($classPath)) {
        include_once ($classPath);
    }
});

/*
 * Includes vendor's autoloader.
 * */
include_once (__DIR__ . '/../vendor/autoload.php');

/*
 * Execute peach framework startup.
 * */
peach\Exec::exec();