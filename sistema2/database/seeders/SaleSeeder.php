<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        if (Sale::query()->exists()) {
            return;
        }

        $demoUsers = [
            ['email' => 'admin@sanjuan.com', 'name' => 'Administrador San Juan', 'role' => 'Administrador'],
            ['email' => 'rquispe@sanjuan.com', 'name' => 'Rosa Quispe Huamán', 'role' => 'Químico farmacéutico'],
            ['email' => 'cmendoza@sanjuan.com', 'name' => 'Carmen Mendoza', 'role' => 'Cajero'],
            ['email' => 'ltorres@sanjuan.com', 'name' => 'Lucía Torres Salazar', 'role' => 'Cajero'],
        ];
        $users = [];

        foreach ($demoUsers as $attributes) {
            $user = User::query()->with('role')->where('email', $attributes['email'])->first();
            if (! $user || $user->name !== $attributes['name'] || $user->role?->name !== $attributes['role']) {
                return;
            }

            $users[] = $user;
        }

        $customers = Customer::query()->where('name', 'like', 'Cliente Demo S14 %')->get();
        $medicines = Medicine::query()
            ->where('name', 'like', 'Demo S14 - %')
            ->where('stock', '>', 0)
            ->exists();

        if ($customers->isEmpty() || ! $medicines) {
            return;
        }

        DB::transaction(function () use ($customers, $users): void {
            for ($number = 0; $number < 60; $number++) {
                $medicine = Medicine::query()
                    ->where('name', 'like', 'Demo S14 - %')
                    ->where('stock', '>', 0)
                    ->inRandomOrder()
                    ->lockForUpdate()
                    ->first();

                if (! $medicine) {
                    throw new RuntimeException('No hay stock suficiente para completar las ventas de demostración.');
                }

                $amount = random_int(1, min(3, $medicine->stock));
                $subtotal = round((float) $medicine->price * $amount, 2);
                $date = now()
                    ->subDays(random_int(0, 89))
                    ->setTime(random_int(8, 20), random_int(0, 59));

                $sale = Sale::create([
                    'customer_id' => $customers->random()->id,
                    'user_id' => $users[array_rand($users)]->id,
                    'date' => $date,
                    'total' => $subtotal,
                    'igv' => round($subtotal * 18 / 118, 2),
                ]);

                $sale->details()->create([
                    'medicine_id' => $medicine->id,
                    'amount' => $amount,
                    'price_unit' => $medicine->price,
                    'subtotal' => $subtotal,
                ]);

                $medicine->decrement('stock', $amount);
            }
        });
    }
}
