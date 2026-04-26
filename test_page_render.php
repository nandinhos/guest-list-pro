<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing ExcursaoResource ===\n\n";

try {
    $page = app(\App\Filament\Admin\Resources\Excursao\Pages\ListExcursoes::class);
    echo 'Page class: '.get_class($page)."\n";
    echo 'Resource: '.$page->getResource()."\n";

    // Try to render
    echo "\nTrying to render page...\n";
    $response = $page->render();

    if ($response) {
        echo 'Render successful!';
    } else {
        echo 'Render returned null';
    }
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
    echo 'Trace: '.$e->getTraceAsString()."\n";
}
