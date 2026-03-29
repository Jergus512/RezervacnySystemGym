<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'period_start',
        'period_end',
        'trainings_count',
        'total_registrations',
        'canceled_registrations',
        'avg_occupancy',
        'credits_gained',
        'avg_user_rating',
        'base_reward',
        'rating_bonus',
        'performance_bonus',
        'total_reward',
        'reward_notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'trainings_count' => 'integer',
        'total_registrations' => 'integer',
        'canceled_registrations' => 'integer',
        'avg_occupancy' => 'float',
        'credits_gained' => 'integer',
        'avg_user_rating' => 'float',
        'base_reward' => 'float',
        'rating_bonus' => 'float',
        'performance_bonus' => 'float',
        'total_reward' => 'float',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }
}
