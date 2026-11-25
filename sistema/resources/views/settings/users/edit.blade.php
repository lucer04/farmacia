
@extends('template.administrador')

@section('title', 'EDITAR USUARIO')
@section('name_section', 'USUARIOS')
@section('description', 'Edición de usuario')
@section('item_section', 'Inicio')
@section('section', 'Usuarios')
@section('module', 'Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-header bg-white">
        <h5 class="mb-0">Editar usuario</h5>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('usuarios.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            @include('settings.users.form')

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
