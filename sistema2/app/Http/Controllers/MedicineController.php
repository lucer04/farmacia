<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MedicineController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'low_stock' => ['sometimes', 'boolean'],
        ], [
            'search.string' => 'La búsqueda debe ser texto.',
            'search.max' => 'La búsqueda no puede superar los 120 caracteres.',
            'category_id.integer' => 'La categoría seleccionada no es válida.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'low_stock.boolean' => 'El filtro de stock bajo no es válido.',
        ]);

        $medicines = Medicine::with('category')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->boolean('low_stock'), function ($query) {
                $query->where('stock', '<=', self::LOW_STOCK_THRESHOLD);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $categories = Category::orderBy('name')->get();
        $lowStockThreshold = self::LOW_STOCK_THRESHOLD;

        return view('management.medicines.index', compact('medicines', 'categories', 'filters', 'lowStockThreshold'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('management.medicines.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->imageValidationMessages());

        $imagePath = $request->hasFile('image')
            ? $this->storeImage($request->file('image'))
            : null;

        if ($imagePath !== null) {
            $validated['image'] = $imagePath;
        }

        try {
            Medicine::create($validated);
        } catch (Throwable $exception) {
            if ($imagePath !== null && ! $this->deleteImage($imagePath)) {
                Log::warning('No se pudo limpiar una imagen tras fallar el registro del medicamento.', [
                    'path' => $imagePath,
                ]);
            }

            throw $exception;
        }

        return to_route('medicines.index')->with('ok', 'Medicamento registrado correctamente.');
    }

    public function edit(Medicine $medicine)
    {
        $categories = Category::orderBy('name')->get();

        return view('management.medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->imageValidationMessages());

        $oldImagePath = $medicine->image;
        $newImagePath = $request->hasFile('image')
            ? $this->storeImage($request->file('image'))
            : null;

        if ($newImagePath !== null) {
            $validated['image'] = $newImagePath;
        } else {
            unset($validated['image']);
        }

        try {
            $updated = $medicine->update($validated);
        } catch (Throwable $exception) {
            if ($newImagePath !== null && ! $this->deleteImage($newImagePath)) {
                Log::warning('No se pudo limpiar una imagen tras fallar la actualización del medicamento.', [
                    'path' => $newImagePath,
                ]);
            }

            throw $exception;
        }

        if (! $updated) {
            if ($newImagePath !== null && ! $this->deleteImage($newImagePath)) {
                Log::warning('No se pudo limpiar una imagen tras fallar la actualización del medicamento.', [
                    'path' => $newImagePath,
                ]);
            }

            return to_route('medicines.edit', $medicine)
                ->withInput()
                ->withErrors(['medicine' => 'No se pudo actualizar el medicamento.']);
        }

        $response = to_route('medicines.index')->with('ok', 'Medicamento actualizado correctamente.');

        if (
            $newImagePath !== null
            && $oldImagePath !== null
            && $oldImagePath !== $newImagePath
            && ! $this->deleteImage($oldImagePath, $medicine->id)
        ) {
            Log::warning('No se pudo eliminar la imagen anterior del medicamento.', [
                'medicine_id' => $medicine->id,
                'path' => $oldImagePath,
            ]);

            return $response->withErrors([
                'image' => 'El medicamento se actualizó, pero no se pudo eliminar su imagen anterior.',
            ]);
        }

        return $response;
    }

    public function destroy(Medicine $medicine)
    {
        if ($medicine->detailSales()->exists()) {
            return to_route('medicines.index')
                ->withErrors(['medicine' => 'No se puede eliminar un medicamento incluido en ventas.']);
        }

        $imagePath = $medicine->image;

        if (! $medicine->delete()) {
            return to_route('medicines.index')
                ->withErrors(['medicine' => 'No se pudo eliminar el medicamento.']);
        }

        if ($imagePath !== null && ! $this->deleteImage($imagePath)) {
            Log::warning('No se pudo eliminar la imagen del medicamento eliminado.', [
                'medicine_id' => $medicine->id,
                'path' => $imagePath,
            ]);

            return to_route('medicines.index')
                ->withErrors(['image' => 'El medicamento se eliminó, pero no se pudo eliminar su imagen.']);
        }

        return to_route('medicines.index')->with('ok', 'Medicamento eliminado correctamente.');
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('medicines', 'public');

        if ($path === false) {
            throw new RuntimeException('No se pudo guardar la imagen del medicamento.');
        }

        return $path;
    }

    private function deleteImage(string $path, ?int $exceptMedicineId = null): bool
    {
        if (! str_starts_with($path, 'medicines/')) {
            Log::warning('Se omitió eliminar una ruta de imagen fuera de la carpeta de medicamentos.', [
                'path' => $path,
            ]);

            return false;
        }

        $otherMedicines = Medicine::where('image', $path);

        if ($exceptMedicineId !== null) {
            $otherMedicines->where('id', '!=', $exceptMedicineId);
        }

        if ($otherMedicines->exists()) {
            return true;
        }

        $disk = Storage::disk('public');

        return ! $disk->exists($path) || $disk->delete($path);
    }

    private function imageValidationMessages(): array
    {
        return [
            'image.image' => 'El archivo seleccionado debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'image.max' => 'La imagen no puede superar los 2 MB.',
            'image.uploaded' => 'No se pudo subir la imagen. Verifica el tamaño máximo de 2 MB.',
        ];
    }
}
