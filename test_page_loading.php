<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing ExcursaoResource pages ===\n\n";

// Test ListExcursoes page
echo "1. Testing ListExcursoes page:\n";
try {
    $page = app(\App\Filament\Admin\Resources\Excursao\Pages\ListExcursoes::class);
    echo '   - Page created: '.get_class($page)."\n";

    // Check if it has the right resource
    echo '   - Resource: '.$page->getResource()."\n";

    // Try to get the Eloquent query
    $resourceClass = $page->getResource();
    $query = $resourceClass::getEloquentQuery();
    echo '   - Query: '.$query->toSql()."\n";

    // Try to count records
    $count = $query->count();
    echo '   - Count: '.$count."\n";
} catch (Exception $e) {
    echo '   ERROR: '.$e->getMessage()."\n";
    echo '   File: '.$e->getFile().':'.$e->getLine()."\n";
    echo '   Trace: '.$e->getTraceAsString()."\n";
}

echo "\n2. Testing MonitorResource pages:\n";
try {
    $page = app(\App\Filament\Admin\Resources\Monitor\Pages\ListMonitores::class);
    echo '   - Page created: '.get_class($page)."\n";

    // Try to get the Eloquent query
    $query = $page->getResource()::getEloquentQuery();
    echo '   - Query SQL: '.$query->toSql()."\n";

    // Try to count records
    $count = $query->count();
    echo '   - Count: '.$count."\n";
} catch (Exception $e) {
    echo '   ERROR: '.$e->getMessage()."\n";
    echo '   File: '.$e->getFile().':'.$e->getLine()."\n";
}

echo "\n3. Testing EventResource pages (should work):\n";
try {
    $page = app(\App\Filament\Resources\Events\Pages\ListEvents::class);
    echo '   - Page created: '.get_class($page)."\n";

    // Try to get the Eloquent query
    $query = $page->getResource()::getEloquentQuery();
    echo '   - Count: '.$query->count()."\n";
} catch (Exception $e) {
    echo '   ERROR: '.$e->getMessage()."\n";
}
