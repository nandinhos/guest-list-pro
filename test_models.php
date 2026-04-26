<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Excursao model...\n";

try {
    $count = App\Models\Excursao::count();
    echo 'Excursao table exists, count: '.$count."\n";
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}

echo "\nTesting Monitor model...\n";
try {
    $count = App\Models\Monitor::count();
    echo 'Monitor table exists, count: '.$count."\n";
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
