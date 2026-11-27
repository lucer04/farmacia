@extends('template.administrador')

@section('title', 'USUARIOS')
@section('description', 'Listado de usuarios')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Usuarios')
@section('module-breadcrumb', 'Configuraciones')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white">
            <a href="{{ route('users.create') }}" class="btn btn-primary text-uppercase text-white">Nuevo usuario</a>
        </div>
        <div class="card-body">
            <h6 class="text-uppercase small text-muted mb-3">Total de registros encontrados: {{ $users->total() }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>Nombre</th><th>DNI</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->dni }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?: '—' }}</td>
                                <td>{{ $user->role->name }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No hay usuarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
