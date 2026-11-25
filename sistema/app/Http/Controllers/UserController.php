<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Mostrar todos los usuarios
    public function index()
    {
        $users = User::with('role')
            ->orderBy('id', 'desc')
            ->get();

        return view('settings.users.index', compact('users'));
    }

    // Mostrar formulario para crear usuario
    public function create()
    {
        $roles = Role::orderBy('nombre')->get();

        return view('settings.users.create', compact('roles'));
    }

    // Guardar usuario nuevo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|max:255',
            'dni' => 'required|digits:8|unique:users,dni',
            'telefono' => 'nullable|string|max:15',
            'colegiatura' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create($validated);

        return to_route('usuarios.index')
            ->with('ok', 'Usuario registrado correctamente');
    }

    // Mostrar formulario para editar
    public function edit(User $user)
    {
        $roles = Role::orderBy('nombre')->get();

        return view('settings.users.edit', compact('user', 'roles'));
    }

    // Actualizar usuario
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|max:255',
            'dni' => [
                'required',
                'digits:8',
                Rule::unique('users', 'dni')->ignore($user->id),
            ],
            'telefono' => 'nullable|string|max:15',
            'colegiatura' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Si no escribe una contraseña nueva,
        // se conserva la contraseña actual
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return to_route('usuarios.index')
            ->with('ok', 'Usuario actualizado correctamente');
    }

    // Eliminar usuario
    public function destroy(User $user)
    {
        // No permitir borrar usuarios que registraron ventas
        if ($user->sales()->exists()) {
            return to_route('usuarios.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene ventas registradas');
        }

        $user->delete();

        return to_route('usuarios.index')
            ->with('ok', 'Usuario eliminado correctamente');
    }
}
