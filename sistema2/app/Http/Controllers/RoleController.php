<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();

        return view('settings.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('settings.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Role::create($validated);

        return to_route('roles.index')->with('ok', 'Rol registrado correctamente.');
    }

    public function edit(Role $role)
    {
        return view('settings.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate($this->rules($role), $this->messages());

        $role->update($validated);

        return to_route('roles.index')->with('ok', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return to_route('roles.index')
                ->withErrors(['role' => 'No se puede eliminar un rol asignado a uno o más usuarios.']);
        }

        $role->delete();

        return to_route('roles.index')->with('ok', 'Rol eliminado correctamente.');
    }

    private function rules(?Role $role = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string' => 'El nombre del rol debe ser texto.',
            'name.max' => 'El nombre del rol no puede superar los 50 caracteres.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'description.string' => 'La descripción debe ser texto.',
            'description.max' => 'La descripción no puede superar los 255 caracteres.',
        ];
    }
}
