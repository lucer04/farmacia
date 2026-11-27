<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class SalesReportExport implements FromArray
{
    public function __construct(
        private readonly Collection $sales,
        private readonly array $filters,
        private readonly array $summary,
    ) {}

    public function array(): array
    {
        $rows = [
            ['Reporte de ventas'],
            ['Desde', $this->filters['Desde'], 'Hasta', $this->filters['Hasta']],
            ['Cliente', $this->filters['Cliente'], 'Vendedor', $this->filters['Vendedor']],
            ['Categoría', $this->filters['Categoría']],
            [],
            ['N.º de venta', 'Fecha', 'Cliente', 'Vendedor', 'IGV (S/)', 'Total (S/)'],
        ];

        foreach ($this->sales as $sale) {
            $rows[] = [
                str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT),
                $sale->date->format('d/m/Y H:i'),
                $sale->customer->name,
                $sale->user->name,
                (float) $sale->igv,
                (float) $sale->total,
            ];
        }

        $rows[] = [];
        $rows[] = [
            'Resumen',
            'Ventas',
            $this->summary['count'],
            'Total vendido (S/)',
            $this->summary['total'],
            'IGV (S/)',
            $this->summary['igv'],
            'Promedio (S/)',
            $this->summary['average'],
        ];

        return $rows;
    }
}
