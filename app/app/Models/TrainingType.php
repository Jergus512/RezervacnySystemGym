<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }
}

