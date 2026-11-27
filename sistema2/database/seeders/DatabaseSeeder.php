<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los datos de demostración solo se pueden cargar en local o testing.');
        }

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        if (
            Category::query()->exists()
            || Customer::query()->exists()
            || Medicine::query()->exists()
            || Sale::query()->exists()
        ) {
            return;
        }

        $this->call([
            CategorySeeder::class,
            CustomerSeeder::class,
            MedicineSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
