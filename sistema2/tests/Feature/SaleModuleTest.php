<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SaleModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_module_lists_and_forms_render(): void
    {
        $this->createSaleDependencies();

        foreach ([
            'roles.index',
            'roles.create',
            'categories.index',
            'categories.create',
            'customers.index',
            'customers.create',
            'users.index',
            'users.create',
            'medicines.index',
            'medicines.create',
            'sales.index',
            'sales.create',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_sale_stores_details_and_decrements_medicine_stock(): void
    {
        [$customer, $user, $medicine] = $this->createSaleDependencies();

        $response = $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'date' => '2000-01-01T00:00',
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 2, 'price' => 0.01],
            ],
        ]);

        $response->assertRedirect(route('sales.show', 1));
        $this->assertDatabaseHas('sales', [
            'id' => 1,
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total' => 20,
            'igv' => 3.05,
        ]);
        $this->assertDatabaseHas('detail_sales', [
            'sale_id' => 1,
            'medicine_id' => $medicine->id,
            'amount' => 2,
            'price_unit' => 10,
            'subtotal' => 20,
        ]);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock' => 8,
        ]);

        $sale = Sale::findOrFail(1);
        $this->assertTrue($sale->date->greaterThan(now()->subMinute()));
        $this->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee('Operación gravada')
            ->assertSee('S/ 16.95')
            ->assertSee('S/ 3.05');
    }

    public function test_sale_uses_the_authenticated_user_even_if_another_user_id_is_submitted(): void
    {
        [$customer, $user, $medicine] = $this->createSaleDependencies();
        $otherUser = User::create([
            'role_id' => $user->role_id,
            'name' => 'Otro usuario',
            'dni' => '76543210',
            'email' => 'otro@example.test',
            'password' => 'password123',
        ]);

        $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'user_id' => $otherUser->id,
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 1],
            ],
        ])->assertRedirect(route('sales.show', 1));

        $this->assertDatabaseHas('sales', [
            'id' => 1,
            'user_id' => $user->id,
        ]);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_writing_data(): void
    {
        [$customer, $user, $medicine] = $this->createSaleDependencies();

        $response = $this->from(route('sales.create'))->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'date' => '2026-11-26T14:00',
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 11],
            ],
        ]);

        $response->assertRedirect(route('sales.create'));
        $response->assertSessionHasErrors('items.0.amount');
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('detail_sales', 0);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock' => 10,
        ]);
    }

    public function test_sale_form_lists_only_medicines_with_available_stock(): void
    {
        [, , $medicine] = $this->createSaleDependencies();
        $outOfStockMedicine = Medicine::create([
            'category_id' => $medicine->category_id,
            'name' => 'Sin stock',
            'price' => 10,
            'stock' => 0,
        ]);

        $this->get(route('sales.create'))
            ->assertOk()
            ->assertSee($medicine->name)
            ->assertDontSee($outOfStockMedicine->name);
    }

    public function test_sale_form_displays_the_authenticated_seller_without_a_user_selector(): void
    {
        $this->createSaleDependencies();

        $this->get(route('sales.create'))
            ->assertOk()
            ->assertSee('value="Usuario de prueba" disabled', false)
            ->assertDontSee('name="user_id"', false);
    }

    public function test_insufficient_stock_in_any_item_leaves_all_rows_and_stock_unchanged(): void
    {
        [$customer, $user, $medicine] = $this->createSaleDependencies();
        $lowStockMedicine = Medicine::create([
            'category_id' => $medicine->category_id,
            'name' => 'Stock insuficiente',
            'price' => 5,
            'stock' => 1,
        ]);

        $this->from(route('sales.create'))->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 2],
                ['medicine_id' => $lowStockMedicine->id, 'amount' => 2],
            ],
        ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('items.1.amount');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('detail_sales', 0);
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stock' => 10]);
        $this->assertDatabaseHas('medicines', ['id' => $lowStockMedicine->id, 'stock' => 1]);
    }

    public function test_sale_rejects_repeated_medicines_without_changing_stock(): void
    {
        [$customer, $user, $medicine] = $this->createSaleDependencies();

        $this->from(route('sales.create'))->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 1],
                ['medicine_id' => $medicine->id, 'amount' => 1],
            ],
        ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('items.1.medicine_id');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stock' => 10]);
    }

    public function test_cancelling_a_sale_restores_stock_deletes_details_and_cannot_repeat(): void
    {
        [$customer, $user, $medicine] = $this->createSaleDependencies();
        $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'items' => [
                ['medicine_id' => $medicine->id, 'amount' => 2],
            ],
        ])->assertRedirect(route('sales.show', 1));

        $sale = Sale::findOrFail(1);
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stock' => 8]);

        $this->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'))
            ->assertSessionHas('ok', 'Venta anulada y stock devuelto correctamente.');

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertDatabaseCount('detail_sales', 0);
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stock' => 10]);

        $this->delete(route('sales.destroy', $sale->id))->assertNotFound();
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stock' => 10]);
    }

    private function createSaleDependencies(): array
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Administrador',
            'description' => 'Personal de farmacia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'dni' => '12345678',
        ]);

        $user = User::create([
            'role_id' => $roleId,
            'name' => 'Usuario de prueba',
            'dni' => '87654321',
            'email' => 'usuario@example.test',
            'password' => 'password123',
        ]);
        $this->actingAs($user);

        $category = Category::create([
            'name' => 'General',
        ]);

        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Medicamento de prueba',
            'price' => 10,
            'stock' => 10,
        ]);

        return [$customer, $user, $medicine];
    }
}
