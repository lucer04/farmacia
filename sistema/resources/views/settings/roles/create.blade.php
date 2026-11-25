@extends('template.administrador')

@section('title', 'REGISTRAR ROL')
@section('name_section', 'ROLES')
@section('description', 'Registrar nuevo rol')
@section('item_section', 'Inicio')
@section('section', 'Roles')
@section('module', 'Configuraciones')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-header bg-white">
        <span class="text-secondary small">Formulario de registro</span>
    </div>
    <div class="card-body">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            @include('settings.roles.form')
        </form>
    </div>
</div>
@endsection