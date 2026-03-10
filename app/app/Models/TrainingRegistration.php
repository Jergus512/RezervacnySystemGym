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
}

