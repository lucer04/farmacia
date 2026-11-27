<?php

namespace Tests\Feature\Concerns;

use App\Models\Role;
use App\Models\User;

trait CreatesUsers
{
    protected function createUserForRole(string $roleName, array $attributes = []): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['description' => 'Rol utilizado en pruebas.'],
        );

        $sequence = User::query()->count() + 1;

        return User::create(array_replace([
            'role_id' => $role->id,
            'name' => 'Usuario de prueba',
            'dni' => (string) (80000000 + $sequence),
            'email' => "usuario{$sequence}@example.test",
            'password' => 'password123',
        ], $attributes));
    }

    protected function actingAsRole(string $roleName, array $attributes = []): User
    {
        $user = $this->createUserForRole($roleName, $attributes);
        $this->actingAs($user);

        return $user;
    }
}
