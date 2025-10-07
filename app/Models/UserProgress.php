<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    protected $table = 'user_progress';
    
    protected $fillable = [
        'user_id',
        'total_points',
        'daily_points',
        'weekly_points',
        'monthly_points',
        'level',
        'experience',
        'streak_days',
        'last_activity_date'
    ];

    protected $casts = [
        'total_points' => 'integer',
        'daily_points' => 'integer',
        'weekly_points' => 'integer',
        'monthly_points' => 'integer',
        'level' => 'integer',
        'experience' => 'integer',
        'streak_days' => 'integer',
        'last_activity_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addPoints($points)
    {
        $this->increment('total_points', $points);
        $this->increment('daily_points', $points);
        $this->increment('weekly_points', $points);
        $this->increment('monthly_points', $points);
        $this->increment('experience', $points);
        
        // Update last activity
        $this->update(['last_activity_date' => now()->toDateString()]);
        
        // Check for level up
        $this->checkLevelUp();
        
        // Update streak
        $this->updateStreak();
    }

    public function removePoints($points)
    {
        $this->decrement('total_points', max(0, $points));
        $this->decrement('daily_points', max(0, $points));
        $this->decrement('weekly_points', max(0, $points));
        $this->decrement('monthly_points', max(0, $points));
        $this->decrement('experience', max(0, $points));
        
        // Prevent negative values
        $this->update([
            'total_points' => max(0, $this->total_points),
            'daily_points' => max(0, $this->daily_points),
            'weekly_points' => max(0, $this->weekly_points),
            'monthly_points' => max(0, $this->monthly_points),
            'experience' => max(0, $this->experience)
        ]);
    }

    public function checkLevelUp()
    {
        $requiredXP = $this->getRequiredXPForLevel($this->level + 1);
        
        if ($this->experience >= $requiredXP) {
            $this->increment('level');
            
            // Award bonus points for leveling up
            $bonusPoints = $this->level * 10;
            $this->increment('total_points', $bonusPoints);
            
            return true;
        }
        
        return false;
    }

    public function getRequiredXPForLevel($level)
    {
        // XP required grows exponentially: Level 1 = 100, Level 2 = 250, Level 3 = 450, etc.
        return $level * 100 + (($level - 1) * 50);
    }

    public function getXPToNextLevel()
    {
        $nextLevelXP = $this->getRequiredXPForLevel($this->level + 1);
        return max(0, $nextLevelXP - $this->experience);
    }

    public function getXPProgress()
    {
        $currentLevelXP = $this->getRequiredXPForLevel($this->level);
        $nextLevelXP = $this->getRequiredXPForLevel($this->level + 1);
        $xpInCurrentLevel = $this->experience - $currentLevelXP;
        $xpRequiredForLevel = $nextLevelXP - $currentLevelXP;
        
        return $xpRequiredForLevel > 0 ? ($xpInCurrentLevel / $xpRequiredForLevel) * 100 : 0;
    }

    public function updateStreak()
    {
        $yesterday = now()->subDay()->toDateString();
        $today = now()->toDateString();
        
        // Check if user had activity yesterday or today
        $hadActivityYesterday = $this->user->calendarTasks()
            ->completed()
            ->whereDate('completed_at', $yesterday)
            ->exists();
            
        $hadActivityToday = $this->user->calendarTasks()
            ->completed()
            ->whereDate('completed_at', $today)
            ->exists();
        
        if ($hadActivityToday) {
            // If last activity was yesterday, continue streak
            if ($this->last_activity_date && $this->last_activity_date->toDateString() === $yesterday) {
                $this->increment('streak_days');
            } 
            // If starting new streak today
            elseif (!$this->last_activity_date || $this->last_activity_date->toDateString() !== $today) {
                $this->update(['streak_days' => 1]);
            }
        } 
        // If no activity today and last activity wasn't yesterday, reset streak
        elseif (!$hadActivityYesterday && $this->last_activity_date && $this->last_activity_date->toDateString() !== $yesterday) {
            $this->update(['streak_days' => 0]);
        }
    }

    public function resetDailyPoints()
    {
        $this->update(['daily_points' => 0]);
    }

    public function resetWeeklyPoints()
    {
        $this->update(['weekly_points' => 0]);
    }

    public function resetMonthlyPoints()
    {
        $this->update(['monthly_points' => 0]);
    }
}