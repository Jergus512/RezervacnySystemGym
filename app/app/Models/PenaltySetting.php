<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenaltySetting extends Model
{
    use HasFactory;

    protected $table = 'penalty_settings';

    protected $fillable = [
        'refund_window_minutes',
        'penalty_policy',
    ];

    /**
     * Return the single settings row (create default if missing)
     */
    public static function getSingleton(): self
    {
        $row = self::first();
        if (! $row) {
            $row = self::create([
                // Default to 12 hours (720 minutes) so admins get a sensible default
                'refund_window_minutes' => 720,
                // Start with 'half' policy by default (only 'half' and 'none' are supported in the UI)
                'penalty_policy' => 'half',
            ]);
        }

        return $row;
    }
}
