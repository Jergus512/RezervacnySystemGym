<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateTrainerRatings extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['trainer_id', 'created_at']);
            $table->unique(['trainer_id', 'user_id', 'training_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('trainer_ratings');
    }
}
