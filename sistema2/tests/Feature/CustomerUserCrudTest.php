<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

class CustomerUserCrudTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsRole('Administrador');
    }

    public function test_customer_crud_accepts_name_up_to_150_and_same_dni_on_update(): void
    {
        $name = str_repeat('C', 100);
        $data = [
            'name' => $name,
            'dni' => '01234567',
            'phone' => '012345678',
            'age' => 120,
        ];

        $this->get(route('customers.index'))->assertOk();
        $this->get(route('customers.create'))->assertOk();
        $this->post(route('customers.store'), $data)
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('ok', 'Cliente registrado correctamente.');

        $customer = Customer::where('dni', $data['dni'])->firstOrFail();
        $this->assertSame($name, $customer->name);

        $this->get(route('customers.edit', $customer))->assertOk();
        $this->put(route('customers.update', $customer), $data)
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('ok', 'Cliente actualizado correctamente.');

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('ok', 'Cliente eliminado correctamente.');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_phone_and_age_validation_is_in_spanish(): void
    {
        $this->assertSame('es', config('app.locale'));
        $this->assertSame('America/Lima', config('app.timezone'));

        $this->from(route('customers.create'))
            ->post(route('customers.store'), [
                'name' => 'Cliente inválido',
                'dni' => '12345678',
                'phone' => '12345678',
                'age' => 121,
            ])
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors([
                'phone' => 'El campo teléfono debe tener 9 dígitos.',
                'age' => 'El campo edad no debe ser mayor que 120.',
            ]);
    }

    public function test_user_crud_hashes_password_and_keeps_it_when_editing_without_a_new_one(): void
    {
        $role = Role::create(['name' => 'Farmacéutico']);
        $data = [
            'role_id' => $role->id,
            'name' => 'Usuario de prueba',
            'dni' => '87654321',
            'phone' => '987654321',
            'tuition' => 'QF-123',
            'email' => 'usuario@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertOk();
        $this->post(route('users.store'), $data)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('ok', 'Usuario registrado correctamente.');

        $user = User::where('email', $data['email'])->firstOrFail();
        $passwordHash = $user->password;
        $this->assertNotSame($data['password'], $passwordHash);
        $this->assertTrue(Hash::check($data['password'], $passwordHash));

        $this->get(route('users.index'))->assertSee($role->name);
        $this->get(route('users.edit', $user))->assertOk();
        unset($data['password'], $data['password_confirmation']);
        $data['name'] = 'Usuario actualizado';

        $this->put(route('users.update', $user), $data)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('ok', 'Usuario actualizado correctamente.');

        $this->assertSame($passwordHash, $user->fresh()->password);
        $this->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('ok', 'Usuario eliminado correctamente.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_requires_an_existing_role(): void
    {
        $this->from(route('users.create'))
            ->post(route('users.store'), [
                'role_id' => 999,
                'name' => 'Usuario sin rol válido',
                'dni' => '12345678',
                'email' => 'sin-rol@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'sin-rol@example.test']);
    }

    public function test_customer_and_user_with_sales_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Cajero']);
        $customer = Customer::create([
            'name' => 'Cliente con venta',
            'dni' => '12345678',
            'phone' => '123456789',
            'age' => 30,
        ]);
        $user = User::create([
            'role_id' => $role->id,
            'name' => 'Vendedor con venta',
            'dni' => '87654321',
            'email' => 'vendedor@example.test',
            'password' => 'password123',
        ]);

        Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'date' => now(),
            'total' => 10,
            'igv' => 1.53,
        ]);

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHasErrors('customer');
        $this->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
