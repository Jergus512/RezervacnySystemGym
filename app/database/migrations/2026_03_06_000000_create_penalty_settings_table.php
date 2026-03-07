<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penalty_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('refund_window_minutes')->default(60); // minutes before training start where full refund is allowed
            $table->enum('penalty_policy', ['half', 'none'])->default('half');
            $table->timestamps();
        });

        // Insert a default row so the app always has settings
        DB::table('penalty_settings')->insert([
            'refund_window_minutes' => 60,
            'penalty_policy' => 'half',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('penalty_settings');
    }
};
