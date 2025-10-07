<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class);
    }

    public function habitEntries(): HasMany
    {
        return $this->hasMany(HabitEntry::class);
    }

    public function calendarTasks(): HasMany
    {
        return $this->hasMany(CalendarTask::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function progress()
    {
        return $this->hasOne(UserProgress::class);
    }

    // Gamification methods
    public function awardPoints($points)
    {
        $progress = $this->progress ?: $this->progress()->create(['user_id' => $this->id]);
        $progress->addPoints($points);
        
        // Check if any goals are achieved
        $this->goals()->active()->each(function ($goal) {
            $goal->checkAndAchieve();
        });
        
        return $progress;
    }

    public function removePoints($points)
    {
        $progress = $this->progress;
        if ($progress) {
            $progress->removePoints($points);
        }
        return $progress;
    }

    public function getTotalPointsAttribute()
    {
        return $this->progress ? $this->progress->total_points : 0;
    }

    public function getLevelAttribute()
    {
        return $this->progress ? $this->progress->level : 1;
    }

    public function getStreakAttribute()
    {
        return $this->progress ? $this->progress->streak_days : 0;
    }
}
