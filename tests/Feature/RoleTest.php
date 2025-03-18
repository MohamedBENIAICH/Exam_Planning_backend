<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Utilisateur;

class RoleTest extends TestCase
{
    /** @test */
    public function un_role_peut_avoir_plusieurs_utilisateurs()
    {
        // Assurez-vous que le rôle existe déjà dans la base de données
        $role = Role::firstOrCreate([
            'nom' => 'Administrateur',
            'description' => 'Peut gérer tout',
        ]);

        // Créer des utilisateurs associés à ce rôle
        $utilisateur1 = Utilisateur::create([
            'nom_utilisateur' => 'User 1',
            'email' => 'user1@example.com',
            'mot_de_passe' => bcrypt('password1'),
            'role_id' => $role->id,
        ]);
        
        $utilisateur2 = Utilisateur::create([
            'nom_utilisateur' => 'User 2',
            'email' => 'user2@example.com',
            'mot_de_passe' => bcrypt('password2'),
            'role_id' => $role->id,
        ]);

        // Vérifier que le rôle a les utilisateurs
        $this->assertCount(2, $role->utilisateurs);
        $this->assertTrue($role->utilisateurs->contains($utilisateur1));
        $this->assertTrue($role->utilisateurs->contains($utilisateur2));
    }
}
