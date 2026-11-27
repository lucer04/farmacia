@extends('template.administrador')

@section('title', 'DETALLE DE PROVEEDOR')
@section('description', 'Información del proveedor')
@section('item-breadcrumb', 'Detalle')
@section('section-breadcrumb', 'Proveedores')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>{{ $supplier->name }}</strong>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">Volver a proveedores</a>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">RUC</dt><dd class="col-sm-9">{{ $supplier->ruc }}</dd>
                <dt class="col-sm-3">Dirección</dt><dd class="col-sm-9">{{ $supplier->address ?? '—' }}</dd>
                <dt class="col-sm-3">Teléfono</dt><dd class="col-sm-9">{{ $supplier->phone ?? '—' }}</dd>
                <dt class="col-sm-3">Correo electrónico</dt><dd class="col-sm-9">{{ $supplier->email ?? '—' }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">{{ $supplier->is_active ? 'Activo' : 'Inactivo' }}</dd>
            </dl>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary text-white mt-3">Editar proveedor</a>
        </div>
    </div>
@endsection
