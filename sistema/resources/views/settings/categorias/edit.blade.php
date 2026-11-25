@extends('template.administrador')

@section('title', 'CATEGORIES')
@section('name_section', 'CATEGORIAS')
@section('description', 'Editar categoría')
@section('item_section', 'Actualizar registro')
@section('section', 'categorias')
@section('module', 'Configuraciones')

@section('content')
<div class="card lg white border-0 shadow-sm border-0 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('categorias.update', $categoria->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('settings.categorias.form')
        </form>
    </div>
</div>
@endsection