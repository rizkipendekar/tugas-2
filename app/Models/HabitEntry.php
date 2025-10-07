<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitEntry extends Model
{
    protected $fillable = [
        'habit_id',
        'user_id',
        'completed_at',
        'count',
        'notes'
    ];

    protected $casts = [
        'completed_at' => 'date',
        'count' => 'integer'
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('completed_at', $date);
    }

    public function scopeForHabit($query, $habitId)
    {
        return $query->where('habit_id', $habitId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}