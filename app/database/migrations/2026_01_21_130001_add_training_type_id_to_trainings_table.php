<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->foreignId('training_type_id')
                ->nullable()
                ->after('created_by_user_id')
                ->constrained('training_types')
                ->nullOnDelete();

            $table->index('training_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_type_id');
        });
    }
};

