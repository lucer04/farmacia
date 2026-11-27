@extends('template.administrador')

@section('title', 'VENTAS')
@section('description', 'Registro de ventas')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white">
            <a href="{{ route('sales.create') }}" class="btn btn-primary text-uppercase text-white">Registrar venta</a>
        </div>
        <div class="card-body">
            <h6 class="text-uppercase small text-muted mb-3">Total de registros encontrados: {{ $sales->total() }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Usuario</th><th>Total</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                                <td>{{ $sale->customer->name }}</td>
                                <td>{{ $sale->user->name }}</td>
                                <td>S/ {{ number_format((float) $sale->total, 2) }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">Ver detalle</a>
                                    @if (auth()->user()->hasRole('Administrador'))
                                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Anular esta venta? El stock será devuelto.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Anular</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No hay ventas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $sales->links() }}
        </div>
    </div>
@endsection
