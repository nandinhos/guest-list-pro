<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Middleware ===\n";

// Try to test if SetUpPanel middleware works
$middleware = new \Filament\Http\Middleware\SetUpPanel('admin');
echo "Middleware created\n";

// Check if the panel is properly set up
$panel = \Filament\Facades\Filament::getPanel('admin');
if ($panel) {
    echo "Panel 'admin' found!\n";
    echo 'Panel resources count: '.count($panel->getRegisteredResources())."\n";

    // List all registered resources
    echo "\nRegistered resources:\n";
    foreach ($panel->getRegisteredResources() as $resource) {
        echo '- '.get_class($resource)."\n";
    }
} else {
    echo "Panel 'admin' NOT found!\n";

    // List all panels
    $manager = \Filament\Facades\Filament::getManager();
    echo 'Available panels: '.json_encode($manager->getPanels())."\n";
}
