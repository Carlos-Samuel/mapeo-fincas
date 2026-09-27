<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// La carpeta pública es la de este archivo. En cPanel este index.php vive en
// public_html/ (fuera del proyecto) y así public_path() —y el disco "uploads"— apuntan ahí.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
