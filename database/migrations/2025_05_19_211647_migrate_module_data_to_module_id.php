<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Récupérer tous les examens
        $exams = DB::table('exams')->get();

        foreach ($exams as $exam) {
            // Trouver l'ID du module correspondant
            $module = DB::table('modules')
                ->where('module_intitule', $exam->module)
                ->first();

            if ($module) {
                // Mettre à jour l'exam avec le bon module_id
                DB::table('exams')
                    ->where('id', $exam->id)
                    ->update(['module_id' => $module->id_module]);
            }
        }
    }

    public function down()
    {
        // Optionnel : restaurer les anciennes valeurs si nécessaire
        DB::table('exams')->update(['module_id' => null]);
    }
};
