<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For existing installations: convert any legacy 'full' values to 'half'
        try {
            DB::table('penalty_settings')->where('penalty_policy', 'full')->update(['penalty_policy' => 'half']);
        } catch (\Throwable $e) {
            // Silently ignore if table doesn't exist or DB driver doesn't support this operation in the current environment.
        }
    }

    public function down()
    {
        // Irreversible in general: we won't try to reconstruct original 'full' values here.
        // If you need to revert, manually update the rows as required.
    }
};

