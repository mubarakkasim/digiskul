<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();
echo "Booted\n";
try {
    $users = Illuminate\Support\Facades\DB::table('users')->count();
    echo "Users: " . $users . "\n";
} catch (\Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
