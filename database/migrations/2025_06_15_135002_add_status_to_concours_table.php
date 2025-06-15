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
        Schema::table('concours', function (Blueprint $table) {
            $table->enum('status', ['active', 'cancelled', 'completed'])->default('active')->after('type_epreuve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concours', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
