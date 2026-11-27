<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_edit_sale_and_transactionally_adjust_stock_and_totals(): void
    {
        $fixture = $this->createSaleFixture(currentStock: 8, soldAmount: 2);
        $newCustomer = Customer::create([
            'name' => 'Cliente nuevo',
            'dni' => '22222222',
        ]);
        $newMedicine = Medicine::create([
            'category_id' => $fixture['category']->id,
            'name' => 'Medicamento nuevo',
            'price' => 5,
            'stock' => 20,
        ]);

        $this->actingAs($fixture['administrator'])
            ->get(route('sales.edit', $fixture['sale']))
            ->assertOk()
            ->assertSee('data-price="1.99"', false);

        $this->put(route('sales.update', $fixture['sale']), [
            'customer_id' => $newCustomer->id,
            'items' => [
                ['medicine_id' => $fixture['medicine']->id, 'amount' => 3],
                ['medicine_id' => $newMedicine->id, 'amount' => 1],
            ],
        ])->assertRedirect(route('sales.show', $fixture['sale']));

        $this->assertDatabaseHas('sales', [
            'id' => $fixture['sale']->id,
            'customer_id' => $newCustomer->id,
            'user_id' => $fixture['seller']->id,
            'total' => 10.97,
            'igv' => 1.67,
        ]);
        $this->assertDatabaseHas('medicines', ['id' => $fixture['medicine']->id, 'stock' => 7]);
        $this->assertDatabaseHas('medicines', ['id' => $newMedicine->id, 'stock' => 19]);
        $this->assertDatabaseHas('detail_sales', [
            'sale_id' => $fixture['sale']->id,
            'medicine_id' => $fixture['medicine']->id,
            'amount' => 3,
            'price_unit' => 1.99,
            'subtotal' => 5.97,
        ]);
    }

    public function test_only_administrators_can_edit_sales(): void
    {
        $fixture = $this->createSaleFixture(currentStock: 8, soldAmount: 2);

        foreach ([$fixture['seller'], $fixture['pharmacist']] as $user) {
            $this->actingAs($user)
                ->get(route('sales.edit', $fixture['sale']))
                ->assertForbidden();
            $this->put(route('sales.update', $fixture['sale']), [
                'customer_id' => $fixture['customer']->id,
                'items' => [
                    ['medicine_id' => $fixture['medicine']->id, 'amount' => 2],
                ],
            ])->assertForbidden();
        }
    }

    public function test_insufficient_stock_rolls_back_all_sale_and_inventory_changes(): void
    {
        $fixture = $this->createSaleFixture(currentStock: 0, soldAmount: 2);

        $this->actingAs($fixture['administrator'])
            ->from(route('sales.edit', $fixture['sale']))
            ->put(route('sales.update', $fixture['sale']), [
                'customer_id' => $fixture['customer']->id,
                'items' => [
                    ['medicine_id' => $fixture['medicine']->id, 'amount' => 3],
                ],
            ])
            ->assertRedirect(route('sales.edit', $fixture['sale']))
            ->assertSessionHasErrors('items.0.amount');

        $this->assertDatabaseHas('sales', ['id' => $fixture['sale']->id, 'total' => 3.98]);
        $this->assertDatabaseHas('medicines', ['id' => $fixture['medicine']->id, 'stock' => 0]);
        $this->assertDatabaseHas('detail_sales', [
            'sale_id' => $fixture['sale']->id,
            'medicine_id' => $fixture['medicine']->id,
            'amount' => 2,
            'subtotal' => 3.98,
        ]);
    }

    private function createSaleFixture(int $currentStock, int $soldAmount): array
    {
        $administrator = $this->createUser('Administrador', 'admin@example.test');
        $seller = $this->createUser('Cajero', 'cashier@example.test');
        $pharmacist = $this->createUser('Químico farmacéutico', 'pharmacist@example.test');
        $customer = Customer::create([
            'name' => 'Cliente inicial',
            'dni' => '11111111',
        ]);
        $category = Category::create([
            'name' => 'Categoría de prueba',
        ]);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Medicamento histórico',
            'price' => 4.50,
            'stock' => $currentStock,
        ]);
        $subtotal = 1.99 * $soldAmount;
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'date' => now(),
            'total' => $subtotal,
            'igv' => round($subtotal * 18 / 118, 2),
        ]);
        $sale->details()->create([
            'medicine_id' => $medicine->id,
            'amount' => $soldAmount,
            'price_unit' => 1.99,
            'subtotal' => $subtotal,
        ]);

        return compact('administrator', 'seller', 'pharmacist', 'customer', 'category', 'medicine', 'sale');
    }

    private function createUser(string $roleName, string $email): User
    {
        $role = Role::create([
            'name' => $roleName,
            'description' => "Rol de prueba: {$roleName}",
        ]);

        return User::create([
            'role_id' => $role->id,
            'name' => "Usuario {$roleName}",
            'dni' => (string) random_int(10000000, 99999999),
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
