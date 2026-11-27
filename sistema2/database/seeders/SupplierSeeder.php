<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use RuntimeException;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los proveedores de demostración solo se pueden cargar en local o testing.');
        }

        foreach ([
            [
                'name' => 'Laboratorios Farma Perú S.A.C.',
                'ruc' => '20123456789',
                'phone' => '987654321',
                'email' => 'ventas@farma-peru.com',
                'address' => 'Av. Los Andes 1250, Lima',
            ],
            [
                'name' => 'Distribuidora Médica San José E.I.R.L.',
                'ruc' => '20456789123',
                'phone' => '912345678',
                'email' => 'contacto@sanjosemed.com',
                'address' => 'Jr. Salud 340, Arequipa',
            ],
            [
                'name' => 'Botica Central de Insumos S.R.L.',
                'ruc' => '20678912345',
                'phone' => '945612378',
                'email' => 'compras@boticacentral.pe',
                'address' => 'Calle Comercio 890, Trujillo',
            ],
            [
                'name' => 'Corporación Pharma Norte S.A.',
                'ruc' => '20912345678',
                'phone' => '956123478',
                'email' => 'info@pharmanorte.pe',
                'address' => 'Av. Industrial 220, Chiclayo',
            ],
        ] as $supplier) {
            Supplier::firstOrCreate(
                ['ruc' => $supplier['ruc']],
                [...$supplier, 'is_active' => true],
            );
        }
    }
}
