<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Builder;

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
        static::addGlobalScope('only_active', function (Builder $query) {
            // Use the concrete table name to avoid referencing $query->from,
            // which can cause recursion in some Laravel internals.
            $query->where('training_registrations.status', '!=', 'canceled');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
