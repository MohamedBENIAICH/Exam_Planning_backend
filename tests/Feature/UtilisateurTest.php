<?php
namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Models\Role;
use Tests\TestCase;

class UtilisateurTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    
    // Retirer le trait RefreshDatabase car nous ne voulons pas réinitialiser la base
    public function test_utilisateur_creation()
    {
        // Créer un rôle de test (sans recréer les rôles existants)
        $role = Role::firstOrCreate(['nom' => 'admin'], ['description' => 'Rôle d\'administrateur']);

        // Créer un utilisateur
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => 'john_doe',
            'email' => 'john@example.com',
            'mot_de_passe' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        // Vérifier que l'utilisateur existe dans la base de données
        $this->assertDatabaseHas('utilisateurs', [
            'nom_utilisateur' => 'john_doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_utilisateur_retrieval()
    {
        // Créer un utilisateur avec un rôle
        $role = Role::firstOrCreate(['nom' => 'utilisateur'], ['description' => 'Rôle d\'utilisateur']);
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => 'jane_doe',
            'email' => 'jane@example.com',
            'mot_de_passe' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        // Récupérer l'utilisateur par son nom d'utilisateur
        $retrievedUtilisateur = Utilisateur::where('nom_utilisateur', 'jane_doe')->first();

        // Vérifier que les données récupérées sont correctes
        $this->assertEquals('jane_doe', $retrievedUtilisateur->nom_utilisateur);
        $this->assertEquals('jane@example.com', $retrievedUtilisateur->email);
    }

    public function test_utilisateur_update()
    {
        // Créer un utilisateur
        $role = Role::create([
            'nom' => 'admin',
            'description' => 'Rôle d\'administrateur', // Fournir une description
        ]);
    
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => 'jack_doe',
            'email' => 'ja0ck@example.com',
            'mot_de_passe' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        // Mettre à jour l'utilisateur
        $utilisateur->nom_utilisateur = 'updated_jack_doe';
        $utilisateur->save();

        // Vérifier que l'utilisateur a bien été mis à jour
        $this->assertDatabaseHas('utilisateurs', [
            'nom_utilisateur' => 'updated_jack_doe',
        ]);
    }

    public function test_utilisateur_deletion()
    {
        // Créer un utilisateur
        $role = Role::firstOrCreate(['nom' => 'utilisateur'], ['description' => 'Rôle d\'utilisateur']);
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => 'david_doe',
            'email' => 'david@example.com',
            'mot_de_passe' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        // Supprimer l'utilisateur
        $utilisateur->delete();

        // Vérifier que l'utilisateur a bien été supprimé
        $this->assertDatabaseMissing('utilisateurs', [
            'nom_utilisateur' => 'david_doe',
        ]);
    }

    public function test_role_creation()
    {
        // Créer un rôle
        $role = Role::firstOrCreate(['nom' => 'admin'], ['description' => 'Rôle d\'administrateur']);

        // Vérifier que le rôle existe dans la base de données
        $this->assertDatabaseHas('roles', [
            'nom' => 'admin',
        ]);
    }
}
