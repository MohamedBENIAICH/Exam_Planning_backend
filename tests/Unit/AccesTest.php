<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Acces;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Composant;
use App\Models\TypePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccesTest extends TestCase
{
   

//     /** @test */
//     public function un_acces_peut_avoir_plusieurs_utilisateurs()
//     {
//         // Créer un accès
        
        
//        // Créer un composant avant de créer l'accès
//        $composant = Composant::create([
//         'nom' => 'Composant A',
//         'description' => 'Description du composant A',
//         'url' => 'http://example.com/composant-a',
//     ]);
//     $role = Role::firstOrCreate([
//         'nom' => 'Administrateur',
//         'description' => 'Peut gérer tout',
//     ]);

//     $typePermission = TypePermission::create([
//         'type' => 'Admin',
//     ]);
// // Créer l'accès en utilisant l'ID du composant créé
// $acces = Acces::create([
//     'role_id' => $role->id,
//     'composant_id' => $composant->id,  // Utiliser l'ID du composant créé
//     'type_permission_id' => $typePermission->id,
//     'nom' => 'Accès complet',
//     'description' => 'Accès complet aux fonctionnalités admin pour le composant X',
// ]);

//         // Créer des utilisateurs associés à cet accès
//         $user1 = Utilisateur::create([
//             'nom_utilisateur' => 'User 1',
//             'email' => 'user1@example.com',
//             'mot_de_passe' => bcrypt('password1'),
//             'role_id' => $role->id,
//         ]);

//         $user2 = Utilisateur::create([
//             'nom_utilisateur' => 'User 2',
//             'email' => 'user2@example.com',
//             'mot_de_passe' => bcrypt('password2'),
//            'role_id' => $role->id,
//         ]);

//         // Vérifier que l'accès a les utilisateurs
//         $this->assertCount(2, $acces->utilisateurs);
//         $this->assertTrue($acces->utilisateurs->contains($user1));
//         $this->assertTrue($acces->utilisateurs->contains($user2));
//     }
}
