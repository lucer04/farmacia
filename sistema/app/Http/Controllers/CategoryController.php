<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categorias::all();
        return view("settings.categorias.index", compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("settings.categorias.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|max:50|unique:categorias',
            'descripcion' => 'nullable|max:255',
        ]);

        Categorias::create($validated);
        return to_route('categorias.index')->with('ok', 'Categoría registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Categorias $categoria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categorias $categoria)
    {
        return view('settings.categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categorias $categoria)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'max:50',
                Rule::unique('categorias', 'nombre')->ignore($categoria->id),
            ],
            'descripcion' => 'nullable|max:255',
        ]);

        $categoria->update($validated);

        return to_route('categorias.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorias $categoria)
    {
        // Ojo: verifica si tu relación en el modelo se llama 'medicines' o 'medicamentos'
        if (method_exists($categoria, 'medicines') && $categoria->medicines()->exists()) {
            return to_route('categorias.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene medicamentos asociados.');
        }

        $categoria->delete();

        return to_route('categorias.index')->with('ok', 'Categoría eliminada correctamente.');
    }
}