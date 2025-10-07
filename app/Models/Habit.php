<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Habit extends Model
{
    protected $fillable = [
        'name',
        'description',
        'frequency',
        'target_count',
        'color',
        'is_active',
        'user_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_count' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(HabitEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    // Get entries for a specific date
    public function getEntryForDate($date)
    {
        $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        return $this->entries()
            ->where('completed_at', $dateString)
            ->first();
    }

    // Check if habit is completed for a specific date
    public function isCompletedForDate($date)
    {
        $entry = $this->getEntryForDate($date);
        return $entry && $entry->count >= $this->target_count;
    }

    // Get current streak
    public function getCurrentStreak()
    {
        $streak = 0;
        $currentDate = Carbon::today();
        
        while (true) {
            if ($this->isCompletedForDate($currentDate)) {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }
        
        return $streak;
    }

    // Get completion percentage for current week
    public function getWeeklyCompletionPercentage()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $completedDays = 0;
        $totalDays = 0;
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            if ($date->lte(Carbon::today())) {
                $totalDays++;
                if ($this->isCompletedForDate($date)) {
                    $completedDays++;
                }
            }
        }
        
        return $totalDays > 0 ? round(($completedDays / $totalDays) * 100) : 0;
    }

    // Get completion percentage for current month
    public function getMonthlyCompletionPercentage()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $completedDays = 0;
        $totalDays = 0;
        
        $currentDate = $startOfMonth->copy();
        while ($currentDate->lte(Carbon::today()) && $currentDate->month === Carbon::now()->month) {
            $totalDays++;
            if ($this->isCompletedForDate($currentDate)) {
                $completedDays++;
            }
            $currentDate->addDay();
        }
        
        return $totalDays > 0 ? round(($completedDays / $totalDays) * 100) : 0;
    }
}