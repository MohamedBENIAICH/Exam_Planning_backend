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
        // First, make the column nullable to avoid data truncation
        Schema::table('concours', function (Blueprint $table) {
            $table->text('locaux')->nullable()->change();
        });
        
        // Then convert to JSON using raw SQL
        DB::statement('ALTER TABLE concours MODIFY locaux JSON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to text
        Schema::table('concours', function (Blueprint $table) {
            $table->text('locaux')->nullable()->change();
        });
    }
};
