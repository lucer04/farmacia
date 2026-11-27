@extends('template.administrador')

@section('title', 'EDITAR PROVEEDOR')
@section('description', 'Actualizar proveedor')
@section('item-breadcrumb', 'Editar')
@section('section-breadcrumb', 'Proveedores')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                @csrf
                @method('PUT')
                @include('management.suppliers._form')
            </form>
        </div>
    </div>
@endsection
