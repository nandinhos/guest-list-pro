
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('IlluminateContractsHttpKernel')->bootstrap();

echo '=== Testing HTTP Request to admin/excursoes ===
';

// Create request
$request = Request::create('/admin/excursoes', 'GET');
$request->headers->set('Accept', 'text/html');

// Try to handle the request
try {
    $kernel = app('IlluminateContractsHttpKernel');
    $response = $kernel->handle($request);
    echo 'Response status: '.$response->getStatusCode().'
';
    echo 'Response content (first 200 chars): '.substr($response->getContent(), 0, 200).'
';
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage().'
';
    echo 'File: '.$e->getFile().':'.$e->getLine().'
';
}
