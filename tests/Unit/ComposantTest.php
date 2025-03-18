<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Composant;
use App\Models\Acces;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComposantTest extends TestCase
{
   

    /** @test */
    public function un_composant_peut_avoir_plusieurs_acces()
    {
        // Créer un composant
        $composant = Composant::create([
            'nom' => 'Composant A',
            'description' => 'Description du composant A',
            'url' => 'http://example.com/composant-a',
        ]);

       
    }
}
