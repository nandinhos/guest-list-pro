<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Filament resources...\n";

$manager = \Filament\FilamentManager::getInstance();
$panels = $manager->getPanels();
echo 'Found '.count($panels)." panels:\n";

foreach ($panels as $panel) {
    echo '- ID: '.$panel->getId()."\n";
    $resources = $panel->getRegisteredResources();
    echo '  Resources: '.count($resources)."\n";
    foreach ($resources as $resource) {
        echo '  - '.get_class($resource)."\n";
    }
}
