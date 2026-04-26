<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Page Resolution ===\n";

// Test if the ListExcursoes page can be resolved
try {
    $pageClass = \App\Filament\Admin\Resources\Excursao\Pages\ListExcursoes::class;
    echo "Page class: $pageClass\n";

    $instance = app($pageClass);
    echo 'Page instantiated: '.get_class($instance)."\n";

    // Try to call the render method
    echo "Trying to render...\n";
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
}

echo "\n=== Testing Resource ===\n";

// Test if the ExcursaoResource can be resolved
try {
    $resourceClass = \App\Filament\Admin\Resources\Excursao\ExcursaoResource::class;
    echo "Resource class: $resourceClass\n";

    $instance = app($resourceClass);
    echo 'Resource instantiated: '.get_class($instance)."\n";

    // Check pages
    $pages = $instance::getPages();
    echo 'Pages: '.json_encode($pages)."\n";
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
}
