<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if the table already exists
        if (!Schema::hasTable('concours_classroom_assignments')) {
            Schema::create('concours_classroom_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
                $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
                $table->foreignId('candidat_id')->constrained('candidats')->onDelete('cascade');
                $table->integer('seat_number');
                $table->timestamps();

                // Ensure a candidate can only be assigned once per concours
                $table->unique(['concours_id', 'candidat_id']);
                
                // Index for better performance on common queries
                $table->index(['concours_id', 'classroom_id']);
            });
            
            \Illuminate\Support\Facades\Log::info('Created concours_classroom_assignments table');
        } else {
            \Illuminate\Support\Facades\Log::info('concours_classroom_assignments table already exists');
        }
    }

    public function down()
    {
        Schema::dropIfExists('concours_classroom_assignments');
    }
};
