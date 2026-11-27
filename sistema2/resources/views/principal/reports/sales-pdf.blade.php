<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de ventas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #202633; }
        h1 { margin: 0 0 6px; color: #0d6efd; font-size: 20px; }
        .filters, .summary { width: 100%; margin: 12px 0; border-collapse: collapse; }
        .filters td, .summary td { padding: 5px; border: 1px solid #dce2ea; }
        .label { font-weight: bold; background: #f2f5f9; }
        table.sales { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .sales th, .sales td { padding: 6px; border: 1px solid #dce2ea; }
        .sales th { color: #fff; background: #0d6efd; text-align: left; }
        .number { text-align: right; }
        .empty { padding: 16px; text-align: center; color: #697386; }
    </style>
</head>
<body>
    <h1>Reporte de ventas — Farmacia San Juan</h1>
    <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>

    <table class="filters">
        <tr><td class="label">Desde</td><td>{{ $filters['Desde'] }}</td><td class="label">Hasta</td><td>{{ $filters['Hasta'] }}</td></tr>
        <tr><td class="label">Cliente</td><td>{{ $filters['Cliente'] }}</td><td class="label">Vendedor</td><td>{{ $filters['Vendedor'] }}</td></tr>
        <tr><td class="label">Categoría</td><td colspan="3">{{ $filters['Categoría'] }}</td></tr>
    </table>

    <table class="summary">
        <tr>
            <td class="label">N.º de ventas</td><td>{{ $summary['count'] }}</td>
            <td class="label">Total vendido</td><td>S/ {{ number_format($summary['total'], 2) }}</td>
            <td class="label">IGV incluido</td><td>S/ {{ number_format($summary['igv'], 2) }}</td>
            <td class="label">Promedio</td><td>S/ {{ number_format($summary['average'], 2) }}</td>
        </tr>
    </table>

    <table class="sales">
        <thead>
            <tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th class="number">IGV (S/)</th><th class="number">Total (S/)</th></tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="number">{{ number_format((float) $sale->igv, 2) }}</td>
                    <td class="number">{{ number_format((float) $sale->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No hay ventas para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
