<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (strpos($route->uri(), 'admin/excursao') !== false || strpos($route->uri(), 'admin/excursoes') !== false) {
        echo $route->uri().' -> '.$route->action['uses'].PHP_EOL;
    }
}
