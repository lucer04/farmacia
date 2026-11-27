<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'search.string' => 'La búsqueda debe ser texto.',
            'search.max' => 'La búsqueda no puede superar los 100 caracteres.',
            'status.in' => 'El estado seleccionado no es válido.',
        ]);

        $suppliers = Supplier::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('ruc', 'like', '%'.$search.'%');
                });
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('management.suppliers.index', compact('suppliers', 'filters'));
    }

    public function create()
    {
        return view('management.suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($this->validatedData($request));

        return to_route('suppliers.index')->with('ok', 'Proveedor registrado correctamente.');
    }

    public function show(Supplier $supplier)
    {
        return view('management.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('management.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validatedData($request, $supplier));

        return to_route('suppliers.index')->with('ok', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier)
    {
        if (! $supplier->delete()) {
            return to_route('suppliers.index')
                ->withErrors(['supplier' => 'No se pudo eliminar el proveedor.']);
        }

        return to_route('suppliers.index')->with('ok', 'Proveedor eliminado correctamente.');
    }

    private function validatedData(Request $request, ?Supplier $supplier = null): array
    {
        $uniqueRuc = Rule::unique('suppliers', 'ruc');

        if ($supplier !== null) {
            $uniqueRuc->ignore($supplier->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'ruc' => ['required', 'digits:11', $uniqueRuc],
            'phone' => ['nullable', 'digits:9'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:150'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'El nombre o razón social es obligatorio.',
            'name.max' => 'El nombre o razón social no puede superar los 100 caracteres.',
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique' => 'El RUC ya está registrado.',
            'phone.digits' => 'El teléfono debe tener exactamente 9 dígitos.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 100 caracteres.',
            'address.max' => 'La dirección no puede superar los 150 caracteres.',
            'is_active.required' => 'Selecciona el estado del proveedor.',
            'is_active.boolean' => 'El estado seleccionado no es válido.',
        ]);
    }
}
