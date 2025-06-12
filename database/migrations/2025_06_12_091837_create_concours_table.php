<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('concours', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_concours');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('locaux')->nullable();
            $table->string('type_epreuve'); // 'écrit' or 'oral'
            $table->timestamps();
        });

        // Pivot tables for relations
        Schema::create('concours_candidat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
            $table->foreignId('candidat_id')->constrained('candidats')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('concours_superviseur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
            $table->foreignId('superviseur_id')->constrained('superviseurs')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('concours_professeur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
            $table->foreignId('professeur_id')->constrained('professeurs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concours_professeur');
        Schema::dropIfExists('concours_superviseur');
        Schema::dropIfExists('concours_candidat');
        Schema::dropIfExists('concours');
    }
};
