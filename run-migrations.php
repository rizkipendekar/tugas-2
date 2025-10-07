<?php
// Simple migration runner
// Run this with: C:\laragon\bin\php\php-8.2.29-Win32-vs16-x86\php.exe run-migrations.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Running migrations...\n";

try {
    // Check if migrations table exists
    if (!Schema::hasTable('migrations')) {
        echo "Creating migrations table...\n";
        DB::statement("
            CREATE TABLE migrations (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                migration varchar(255) NOT NULL,
                batch int(11) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
    }

    // Create habits table
    if (!Schema::hasTable('habits')) {
        echo "Creating habits table...\n";
        DB::statement("
            CREATE TABLE habits (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                name varchar(255) NOT NULL,
                description text,
                frequency enum('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
                target_count int(11) NOT NULL DEFAULT '1',
                color varchar(255) NOT NULL DEFAULT '#3B82F6',
                is_active tinyint(1) NOT NULL DEFAULT '1',
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY habits_user_id_foreign (user_id),
                CONSTRAINT habits_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )
        ");
        
        // Add migration record
        DB::table('migrations')->insert([
            'migration' => '2025_10_07_000001_create_habits_table',
            'batch' => 1
        ]);
    }

    // Create habit_entries table
    if (!Schema::hasTable('habit_entries')) {
        echo "Creating habit_entries table...\n";
        DB::statement("
            CREATE TABLE habit_entries (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                habit_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                completed_at date NOT NULL,
                count int(11) NOT NULL DEFAULT '1',
                notes text,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY habit_entries_habit_id_user_id_completed_at_unique (habit_id,user_id,completed_at),
                KEY habit_entries_user_id_foreign (user_id),
                CONSTRAINT habit_entries_habit_id_foreign FOREIGN KEY (habit_id) REFERENCES habits (id) ON DELETE CASCADE,
                CONSTRAINT habit_entries_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )
        ");
        
        // Add migration record
        DB::table('migrations')->insert([
            'migration' => '2025_10_07_000002_create_habit_entries_table',
            'batch' => 1
        ]);
    }

    echo "✓ All migrations completed successfully!\n";
    echo "✓ Habits table created\n";
    echo "✓ Habit entries table created\n";
    echo "\nYou can now use the habit tracker!\n";

} catch (Exception $e) {
    echo "✗ Migration error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
}
?>