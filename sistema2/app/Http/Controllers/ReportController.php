<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        [$from, $to] = $this->dateRange($filters);
        $query = $this->salesQuery($filters, $from, $to);
        $summary = $this->summary($query);

        $chart = (clone $query)
            ->selectRaw('DATE(date) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(fn ($row) => [
                CarbonImmutable::parse($row->day)->format('d/m/Y') => (float) $row->total,
            ]);

        $sales = (clone $query)
            ->with(['customer', 'user'])
            ->orderByDesc('date')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::query()->orderBy('name')->get();
        $users = User::query()->orderBy('name')->get();
        $categories = Category::query()->orderBy('name')->get();

        return view('principal.reports.sales', compact(
            'sales',
            'summary',
            'chart',
            'from',
            'to',
            'customers',
            'users',
            'categories',
        ))->with('exportFilters', $filters);
    }

    public function salesPdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        [$from, $to] = $this->dateRange($filters);
        $query = $this->salesQuery($filters, $from, $to);
        $sales = (clone $query)->with(['customer', 'user'])->orderBy('date')->get();

        return Pdf::loadView('principal.reports.sales-pdf', [
            'sales' => $sales,
            'summary' => $this->summary($query),
            'filters' => $this->filterLabels($filters, $from, $to),
        ])->setPaper('a4', 'landscape')
            ->download($this->filename($from, $to, 'pdf'));
    }

    public function salesExcel(Request $request)
    {
        $filters = $this->validatedFilters($request);
        [$from, $to] = $this->dateRange($filters);
        $query = $this->salesQuery($filters, $from, $to);
        $sales = (clone $query)->with(['customer', 'user'])->orderBy('date')->get();

        return Excel::download(
            new SalesReportExport($sales, $this->filterLabels($filters, $from, $to), $this->summary($query)),
            $this->filename($from, $to, 'xlsx'),
        );
    }

    private function validatedFilters(Request $request): array
    {
        $request->merge([
            'from' => $request->input('from') ?: now()->startOfMonth()->toDateString(),
            'to' => $request->input('to') ?: today()->toDateString(),
        ]);

        return $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
    }

    private function dateRange(array $filters): array
    {
        $from = CarbonImmutable::parse($filters['from'])->startOfDay();
        $to = CarbonImmutable::parse($filters['to'])->endOfDay();

        if ($to->lessThan($from)) {
            throw ValidationException::withMessages([
                'to' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            ]);
        }

        return [$from, $to];
    }

    private function salesQuery(array $filters, CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return Sale::query()
            ->whereBetween('date', [$from, $to])
            ->when($filters['customer_id'] ?? null, fn ($sales, $id) => $sales->where('customer_id', $id))
            ->when($filters['user_id'] ?? null, fn ($sales, $id) => $sales->where('user_id', $id))
            ->when($filters['category_id'] ?? null, fn ($sales, $id) => $sales->whereHas(
                'details.medicine',
                fn ($medicines) => $medicines->where('category_id', $id),
            ));
    }

    private function summary(Builder $query): array
    {
        $totals = (clone $query)
            ->selectRaw('COUNT(*) as sales_count, COALESCE(SUM(total), 0) as total, COALESCE(SUM(igv), 0) as igv, COALESCE(AVG(total), 0) as average')
            ->first();

        return [
            'count' => (int) $totals->sales_count,
            'total' => (float) $totals->total,
            'igv' => (float) $totals->igv,
            'average' => (float) $totals->average,
        ];
    }

    private function filterLabels(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [
            'Desde' => $from->format('d/m/Y'),
            'Hasta' => $to->format('d/m/Y'),
            'Cliente' => isset($filters['customer_id'])
                ? Customer::query()->findOrFail($filters['customer_id'])->name
                : 'Todos',
            'Vendedor' => isset($filters['user_id'])
                ? User::query()->findOrFail($filters['user_id'])->name
                : 'Todos',
            'Categoría' => isset($filters['category_id'])
                ? Category::query()->findOrFail($filters['category_id'])->name
                : 'Todas',
        ];
    }

    private function filename(CarbonImmutable $from, CarbonImmutable $to, string $extension): string
    {
        return sprintf(
            'reporte-ventas-%s-%s.%s',
            $from->format('Ymd'),
            $to->format('Ymd'),
            $extension,
        );
    }
}
