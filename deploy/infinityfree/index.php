<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$core = __DIR__.'/app-core';

if (file_exists($maintenance = $core.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $core.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $core.'/bootstrap/app.php';
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
