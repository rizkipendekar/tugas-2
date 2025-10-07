<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('target_points'); // Points needed to achieve goal
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reward_type')->default('badge'); // badge, title, achievement
            $table->string('reward_name')->nullable(); // Name of the reward
            $table->string('reward_icon')->nullable(); // Icon for the reward
            $table->string('reward_color')->default('#FFD700'); // Color for the reward
            $table->boolean('achieved')->default(false);
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};