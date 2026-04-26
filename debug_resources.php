<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('IlluminateContractsConsoleKernel')->bootstrap();

// Get the Filament manager
$manager = \Filament\FilamentManager::getInstance();

// Get the admin panel
$panels = $manager->getPanels();
foreach ($panels as $panel) {
    echo 'Panel ID: '.$panel->getId()."\n";

    // Get all resources
    $resources = $panel->getResources();
    echo 'Resources count: '.count($resources)."\n";

    foreach ($resources as $resource) {
        echo '  - '.get_class($resource)."\n";
    }
}
