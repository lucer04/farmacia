@extends('template.administrador')

@section('title', 'Reporte de ventas')
@section('name-section', 'Reporte de ventas')
@section('description', 'Ventas por rango de fechas y filtros')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Reportes')
@section('module-breadcrumb', 'Principal')

@section('content')
    <section class="card border-0 shadow-sm p-3 mb-3">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="from" class="form-label small">Desde</label>
                <input type="date" name="from" id="from" value="{{ $from->toDateString() }}"
                       class="form-control @error('from') is-invalid @enderror">
                @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="to" class="form-label small">Hasta</label>
                <input type="date" name="to" id="to" value="{{ $to->toDateString() }}"
                       class="form-control @error('to') is-invalid @enderror">
                @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="customer_id" class="form-label small">Cliente</label>
                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                    <option value="">Todos</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="user_id" class="form-label small">Vendedor</label>
                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="category_id" class="form-label small">Categoría</label>
                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary text-white flex-grow-1" type="submit">Filtrar</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-light" title="Quitar filtros">Limpiar</a>
            </div>
        </form>
    </section>

    <div class="d-flex justify-content-end gap-2 mb-3">
        <a class="btn btn-outline-danger" href="{{ route('reports.sales.pdf', $exportFilters) }}">
            Descargar PDF
        </a>
        <a class="btn btn-outline-success" href="{{ route('reports.sales.excel', $exportFilters) }}">
            Descargar Excel
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><small class="text-secondary">N.º de ventas</small><div class="fs-5 fw-bold">{{ $summary['count'] }}</div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><small class="text-secondary">Total vendido</small><div class="fs-5 fw-bold">S/ {{ number_format($summary['total'], 2) }}</div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><small class="text-secondary">IGV</small><div class="fs-5 fw-bold">S/ {{ number_format($summary['igv'], 2) }}</div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3"><small class="text-secondary">Venta promedio</small><div class="fs-5 fw-bold">S/ {{ number_format($summary['average'], 2) }}</div></div></div>
    </div>

    <section class="card border-0 shadow-sm p-3 mb-3">
        <h2 class="h6 fw-bold">Total vendido por día (S/)</h2>
        <div style="height: 280px"><canvas id="chartReport" aria-label="Total vendido por día"></canvas></div>
    </section>

    <section class="card border-0 shadow-sm p-3">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th class="text-end">IGV</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}">{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</a></td>
                            <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->customer->name }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td class="text-end">S/ {{ number_format((float) $sale->igv, 2) }}</td>
                            <td class="text-end">S/ {{ number_format((float) $sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">No hay ventas con esos filtros.</td></tr>
                    @endforelse
                </tbody>
                @if ($sales->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Totales</th>
                            <th class="text-end">S/ {{ number_format($summary['igv'], 2) }}</th>
                            <th class="text-end">S/ {{ number_format($summary['total'], 2) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        {{ $sales->links() }}
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
    <script>
        new Chart(document.getElementById('chartReport'), {
            type: 'bar',
            data: {
                labels: @json($chart->keys()->values()),
                datasets: [{
                    label: 'Total (S/)',
                    data: @json($chart->values()),
                    backgroundColor: '#0d6efd',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    </script>
@endpush
