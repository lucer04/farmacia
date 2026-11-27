@extends('template.administrador')

@section('title', 'CLIENTES')
@section('description', 'Listado de clientes')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Clientes')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white">
            <a href="{{ route('customers.create') }}" class="btn btn-primary text-uppercase text-white">Nuevo cliente</a>
        </div>
        <div class="card-body">
            <h6 class="text-uppercase small text-muted mb-3">Total de registros encontrados: {{ $customers->total() }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>Nombre</th><th>DNI</th><th>Teléfono</th><th>Edad</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->dni }}</td>
                                <td>{{ $customer->phone ?: '—' }}</td>
                                <td>{{ $customer->age ?? '—' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este cliente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No hay clientes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $customers->links() }}
        </div>
    </div>
@endsection
