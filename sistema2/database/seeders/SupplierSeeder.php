<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    // Registra proveedores ficticios para demostrar el CRUD
    public function run(): void
    {
        $suppliers = [
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
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
