@extends('template.administrador')

@section('title', 'MEDICAMENTOS')
@section('description', 'Inventario de medicamentos')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Medicamentos')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white">
            <a href="{{ route('medicines.create') }}" class="btn btn-primary text-uppercase text-white">Nuevo medicamento</a>
        </div>
        <div class="card-body">
            <form action="{{ route('medicines.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label">Buscar por nombre</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        maxlength="120"
                        value="{{ old('search', $filters['search'] ?? '') }}"
                        class="form-control"
                        placeholder="Nombre del medicamento"
                    >
                </div>
                <div class="col-12 col-md-4">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $filters['category_id'] ?? '') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-check">
                        <input
                            id="low_stock"
                            name="low_stock"
                            type="checkbox"
                            value="1"
                            class="form-check-input"
                            @checked(request()->boolean('low_stock'))
                        >
                        <label for="low_stock" class="form-check-label">Stock bajo (≤ {{ $lowStockThreshold }})</label>
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary text-white">Filtrar</button>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>

            <h6 class="text-uppercase small text-muted mb-3">Total de registros encontrados: {{ $medicines->total() }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Existencia</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td>
                                    @if ($medicine->image)
                                        <img src="{{ asset('storage/' . $medicine->image) }}" alt="Imagen de {{ $medicine->name }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover">
                                    @else
                                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-secondary" style="width: 48px; height: 48px" role="img" aria-label="Sin imagen">
                                            <i class="bi bi-image" aria-hidden="true"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $medicine->name }}</td>
                                <td>{{ $medicine->category->name }}</td>
                                <td>{{ number_format((float) $medicine->price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $medicine->stock <= $lowStockThreshold ? 'bg-danger' : 'bg-success' }}">{{ $medicine->stock <= $lowStockThreshold ? 'Stock bajo: ' : '' }}{{ $medicine->stock }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('medicines.destroy', $medicine) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este medicamento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No hay medicamentos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $medicines->links() }}
        </div>
    </div>
@endsection
