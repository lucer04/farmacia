<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Analgésicos', 'description' => 'Alivio del dolor y la fiebre.'],
            ['name' => 'Antibióticos', 'description' => 'Medicamentos de uso antimicrobiano.'],
            ['name' => 'Antiinflamatorios', 'description' => 'Tratamiento de la inflamación.'],
            ['name' => 'Antialérgicos', 'description' => 'Tratamiento de síntomas alérgicos.'],
            ['name' => 'Vitaminas', 'description' => 'Suplementos vitamínicos.'],
        ] as $attributes) {
            Category::firstOrCreate(
                ['name' => $attributes['name']],
                ['description' => $attributes['description']],
            );
        }
    }
}
