<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model {
    protected $fillable = [
        'user_id',
        'goal_id',
        'title',
        'description',
        'badge_icon',
        'badge_color',
        'points_earned'
    ];

    protected $casts = [
        'points_earned' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}