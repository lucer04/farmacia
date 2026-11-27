<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer', 'user'])->latest('date')->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $medicines = Medicine::where('stock', '>', 0)->orderBy('name')->get();

        return view('sales.create', compact('customers', 'medicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'distinct', 'exists:medicines,id'],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ]);

        $sale = DB::transaction(function () use ($validated) {
            $medicineIds = collect($validated['items'])->pluck('medicine_id');
            $medicines = Medicine::whereIn('id', $medicineIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $details = [];

            foreach ($validated['items'] as $index => $item) {
                $medicine = $medicines->get($item['medicine_id']);

                if (! $medicine) {
                    throw ValidationException::withMessages([
                        "items.{$index}.medicine_id" => 'El medicamento seleccionado ya no está disponible.',
                    ]);
                }

                if ($medicine->stock < $item['amount']) {
                    throw ValidationException::withMessages([
                        "items.{$index}.amount" => "Stock insuficiente para {$medicine->name}. Disponible: {$medicine->stock}.",
                    ]);
                }

                $subtotal = round((float) $medicine->price * $item['amount'], 2);
                $total = round($total + $subtotal, 2);
                $details[] = [
                    'medicine' => $medicine,
                    'amount' => $item['amount'],
                    'price_unit' => $medicine->price,
                    'subtotal' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'date' => now(),
                'total' => $total,
                'igv' => round($total * 18 / 118, 2),
            ]);

            foreach ($details as $detail) {
                $sale->details()->create([
                    'medicine_id' => $detail['medicine']->id,
                    'amount' => $detail['amount'],
                    'price_unit' => $detail['price_unit'],
                    'subtotal' => $detail['subtotal'],
                ]);

                $detail['medicine']->decrement('stock', $detail['amount']);
            }

            return $sale;
        });

        return to_route('sales.show', $sale)->with('ok', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.medicine']);

        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::whereKey($sale->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($sale->details()->orderBy('medicine_id')->get() as $detail) {
                $medicine = Medicine::whereKey($detail->medicine_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $medicine->increment('stock', $detail->amount);
            }

            if (! $sale->delete()) {
                throw new RuntimeException('No se pudo anular la venta.');
            }
        });

        return to_route('sales.index')->with('ok', 'Venta anulada y stock devuelto correctamente.');
    }
}
