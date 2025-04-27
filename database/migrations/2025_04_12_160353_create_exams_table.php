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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('formation');
            $table->string('filiere');
            $table->string('module');
            $table->string('semestre');
            $table->date('date_examen');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('locaux');
            $table->string('superviseurs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
