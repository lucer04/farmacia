@extends('template.administrador')

@section('title', 'Dashboard')
@section('name-section', 'Dashboard')
@section('description', 'Resumen del negocio para tomar decisiones')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Dashboard')
@section('module-breadcrumb', 'Principal')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <small class="text-secondary">Ventas de hoy</small>
                <div class="fs-4 fw-bold">S/ {{ number_format((float) $salesToday, 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <small class="text-secondary">Ventas del mes</small>
                <div class="fs-4 fw-bold">S/ {{ number_format((float) $salesMonth, 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <small class="text-secondary">Clientes registrados</small>
                <div class="fs-4 fw-bold">{{ $customers }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <small class="text-secondary">Medicamentos con stock bajo</small>
                <div class="fs-4 fw-bold">{{ $lowStock }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <section class="card border-0 shadow-sm p-3 h-100">
                <h2 class="h6 fw-bold">Ventas de los últimos 7 días (S/)</h2>
                <div style="height: 280px"><canvas id="chartDays" aria-label="Ventas de los últimos siete días"></canvas></div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="card border-0 shadow-sm p-3 h-100">
                <h2 class="h6 fw-bold">Ventas del mes por categoría</h2>
                <div style="height: 280px"><canvas id="chartCategories" aria-label="Ventas del mes por categoría"></canvas></div>
            </section>
        </div>
        <div class="col-12">
            <section class="card border-0 shadow-sm p-3">
                <h2 class="h6 fw-bold">Los 5 medicamentos más vendidos del mes</h2>
                <div style="height: 280px"><canvas id="chartMedicines" aria-label="Cinco medicamentos más vendidos"></canvas></div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
    <script>
        const chartColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1'];

        new Chart(document.getElementById('chartDays'), {
            type: 'line',
            data: {
                labels: @json($days),
                datasets: [{
                    label: 'Ventas (S/)',
                    data: @json($totals),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, .12)',
                    fill: true,
                    tension: .3,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } },
        });

        new Chart(document.getElementById('chartCategories'), {
            type: 'doughnut',
            data: {
                labels: @json($byCategory->pluck('name')->values()),
                datasets: [{
                    data: @json($byCategory->pluck('total')->map(fn ($total) => (float) $total)->values()),
                    backgroundColor: chartColors,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });

        new Chart(document.getElementById('chartMedicines'), {
            type: 'bar',
            data: {
                labels: @json($topMedicines->pluck('name')->values()),
                datasets: [{
                    label: 'Unidades vendidas',
                    data: @json($topMedicines->pluck('units')->map(fn ($units) => (int) $units)->values()),
                    backgroundColor: chartColors,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
            },
        });
    </script>
@endpush
