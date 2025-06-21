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
        // First, create a temporary table with the same structure as concours
        Schema::create('concours_temp', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_concours');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->json('locaux')->nullable();
            $table->string('type_epreuve');
            $table->string('status')->default('active');
            $table->timestamps();
        });
        
        // Copy data from the original table to the temporary table
        $concoursList = DB::table('concours')->get();
        foreach ($concoursList as $concours) {
            $data = (array)$concours;
            
            // Convert locaux to valid JSON if it's a string
            if (isset($data['locaux']) && is_string($data['locaux'])) {
                try {
                    $locaux = json_decode($data['locaux'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['locaux'] = json_encode($locaux);
                    } else {
                        $data['locaux'] = '[]';
                    }
                } catch (\Exception $e) {
                    $data['locaux'] = '[]';
                }
            } else {
                $data['locaux'] = '[]';
            }
            
            // Insert into the temporary table
            unset($data['id']); // Let the database auto-increment the ID
            DB::table('concours_temp')->insert($data);
        }
        
        // Drop the original table
        Schema::drop('concours');
        
        // Rename the temporary table
        Schema::rename('concours_temp', 'concours');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration for safety
        // To reverse, you would need to restore from backup
    }
};
