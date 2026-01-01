<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "DB check...\n";
$count = Illuminate\Support\Facades\DB::table('users')->count();
echo "Total users: $count\n";

echo "Searching for user via DB facade...\n";
$user_raw = Illuminate\Support\Facades\DB::table('users')->where('email', 'admin@digiskul.app')->first();
echo "Raw user found: " . ($user_raw ? "Yes" : "No") . "\n";

echo "Searching for user via Model...\n";
$user = User::find(1);
echo "Model user found: " . ($user ? $user->name : "No") . "\n";

echo "Checking password...\n";
if (Hash::check('password', $user->password)) {
    echo "Password correct!\n";
} else {
    echo "Password incorrect!\n";
}

echo "Updating last login...\n";
$user->update(['last_login' => now()]);
echo "Last login updated.\n";

echo "Creating token...\n";
$token = $user->createToken('test-token')->plainTextToken;
echo "Token created: " . $token . "\n";

echo "Success!\n";
