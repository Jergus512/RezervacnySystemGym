<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make credits nullable so role accounts can have NULL.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('credits')->nullable()->default(null)->change();
        });

        // Ensure role accounts don't have credits.
        DB::table('users')
            ->where('is_admin', true)
            ->orWhere('is_trainer', true)
            ->orWhere('is_reception', true)
            ->update(['credits' => null]);

        // Ensure regular users always have an integer value.
        DB::table('users')
            ->where(function ($q) {
                $q->where('is_admin', false)
                    ->where('is_trainer', false)
                    ->where('is_reception', false);
            })
            ->whereNull('credits')
            ->update(['credits' => 0]);
    }

    public function down(): void
    {
        // Revert: make credits non-nullable with default 0.
        DB::table('users')->whereNull('credits')->update(['credits' => 0]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('credits')->default(0)->nullable(false)->change();
        });
    }
};

