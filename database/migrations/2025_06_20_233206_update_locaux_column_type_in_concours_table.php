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
        // First, create a new JSON column
        Schema::table('concours', function (Blueprint $table) {
            $table->json('locaux_json')->after('locaux')->nullable();
        });
        
        // Copy and convert existing data to the new column
        $concoursList = DB::table('concours')->get();
        foreach ($concoursList as $concours) {
            if ($concours->locaux) {
                try {
                    // Try to decode and re-encode to ensure valid JSON
                    $locaux = json_decode($concours->locaux, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        DB::table('concours')
                            ->where('id', $concours->id)
                            ->update(['locaux_json' => json_encode($locaux)]);
                    } else {
                        // If invalid JSON, set to empty array
                        DB::table('concours')
                            ->where('id', $concours->id)
                            ->update(['locaux_json' => '[]']);
                    }
                } catch (\Exception $e) {
                    // In case of any error, set to empty array
                    DB::table('concours')
                        ->where('id', $concours->id)
                        ->update(['locaux_json' => '[]']);
                }
            }
        }
        
        // Rename columns to swap them using raw SQL for MariaDB compatibility
        DB::statement('ALTER TABLE concours CHANGE locaux locaux_old TEXT');
        DB::statement('ALTER TABLE concours CHANGE locaux_json locaux JSON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If we have the old column, restore it
        if (Schema::hasColumn('concours', 'locaux_old')) {
            // Drop the new column and restore the old one using raw SQL for MariaDB
            DB::statement('ALTER TABLE concours DROP COLUMN locaux');
            DB::statement('ALTER TABLE concours CHANGE locaux_old locaux TEXT');
        } else if (Schema::hasColumn('concours', 'locaux')) {
            // If for some reason we don't have the old column, just drop the new one
            Schema::table('concours', function (Blueprint $table) {
                $table->dropColumn('locaux');
            });
        }
    }
};
