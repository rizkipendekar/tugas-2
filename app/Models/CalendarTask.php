<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CalendarTask extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'task_date',
        'task_time',
        'points',
        'priority',
        'category',
        'completed',
        'completed_at'
    ];

    protected $casts = [
        'task_date' => 'date',
        'task_time' => 'datetime:H:i',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'points' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('task_date', $date);
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'high' => '#EF4444',
            'medium' => '#F59E0B',
            'low' => '#10B981',
            default => '#6B7280'
        };
    }

    public function getFormattedTimeAttribute()
    {
        return $this->task_time ? Carbon::parse($this->task_time)->format('H:i') : null;
    }

    public function markAsCompleted()
    {
        $this->update([
            'completed' => true,
            'completed_at' => now()
        ]);

        // Award points to user
        $this->user->awardPoints($this->points);
        
        return $this;
    }

    public function markAsIncomplete()
    {
        $previouslyCompleted = $this->completed;
        
        $this->update([
            'completed' => false,
            'completed_at' => null
        ]);

        // Remove points if previously completed
        if ($previouslyCompleted) {
            $this->user->removePoints($this->points);
        }
        
        return $this;
    }
}