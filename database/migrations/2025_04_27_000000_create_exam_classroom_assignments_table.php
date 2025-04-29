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
        Schema::create('exam_classroom_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('seat_number');
            $table->timestamps();

            // A student can only be assigned once per exam
            $table->unique(['exam_id', 'student_id'], 'exam_student_unique');

            // Seat numbers must be unique within a classroom for an exam
            $table->unique(['exam_id', 'classroom_id', 'seat_number'], 'exam_classroom_seat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_classroom_assignments');
    }
};
