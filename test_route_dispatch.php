<?php

// Test the route internally in the container
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the router
$router = app('router');

// Find the excursion route
$routes = $router->getRoutes();
foreach ($routes as $route) {
    if ($route->uri() === 'admin/excursao/excursaos') {
        echo "Found route!\n";
        echo 'URI: '.$route->uri()."\n";
        echo 'Action: '.$route->action['uses']."\n";
        echo 'Name: '.$route->getName()."\n";

        // Try to run the route handler
        echo "\nTrying to dispatch route...\n";

        // Create a mock request
        $request = request();
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        try {
            // Run the route
            $response = $route->run($request);

            if ($response) {
                echo 'Response status: '.$response->getStatusCode()."\n";
            } else {
                echo "Response is null\n";
            }
        } catch (Exception $e) {
            echo 'Error running route: '.$e->getMessage()."\n";
            echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
        }

        break;
    }
}
