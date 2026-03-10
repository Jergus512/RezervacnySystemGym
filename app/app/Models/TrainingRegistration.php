<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrainingRegistration extends Pivot
{
    protected $table = 'training_registrations';

    protected $fillable = [
        'training_id',
        'user_id',
        'status',
    ];

    // Ensure canceled registrations are not returned by default
    protected $attributes = [
        'status' => 'active',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('only_active', function ($query) {
            $query->where($query->from.'.status', '!=', 'canceled');
        });
    }
}
