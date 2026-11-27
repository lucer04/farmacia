<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            ['category' => 'Analgésicos', 'name' => 'Paracetamol 500 mg', 'price' => 2.50],
            ['category' => 'Analgésicos', 'name' => 'Paracetamol 1 g', 'price' => 4.00],
            ['category' => 'Analgésicos', 'name' => 'Metamizol 500 mg', 'price' => 3.50],
            ['category' => 'Antibióticos', 'name' => 'Amoxicilina 500 mg', 'price' => 1.80],
            ['category' => 'Antibióticos', 'name' => 'Azitromicina 500 mg', 'price' => 5.50],
            ['category' => 'Antibióticos', 'name' => 'Cefalexina 500 mg', 'price' => 2.20],
            ['category' => 'Antiinflamatorios', 'name' => 'Ibuprofeno 400 mg', 'price' => 2.00],
            ['category' => 'Antiinflamatorios', 'name' => 'Naproxeno 550 mg', 'price' => 3.50],
            ['category' => 'Antiinflamatorios', 'name' => 'Diclofenaco 50 mg', 'price' => 2.80],
            ['category' => 'Antialérgicos', 'name' => 'Loratadina 10 mg', 'price' => 1.50],
            ['category' => 'Antialérgicos', 'name' => 'Cetirizina 10 mg', 'price' => 1.70],
            ['category' => 'Vitaminas', 'name' => 'Vitamina C 500 mg', 'price' => 1.20],
        ];

        foreach ($medicines as $attributes) {
            $category = Category::query()->where('name', $attributes['category'])->firstOrFail();
            unset($attributes['category']);
            $attributes['name'] = 'Demo S14 - '.$attributes['name'];

            Medicine::firstOrCreate(
                ['category_id' => $category->id, 'name' => $attributes['name']],
                ['price' => $attributes['price'], 'stock' => 100],
            );
        }
    }
}
