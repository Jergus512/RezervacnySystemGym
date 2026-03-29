<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerStatistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'trainings_count',
        'total_participants',
        'unique_participants',
        'total_registrations',
        'canceled_registrations',
        'avg_occupancy',
        'credits_gained',
        'total_capacity',
        'performance_rating',
        'loyalty_score',
        'cancellation_rate',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'trainings_count' => 'integer',
        'total_participants' => 'integer',
        'unique_participants' => 'integer',
        'total_registrations' => 'integer',
        'canceled_registrations' => 'integer',
        'avg_occupancy' => 'float',
        'credits_gained' => 'integer',
        'total_capacity' => 'integer',
        'performance_rating' => 'float',
        'loyalty_score' => 'float',
        'cancellation_rate' => 'integer',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
