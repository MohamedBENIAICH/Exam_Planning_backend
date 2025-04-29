<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classroom;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classrooms = [
            [
                'nom_du_local' => 'Salle A1',
                'departement' => 'Informatique',
                'capacite' => 30,
                'liste_des_equipements' => ['projecteur', 'tableau blanc'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle A2',
                'departement' => 'Informatique',
                'capacite' => 25,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'ordinateurs'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle B1',
                'departement' => 'Mathématiques',
                'capacite' => 40,
                'liste_des_equipements' => ['projecteur', 'tableau blanc'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle B2',
                'departement' => 'Mathématiques',
                'capacite' => 35,
                'liste_des_equipements' => ['projecteur', 'tableau blanc'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle C1',
                'departement' => 'Physique',
                'capacite' => 30,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'équipement de laboratoire'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle C2',
                'departement' => 'Physique',
                'capacite' => 25,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'équipement de laboratoire'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle D1',
                'departement' => 'Chimie',
                'capacite' => 30,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'équipement de laboratoire'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle D2',
                'departement' => 'Chimie',
                'capacite' => 25,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'équipement de laboratoire'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle E1',
                'departement' => 'Biologie',
                'capacite' => 30,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'microscopes'],
                'disponible_pour_planification' => true
            ],
            [
                'nom_du_local' => 'Salle E2',
                'departement' => 'Biologie',
                'capacite' => 25,
                'liste_des_equipements' => ['projecteur', 'tableau blanc', 'microscopes'],
                'disponible_pour_planification' => true
            ]
        ];

        foreach ($classrooms as $classroom) {
            Classroom::create($classroom);
        }
    }
}
