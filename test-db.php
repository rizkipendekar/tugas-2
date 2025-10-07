<?php
// Simple database test script
// Run this with: php test-db.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Testing database connection...\n";

try {
    // Test basic connection
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
    
    // Check if tables exist
    $tables = ['users', 'habits', 'habit_entries'];
    
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does not exist - please run migrations\n";
        }
    }
    
    // If habits table exists, test the model
    if (Schema::hasTable('habits')) {
        $habitCount = DB::table('habits')->count();
        echo "✓ Found $habitCount habits in database\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and run migrations\n";
}

echo "\nTo run migrations, use:\n";
echo "C:\\laragon\\bin\\php\\php-8.2.29-Win32-vs16-x86\\php.exe artisan migrate\n";
?>