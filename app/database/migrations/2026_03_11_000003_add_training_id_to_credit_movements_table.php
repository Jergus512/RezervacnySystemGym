<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('credit_movements', function (Blueprint $table) {
            // Nullable, lebo historické záznamy nemusia mať väzbu na tréning
            $table->unsignedBigInteger('training_id')->nullable()->after('user_id');

            // Index kvôli výkonu pri filtrovaní podľa tréningu
            $table->index('training_id', 'credit_movements_training_id_index');

            // Voliteľne FK, ale s cascade on delete, aby sa záznamy dali mazať spolu s tréningom
            $table->foreign('training_id', 'credit_movements_training_id_foreign')
                ->references('id')->on('trainings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('credit_movements', function (Blueprint $table) {
            if (Schema::hasColumn('credit_movements', 'training_id')) {
                $table->dropForeign(['training_id']);
                $table->dropIndex(['training_id']);
                $table->dropColumn('training_id');
            }
        });
    }
};
