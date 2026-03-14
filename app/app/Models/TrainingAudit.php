<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'performed_by_user_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}

