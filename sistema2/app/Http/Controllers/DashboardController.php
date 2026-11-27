<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $salesToday = Sale::query()->whereDate('date', $today)->sum('total');
        $salesMonth = Sale::query()->whereBetween('date', [$monthStart, $monthEnd])->sum('total');
        $customers = Customer::query()->count();
        $lowStock = Medicine::query()->where('stock', '<=', 10)->count();

        $dailyTotals = Sale::query()
            ->selectRaw('DATE(date) as day, SUM(total) as total')
            ->whereBetween('date', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $days = [];
        $totals = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $day = today()->subDays($offset);
            $days[] = $day->format('d/m');
            $totals[] = (float) ($dailyTotals[$day->toDateString()] ?? 0);
        }

        $byCategory = DB::table('detail_sales')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->whereBetween('sales.date', [$monthStart, $monthEnd])
            ->select('categories.id', 'categories.name')
            ->selectRaw('SUM(detail_sales.subtotal) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        $topMedicines = DB::table('detail_sales')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->whereBetween('sales.date', [$monthStart, $monthEnd])
            ->select('medicines.id', 'medicines.name')
            ->selectRaw('SUM(detail_sales.amount) as units')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        return view('principal.dashboard.index', compact(
            'salesToday',
            'salesMonth',
            'customers',
            'lowStock',
            'days',
            'totals',
            'byCategory',
            'topMedicines',
        ));
    }
}
