<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_view_update_and_delete_a_supplier(): void
    {
        $administrator = $this->createUser('Administrador');
        $this->actingAs($administrator)
            ->get(route('suppliers.index'))
            ->assertOk();
        $this->get(route('suppliers.create'))->assertOk();

        $this->post(route('suppliers.store'), $this->supplierData())
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('ok', 'Proveedor registrado correctamente.');

        $supplier = Supplier::query()->firstOrFail();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'ruc' => '20123456789',
            'is_active' => true,
        ]);
        $this->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($supplier->name);
        $this->get(route('suppliers.edit', $supplier))->assertOk();

        $this->put(route('suppliers.update', $supplier), [
            ...$this->supplierData(),
            'name' => 'Proveedor actualizado',
            'is_active' => '0',
        ])->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('ok', 'Proveedor actualizado correctamente.');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Proveedor actualizado',
            'ruc' => '20123456789',
            'is_active' => false,
        ]);

        $this->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('ok', 'Proveedor eliminado correctamente.');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_validation_rejects_invalid_and_duplicate_rucs(): void
    {
        $this->actingAs($this->createUser('Administrador'));
        Supplier::create($this->supplierData());

        $this->from(route('suppliers.create'))
            ->post(route('suppliers.store'), [
                ...$this->supplierData(),
                'ruc' => '123',
                'phone' => '123',
                'email' => 'no-es-correo',
            ])
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors(['ruc', 'phone', 'email']);

        $this->from(route('suppliers.create'))
            ->post(route('suppliers.store'), $this->supplierData())
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('ruc');
    }

    public function test_supplier_search_status_filter_alphabetical_order_and_pagination_work(): void
    {
        $this->actingAs($this->createUser('Administrador'));
        Supplier::create([...$this->supplierData(), 'name' => 'Zeta Pharma']);
        Supplier::create([
            ...$this->supplierData(),
            'name' => 'Alfa Pharma',
            'ruc' => '20456789123',
        ]);
        Supplier::create([
            ...$this->supplierData(),
            'name' => 'Inactivo Pharma',
            'ruc' => '20678912345',
            'is_active' => false,
        ]);

        $response = $this->get(route('suppliers.index', ['search' => 'Pharma', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Zeta Pharma')
            ->assertSee('Alfa Pharma')
            ->assertDontSee('Inactivo Pharma');

        $this->assertLessThan(
            strpos($response->getContent(), 'Zeta Pharma'),
            strpos($response->getContent(), 'Alfa Pharma'),
        );

        foreach (range(1, 16) as $index) {
            Supplier::create([
                ...$this->supplierData(),
                'name' => sprintf('Proveedor %02d', $index),
                'ruc' => sprintf('%011d', 21000000000 + $index),
            ]);
        }

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    public function test_only_administrators_and_pharmacists_can_access_suppliers(): void
    {
        $supplier = Supplier::create($this->supplierData());

        $this->actingAs($this->createUser('Químico farmacéutico'))
            ->get(route('suppliers.show', $supplier))
            ->assertOk();

        $this->actingAs($this->createUser('Cajero'))
            ->get(route('suppliers.index'))
            ->assertForbidden();
    }

    public function test_supplier_seeder_is_repeatable_and_does_not_overwrite_existing_records(): void
    {
        $seeder = app(SupplierSeeder::class);
        $seeder->run();
        $supplier = Supplier::query()->where('ruc', '20123456789')->firstOrFail();
        $supplier->update(['name' => 'Nombre existente', 'is_active' => false]);

        $seeder->run();

        $this->assertDatabaseCount('suppliers', 4);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Nombre existente',
            'is_active' => false,
        ]);
    }

    private function createUser(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
        ], [
            'description' => 'Rol para pruebas',
        ]);

        return User::create([
            'role_id' => $role->id,
            'name' => "Usuario {$roleName}",
            'dni' => (string) random_int(10000000, 99999999),
            'email' => strtolower(str_replace(' ', '-', $roleName)).'-'.uniqid().'@example.test',
            'password' => 'password123',
        ]);
    }

    private function supplierData(): array
    {
        return [
            'name' => 'Proveedor de prueba',
            'ruc' => '20123456789',
            'phone' => '987654321',
            'email' => 'ventas@example.test',
            'address' => 'Dirección de prueba',
            'is_active' => '1',
        ];
    }
}
