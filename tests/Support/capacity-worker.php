<?php

// Isolated worker: exercises the actual admission middleware without opening the application database.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->instance('request', \Illuminate\Http\Request::create('/tickets', 'POST'));
$app->make(\Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$directory = $argv[1];
config(['capacity.enabled' => true, 'capacity.requests' => 2, 'capacity.directory' => $directory]);
touch($directory.'/ready-'.getmypid());
$deadline = microtime(true) + 15;
while (!is_file($directory.'/go') && microtime(true) < $deadline) usleep(10000);
if (!is_file($directory.'/go')) exit(2);
$response = (new \App\Http\Middleware\LimitDatabaseConcurrency)->handle(
    \Illuminate\Http\Request::create('/tickets', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']),
    function () { usleep(1000000); return response('accepted'); }
);
echo $response->getStatusCode();
$app->terminate();
