<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateTrainerRewards extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('trainings_count')->default(0);
            $table->integer('total_registrations')->default(0);
            $table->integer('canceled_registrations')->default(0);
            $table->decimal('avg_occupancy', 5, 2)->default(0);
            $table->integer('credits_gained')->default(0);
            $table->decimal('avg_user_rating', 3, 2)->nullable();
            $table->decimal('base_reward', 10, 2)->default(0);
            $table->decimal('rating_bonus', 10, 2)->default(0);
            $table->decimal('performance_bonus', 10, 2)->default(0);
            $table->decimal('total_reward', 10, 2)->default(0);
            $table->text('reward_notes')->nullable();
            $table->timestamps();
            $table->unique(['trainer_id', 'period_start', 'period_end']);
            $table->index(['trainer_id', 'period_start']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('trainer_rewards');
    }
}
