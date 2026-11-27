@extends('template.administrador')

@section('title', 'PROVEEDORES')
@section('description', 'Listado de proveedores')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Proveedores')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white">
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary text-uppercase text-white">Nuevo proveedor</a>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label">Buscar por razón social o RUC</label>
                    <input id="search" name="search" type="search" maxlength="100" value="{{ $filters['search'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label for="status" class="form-label">Estado</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activo</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactivo</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary text-white">Filtrar</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>

            <h6 class="text-uppercase small text-muted mb-3">Total de registros encontrados: {{ $suppliers->total() }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>Razón social</th><th>RUC</th><th>Teléfono</th><th>Correo</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->ruc }}</td>
                                <td>{{ $supplier->phone ?? '—' }}</td>
                                <td>{{ $supplier->email ?? '—' }}</td>
                                <td><span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $supplier->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                                <td class="text-nowrap">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este proveedor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No hay proveedores registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $suppliers->links() }}
        </div>
    </div>
@endsection
