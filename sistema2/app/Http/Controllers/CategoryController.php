<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();

        return view('settings.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Category::create($validated);

        return to_route('categories.index')->with('ok', 'Categoría registrada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('settings.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate($this->rules($category), $this->messages());

        $category->update($validated);

        return to_route('categories.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->medicines()->exists()) {
            return to_route('categories.index')
                ->withErrors(['category' => 'No se puede eliminar una categoría que tiene medicamentos asociados.']);
        }

        $category->delete();

        return to_route('categories.index')->with('ok', 'Categoría eliminada correctamente.');
    }

    private function rules(?Category $category = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'name')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string' => 'El nombre de la categoría debe ser texto.',
            'name.max' => 'El nombre de la categoría no puede superar los 50 caracteres.',
            'name.unique' => 'Ya existe una categoría con ese nombre.',
            'description.string' => 'La descripción debe ser texto.',
            'description.max' => 'La descripción no puede superar los 255 caracteres.',
        ];
    }
}
