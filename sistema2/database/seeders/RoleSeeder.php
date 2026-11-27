<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Administrador' => 'Acceso completo al sistema.',
            'Químico farmacéutico' => 'Acceso a medicamentos, ventas, clientes y reportes.',
            'Cajero' => 'Acceso a ventas y clientes.',
        ] as $name => $description) {
            Role::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
