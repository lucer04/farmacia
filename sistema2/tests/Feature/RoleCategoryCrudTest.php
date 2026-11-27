<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\CreatesUsers;
use Tests\TestCase;

class RoleCategoryCrudTest extends TestCase
{
    use CreatesUsers;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('medicines');
        Schema::dropIfExists('users');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('roles');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('name');
            $table->char('dni', 8)->unique();
            $table->string('phone', 15)->nullable();
            $table->string('tuition', 20)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();
        });

        $this->actingAsRole('Administrador');
    }

    public function test_role_crud_and_validation(): void
    {
        $this->get(route('roles.index'))->assertOk();
        $this->get(route('roles.create'))->assertOk();

        $this->post(route('roles.store'), [
            'name' => 'Farmacéutico',
            'description' => 'Personal autorizado',
        ])->assertRedirect(route('roles.index'))
            ->assertSessionHas('ok', 'Rol registrado correctamente.');

        $roleId = DB::table('roles')->where('name', 'Farmacéutico')->value('id');
        $this->assertDatabaseHas('roles', [
            'id' => $roleId,
            'name' => 'Farmacéutico',
            'description' => 'Personal autorizado',
        ]);
        $this->get(route('roles.edit', $roleId))->assertOk();

        $this->put(route('roles.update', $roleId), [
            'name' => 'Farmacéutico',
            'description' => 'Descripción actualizada',
        ])->assertRedirect(route('roles.index'))
            ->assertSessionHas('ok', 'Rol actualizado correctamente.');

        $this->from(route('roles.create'))->post(route('roles.store'), [
            'name' => 'Farmacéutico',
            'description' => null,
        ])->assertRedirect(route('roles.create'))
            ->assertSessionHasErrors(['name']);

        $this->delete(route('roles.destroy', $roleId))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('ok', 'Rol eliminado correctamente.');

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    public function test_role_cannot_be_deleted_while_assigned_to_a_user(): void
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Rol asignado',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'role_id' => $roleId,
            'name' => 'Usuario de prueba',
            'dni' => '12345678',
            'email' => 'usuario@example.test',
            'password' => 'hashed-password',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('roles.destroy', $roleId))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHasErrors([
                'role' => 'No se puede eliminar un rol asignado a uno o más usuarios.',
            ]);

        $this->assertDatabaseHas('roles', ['id' => $roleId]);
    }

    public function test_category_crud_and_validation(): void
    {
        $this->get(route('categories.index'))->assertOk();
        $this->get(route('categories.create'))->assertOk();

        $this->post(route('categories.store'), [
            'name' => 'Analgésicos',
            'description' => 'Alivio del dolor',
        ])->assertRedirect(route('categories.index'))
            ->assertSessionHas('ok', 'Categoría registrada correctamente.');

        $categoryId = DB::table('categories')->value('id');
        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'name' => 'Analgésicos',
            'description' => 'Alivio del dolor',
        ]);
        $this->get(route('categories.edit', $categoryId))->assertOk();

        $this->put(route('categories.update', $categoryId), [
            'name' => 'Analgésicos',
            'description' => 'Descripción actualizada',
        ])->assertRedirect(route('categories.index'))
            ->assertSessionHas('ok', 'Categoría actualizada correctamente.');

        $this->from(route('categories.create'))->post(route('categories.store'), [
            'name' => 'Analgésicos',
            'description' => null,
        ])->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors(['name']);

        $this->delete(route('categories.destroy', $categoryId))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('ok', 'Categoría eliminada correctamente.');

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_category_cannot_be_deleted_while_it_has_medicines(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Antibióticos',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('medicines')->insert([
            'category_id' => $categoryId,
            'name' => 'Medicamento de prueba',
            'price' => 12.50,
            'stock' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('categories.destroy', $categoryId))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHasErrors([
                'category' => 'No se puede eliminar una categoría que tiene medicamentos asociados.',
            ]);

        $this->assertDatabaseHas('categories', ['id' => $categoryId]);
    }
}
