<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function createMissingTables()
    {
        try {
            // Créer la table candidats si elle n'existe pas
            if (!Schema::hasTable('candidats')) {
                Schema::create('candidats', function ($table) {
                    $table->id();
                    $table->string('CNE')->unique();
                    $table->string('CIN')->unique();
                    $table->string('nom');
                    $table->string('prenom');
                    $table->string('email')->unique();
                    $table->timestamps();
                });
            }

            // Créer la table concours si elle n'existe pas
            if (!Schema::hasTable('concours')) {
                Schema::create('concours', function ($table) {
                    $table->id();
                    $table->string('titre');
                    $table->text('description')->nullable();
                    $table->date('date_concours');
                    $table->time('heure_debut');
                    $table->time('heure_fin');
                    $table->string('locaux')->nullable();
                    $table->string('type_epreuve');
                    $table->timestamps();
                });
            }

            // Créer la table concours_candidat si elle n'existe pas
            if (!Schema::hasTable('concours_candidat')) {
                Schema::create('concours_candidat', function ($table) {
                    $table->id();
                    $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
                    $table->foreignId('candidat_id')->constrained('candidats')->onDelete('cascade');
                    $table->timestamps();
                });
            }

            // Créer la table concours_superviseur si elle n'existe pas
            if (!Schema::hasTable('concours_superviseur')) {
                Schema::create('concours_superviseur', function ($table) {
                    $table->id();
                    $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
                    $table->foreignId('superviseur_id')->constrained('superviseurs')->onDelete('cascade');
                    $table->timestamps();
                });
            }

            // Créer la table concours_professeur si elle n'existe pas
            if (!Schema::hasTable('concours_professeur')) {
                Schema::create('concours_professeur', function ($table) {
                    $table->id();
                    $table->foreignId('concours_id')->constrained('concours')->onDelete('cascade');
                    $table->foreignId('professeur_id')->constrained('professeurs')->onDelete('cascade');
                    $table->timestamps();
                });
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tables créées avec succès',
                'tables_created' => [
                    'candidats' => Schema::hasTable('candidats'),
                    'concours' => Schema::hasTable('concours'),
                    'concours_candidat' => Schema::hasTable('concours_candidat'),
                    'concours_superviseur' => Schema::hasTable('concours_superviseur'),
                    'concours_professeur' => Schema::hasTable('concours_professeur'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création des tables: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkTables()
    {
        $tables = [
            'candidats',
            'concours',
            'concours_candidat',
            'concours_superviseur',
            'concours_professeur'
        ];

        $status = [];
        foreach ($tables as $table) {
            $status[$table] = Schema::hasTable($table);
        }

        return response()->json([
            'status' => 'success',
            'tables_status' => $status
        ]);
    }
}
