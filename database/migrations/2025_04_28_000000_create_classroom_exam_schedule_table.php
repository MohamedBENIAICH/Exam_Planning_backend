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
        Schema::create('classroom_exam_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->date('date_examen');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();

            // Add unique constraint to prevent double booking
            $table->unique(['classroom_id', 'date_examen', 'heure_debut', 'heure_fin'], 'classroom_schedule_unique');

            // Add index for faster queries
            $table->index(['date_examen', 'heure_debut', 'heure_fin'], 'schedule_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_exam_schedule');
    }
};
