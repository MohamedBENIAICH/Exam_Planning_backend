<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('superviseurs', function (Blueprint $table) {
            $table->string('poste')->after('prenom');
        });

        DB::statement('ALTER TABLE superviseurs CHANGE type poste VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('superviseurs', function (Blueprint $table) {
            $table->string('type')->after('prenom');
        });

        DB::statement('ALTER TABLE superviseurs CHANGE poste type VARCHAR(255)');
    }
};
