@extends('template.administrador')

@section('title', 'EDITAR USUARIO')
@section('description', 'Actualizar usuario')
@section('item-breadcrumb', 'Actualizar')
@section('section-breadcrumb', 'Usuarios')
@section('module-breadcrumb', 'Configuraciones')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                @include('settings.users._form')
            </form>
        </div>
    </div>
@endsection
