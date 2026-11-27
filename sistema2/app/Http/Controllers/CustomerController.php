<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(15);

        return view('settings.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('settings.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'digits:8', 'unique:customers,dni'],
            'phone' => ['required', 'digits:9'],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        Customer::create($validated);

        return to_route('customers.index')->with('ok', 'Cliente registrado correctamente.');
    }

    public function edit(Customer $customer)
    {
        return view('settings.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'digits:8', Rule::unique('customers', 'dni')->ignore($customer->id)],
            'phone' => ['required', 'digits:9'],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        $customer->update($validated);

        return to_route('customers.index')->with('ok', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return to_route('customers.index')
                ->withErrors(['customer' => 'No se puede eliminar un cliente con ventas registradas.']);
        }

        $customer->delete();

        return to_route('customers.index')->with('ok', 'Cliente eliminado correctamente.');
    }
}
