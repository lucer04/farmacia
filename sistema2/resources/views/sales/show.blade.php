@extends('template.administrador')

@section('title', 'DETALLE DE VENTA')
@section('description', 'Detalle de venta registrada')
@section('item-breadcrumb', 'Detalle')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('partials.flash')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Venta N.º {{ $sale->id }}</strong>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">Volver a ventas</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4"><strong>Fecha:</strong> {{ $sale->date->format('d/m/Y H:i') }}</div>
                <div class="col-md-4"><strong>Cliente:</strong> {{ $sale->customer->name }} ({{ $sale->customer->dni }})</div>
                <div class="col-md-4"><strong>Usuario:</strong> {{ $sale->user->name }}</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Medicamento</th><th>Precio unitario</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($sale->details as $detail)
                            <tr>
                                <td>{{ $detail->medicine->name }}</td>
                                <td>{{ number_format((float) $detail->price_unit, 2) }}</td>
                                <td>{{ $detail->amount }}</td>
                                <td>{{ number_format((float) $detail->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="3" class="text-end">Operación gravada</th><th>S/ {{ number_format((float) $sale->total - (float) $sale->igv, 2) }}</th></tr>
                        <tr><th colspan="3" class="text-end">IGV incluido (18%)</th><th>S/ {{ number_format((float) $sale->igv, 2) }}</th></tr>
                        <tr><th colspan="3" class="text-end">Total</th><th>S/ {{ number_format((float) $sale->total, 2) }}</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
