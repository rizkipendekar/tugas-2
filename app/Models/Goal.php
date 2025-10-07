<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_points',
        'start_date',
        'end_date',
        'reward_type',
        'reward_name',
        'reward_icon',
        'reward_color',
        'achieved',
        'achieved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_points' => 'integer',
        'achieved' => 'boolean',
        'achieved_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('achieved', false)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeAchieved($query)
    {
        return $query->where('achieved', true);
    }

    public function getCurrentProgress()
    {
        $userProgress = $this->user->progress;
        if (!$userProgress) return 0;

        // Calculate points earned within goal period
        $pointsInPeriod = $this->user->calendarTasks()
            ->completed()
            ->whereBetween('completed_at', [$this->start_date, $this->end_date])
            ->sum('points');

        return min($pointsInPeriod, $this->target_points);
    }

    public function getProgressPercentage()
    {
        $currentProgress = $this->getCurrentProgress();
        return $this->target_points > 0 ? round(($currentProgress / $this->target_points) * 100) : 0;
    }

    public function isAchievable()
    {
        return $this->getCurrentProgress() >= $this->target_points;
    }

    public function checkAndAchieve()
    {
        if (!$this->achieved && $this->isAchievable()) {
            $this->update([
                'achieved' => true,
                'achieved_at' => now()
            ]);

            // Create achievement record
            Achievement::create([
                'user_id' => $this->user_id,
                'goal_id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'badge_icon' => $this->reward_icon,
                'badge_color' => $this->reward_color,
                'points_earned' => $this->target_points
            ]);

            // Award bonus points for achieving goal
            $bonusPoints = round($this->target_points * 0.2); // 20% bonus
            $this->user->awardPoints($bonusPoints);

            return true;
        }

        return false;
    }

    public function getDaysRemainingAttribute()
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->gt($this->end_date);
    }
}