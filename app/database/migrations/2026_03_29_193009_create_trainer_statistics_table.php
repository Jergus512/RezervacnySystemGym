<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trainer_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start'); // Od akého dátumu
            $table->date('period_end'); // Do akého dátumu

            // Základné štatistiky
            $table->integer('trainings_count')->default(0); // Počet cvičení
            $table->integer('total_participants')->default(0); // Počet všetkých zúčastňujúcich sa
            $table->integer('unique_participants')->default(0); // Počet unikátnych účastníkov
            $table->integer('total_registrations')->default(0); // Počet registrácií
            $table->integer('canceled_registrations')->default(0); // Počet zrušených
            $table->decimal('avg_occupancy', 5, 2)->default(0); // Priemerná obsadenosť (%)

            // Finančné štatistiky
            $table->integer('credits_gained')->default(0); // Kredity získané
            $table->integer('total_capacity')->default(0); // Celková kapacita tréningov

            // Hodnotenie
            $table->decimal('performance_rating', 3, 2)->nullable(); // Skóre výkonu 1-5
            $table->decimal('loyalty_score', 3, 2)->nullable(); // Skóre lojalnosti (opakovateľnosť)
            $table->integer('cancellation_rate')->default(0); // Percento zrušení

            $table->timestamps();

            // Indexy
            $table->unique(['user_id', 'period_start', 'period_end']);
            $table->index(['user_id', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_statistics');
    }
};
