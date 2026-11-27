<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Las cuentas de demostración solo se pueden crear en local o testing.');
        }

        $users = [
            [
                'role' => 'Administrador',
                'name' => 'Administrador San Juan',
                'dni' => '90000001',
                'email' => 'admin@sanjuan.com',
                'password' => 'password',
            ],
            [
                'role' => 'Químico farmacéutico',
                'name' => 'Rosa Quispe Huamán',
                'dni' => '90000002',
                'phone' => '900000002',
                'tuition' => 'QF-0001',
                'email' => 'rquispe@sanjuan.com',
                'password' => 'password',
            ],
            [
                'role' => 'Cajero',
                'name' => 'Carmen Mendoza',
                'dni' => '90000003',
                'email' => 'cmendoza@sanjuan.com',
                'password' => 'password',
            ],
            [
                'role' => 'Cajero',
                'name' => 'Lucía Torres Salazar',
                'dni' => '90000004',
                'email' => 'ltorres@sanjuan.com',
                'password' => 'password',
            ],
        ];

        foreach ($users as $attributes) {
            $role = Role::query()->where('name', $attributes['role'])->firstOrFail();
            unset($attributes['role']);

            if (User::query()->where('email', $attributes['email'])->exists()) {
                continue;
            }

            if (User::query()->where('dni', $attributes['dni'])->exists()) {
                $attributes['dni'] = $this->nextAvailableDni();
            }

            User::firstOrCreate(
                ['email' => $attributes['email']],
                ['role_id' => $role->id] + $attributes,
            );
        }
    }

    private function nextAvailableDni(): string
    {
        for ($number = 90000005; $number <= 99999999; $number++) {
            $dni = (string) $number;
            if (! User::query()->where('dni', $dni)->exists()) {
                return $dni;
            }
        }

        throw new RuntimeException('No hay un DNI de demostración disponible para crear usuarios.');
    }
}
