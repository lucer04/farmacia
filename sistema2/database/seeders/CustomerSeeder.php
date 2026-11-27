<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Cliente Demo S14 01', 'dni' => '99000101', 'phone' => '900000101', 'age' => 28],
            ['name' => 'Cliente Demo S14 02', 'dni' => '99000102', 'phone' => '900000102', 'age' => 35],
            ['name' => 'Cliente Demo S14 03', 'dni' => '99000103', 'phone' => '900000103', 'age' => 42],
            ['name' => 'Cliente Demo S14 04', 'dni' => '99000104', 'phone' => '900000104', 'age' => 51],
        ];

        foreach ($customers as $attributes) {
            if (Customer::query()->where('name', $attributes['name'])->exists()) {
                continue;
            }

            $dni = (int) $attributes['dni'];
            while (Customer::query()->where('dni', (string) $dni)->exists()) {
                $dni++;
                if ($dni > 99999999) {
                    throw new \RuntimeException('No hay un DNI de demostración disponible para crear clientes.');
                }
            }

            $attributes['dni'] = (string) $dni;
            Customer::create($attributes);
        }
    }
}
