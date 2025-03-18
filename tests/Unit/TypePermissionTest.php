<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TypePermission;
use App\Models\Acces;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TypePermissionTest extends TestCase
{
   

    /** @test */
    public function un_type_permission_peut_avoir_plusieurs_acces()
    {
        // Créer un type de permission
        $typePermission = TypePermission::create([
            'type' => 'Admin',
        ]);

        // Créer des accès associés à ce type de permission
    }
}
