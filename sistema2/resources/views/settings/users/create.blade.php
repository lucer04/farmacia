@extends('template.administrador')

@section('title', 'NUEVO USUARIO')
@section('description', 'Registrar usuario')
@section('item-breadcrumb', 'Nuevo')
@section('section-breadcrumb', 'Usuarios')
@section('module-breadcrumb', 'Configuraciones')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                @include('settings.users._form')
            </form>
        </div>
    </div>
@endsection
