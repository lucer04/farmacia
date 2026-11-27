@extends('template.administrador')

@section('title', 'NUEVO PROVEEDOR')
@section('description', 'Registrar proveedor')
@section('item-breadcrumb', 'Nuevo')
@section('section-breadcrumb', 'Proveedores')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                @include('management.suppliers._form')
            </form>
        </div>
    </div>
@endsection
