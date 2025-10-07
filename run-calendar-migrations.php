<?php
// Calendar system migration runner
// Run this with: C:\laragon\bin\php\php-8.2.29-Win32-vs16-x86\php.exe run-calendar-migrations.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Running Calendar and Gamification migrations...\n";

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

    // Create calendar_tasks table
    if (!Schema::hasTable('calendar_tasks')) {
        echo "Creating calendar_tasks table...\n";
        DB::statement("
            CREATE TABLE calendar_tasks (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                title varchar(255) NOT NULL,
                description text,
                task_date date NOT NULL,
                task_time time NULL,
                points int(11) NOT NULL DEFAULT '10',
                priority enum('low','medium','high') NOT NULL DEFAULT 'medium',
                category varchar(255) NULL,
                completed tinyint(1) NOT NULL DEFAULT '0',
                completed_at timestamp NULL DEFAULT NULL,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY calendar_tasks_user_id_foreign (user_id),
                KEY calendar_tasks_user_id_task_date_index (user_id,task_date),
                CONSTRAINT calendar_tasks_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )
        ");
        
        DB::table('migrations')->insert([
            'migration' => '2025_10_07_100001_create_calendar_tasks_table',
            'batch' => 2
        ]);
    }

    // Create goals table
    if (!Schema::hasTable('goals')) {
        echo "Creating goals table...\n";
        DB::statement("
            CREATE TABLE goals (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                title varchar(255) NOT NULL,
                description text,
                target_points int(11) NOT NULL,
                start_date date NOT NULL,
                end_date date NOT NULL,
                reward_type varchar(255) NOT NULL DEFAULT 'badge',
                reward_name varchar(255) NULL,
                reward_icon varchar(255) NULL,
                reward_color varchar(255) NOT NULL DEFAULT '#FFD700',
                achieved tinyint(1) NOT NULL DEFAULT '0',
                achieved_at timestamp NULL DEFAULT NULL,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY goals_user_id_foreign (user_id),
                CONSTRAINT goals_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )
        ");
        
        DB::table('migrations')->insert([
            'migration' => '2025_10_07_100002_create_goals_table',
            'batch' => 2
        ]);
    }

    // Create user_progress table
    if (!Schema::hasTable('user_progress')) {
        echo "Creating user_progress table...\n";
        DB::statement("
            CREATE TABLE user_progress (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                total_points int(11) NOT NULL DEFAULT '0',
                daily_points int(11) NOT NULL DEFAULT '0',
                weekly_points int(11) NOT NULL DEFAULT '0',
                monthly_points int(11) NOT NULL DEFAULT '0',
                level int(11) NOT NULL DEFAULT '1',
                experience int(11) NOT NULL DEFAULT '0',
                streak_days int(11) NOT NULL DEFAULT '0',
                last_activity_date date NULL,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY user_progress_user_id_unique (user_id),
                CONSTRAINT user_progress_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )
        ");
        
        DB::table('migrations')->insert([
            'migration' => '2025_10_07_100003_create_user_progress_table',
            'batch' => 2
        ]);
    }

    // Create achievements table
    if (!Schema::hasTable('achievements')) {
        echo "Creating achievements table...\n";
        DB::statement("
            CREATE TABLE achievements (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                goal_id bigint(20) unsigned NULL,
                title varchar(255) NOT NULL,
                description text NOT NULL,
                badge_icon varchar(255) NULL,
                badge_color varchar(255) NOT NULL DEFAULT '#FFD700',
                points_earned int(11) NOT NULL DEFAULT '0',
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY achievements_user_id_foreign (user_id),
                KEY achievements_goal_id_foreign (goal_id),
                CONSTRAINT achievements_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                CONSTRAINT achievements_goal_id_foreign FOREIGN KEY (goal_id) REFERENCES goals (id) ON DELETE SET NULL
            )
        ");
        
        DB::table('migrations')->insert([
            'migration' => '2025_10_07_100004_create_achievements_table',
            'batch' => 2
        ]);
    }

    echo "✓ All Calendar and Gamification migrations completed successfully!\n";
    echo "✓ Calendar tasks table created\n";
    echo "✓ Goals table created\n";
    echo "✓ User progress table created\n";
    echo "✓ Achievements table created\n";
    echo "\n🎯 Calendar gamification system is now ready!\n";
    echo "\nFeatures available:\n";
    echo "- ✅ Add tasks to calendar with points\n";
    echo "- ✅ Set goals and track progress\n";
    echo "- ✅ Earn points and level up\n";
    echo "- ✅ Track daily/weekly/monthly streaks\n";
    echo "- ✅ Unlock achievements and badges\n";
    echo "\nYou can now visit /calendar to start using the system!\n";

} catch (Exception $e) {
    echo "✗ Migration error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
}
?>