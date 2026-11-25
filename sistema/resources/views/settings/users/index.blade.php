
@extends('template.administrador')

@section('title', 'USUARIOS')
@section('name_section', 'USUARIOS')
@section('description', 'Listado de usuarios')
@section('item_section', 'Inicio')
@section('section', 'Usuarios')
@section('module', 'Configuraciones')

@section('content')

@if (session('ok'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('ok') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">

    <div class="card-header bg-white">
        <div class="row">
            <div class="col-12 col-md-3">
                <a href="{{ route('usuarios.create') }}"
                   class="btn btn-primary rounded-3 text-uppercase text-white w-100">
                    <i class="bi bi-plus-lg me-2"></i>
                    <span class="small fw-medium">Nuevo usuario</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">

        <h6 class="text-uppercase small text-muted mb-3">
            Total de usuarios encontrados: {{ $users->count() }}
        </h6>

        <div class="table-responsive users-table-responsive">
    <table class="table table-sm table-hover w-100 bg-white">

                <thead>
                    <tr>
                        <th class="text-center align-middle text-uppercase small">N.º</th>
                        <th class="text-center align-middle text-uppercase small">Nombre</th>
                        <th class="text-center align-middle text-uppercase small">DNI</th>
                        <th class="text-center align-middle text-uppercase small">Correo</th>
                        <th class="text-center align-middle text-uppercase small">Rol</th>
                        <th class="text-center align-middle text-uppercase small">Acciones</th>
                    </tr>
                </thead>

                <tbody>
    @forelse($users as $user)
        <tr>
            <td class="text-center align-middle">
                {{ $loop->iteration }}
            </td>

            <td class="text-start align-middle">
                {{ $user->name }}
            </td>

            <td class="text-center align-middle">
                {{ $user->dni }}
            </td>

            <td class="text-start align-middle">
                {{ $user->email }}
            </td>

            <td class="text-center align-middle">
                {{ $user->role->nombre ?? 'Sin rol' }}
            </td>

            <td class="text-center align-middle">
                <div class="dropdown users-actions-dropdown">
                    <button class="btn btn-light"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false">
    <i class="bi bi-three-dots-vertical"></i>
</button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item"
                               href="{{ route('usuarios.edit', $user->id) }}">
                                <i class="bi bi-pencil-square me-2"></i>
                                Editar
                            </a>
                        </li>

                        <li>
                            <form action="{{ route('usuarios.destroy', $user->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i>
                                    Eliminar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center py-4 text-muted">
                No hay usuarios registrados.
            </td>
        </tr>
    @endforelse
</tbody>

            </table>
        </div>
    </div>
</div>

@endsection
