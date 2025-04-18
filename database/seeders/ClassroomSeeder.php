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
        Classroom::create([
            'name' => 'A101',
            'building' => 'Bâtiment des Sciences',
            'capacity' => 30,
            'equipment' => ['projector', 'whiteboard'],
            'is_available' => true
        ]);

        Classroom::create([
            'name' => 'B203',
            'building' => 'Bâtiment des Lettres',
            'capacity' => 25,
            'equipment' => ['projector', 'computer'],
            'is_available' => true
        ]);
    }
}
