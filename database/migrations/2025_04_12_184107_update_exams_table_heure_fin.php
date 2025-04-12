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
        // Check if the heure_fin column exists, if not add it
        if (!Schema::hasColumn('exams', 'heure_fin')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->time('heure_fin')->after('heure_debut');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the heure_fin column exists, if yes drop it
        if (Schema::hasColumn('exams', 'heure_fin')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('heure_fin');
            });
        }
    }
};
