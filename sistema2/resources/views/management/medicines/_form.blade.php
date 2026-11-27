<div class="mb-3">
    <label for="category_id" class="form-label">Categoría</label>
    <select id="category_id" name="category_id" required class="form-select @error('category_id') is-invalid @enderror">
        <option value="">Seleccione una categoría</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $medicine->category_id ?? '') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($categories->isEmpty())
        <div class="form-text text-danger">Debe existir al menos una categoría para registrar medicamentos.</div>
    @endif
</div>
<div class="mb-3">
    <label for="name" class="form-label">Nombre</label>
    <input id="name" name="name" maxlength="120" required value="{{ old('name', $medicine->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="price" class="form-label">Precio unitario</label>
    <input id="price" name="price" type="number" min="0.01" step="0.01" required value="{{ old('price', $medicine->price ?? '') }}" class="form-control @error('price') is-invalid @enderror">
    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="stock" class="form-label">Existencia</label>
    <input id="stock" name="stock" type="number" min="0" step="1" required value="{{ old('stock', $medicine->stock ?? 0) }}" class="form-control @error('stock') is-invalid @enderror">
    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="image" class="form-label">Imagen del medicamento (opcional)</label>
    <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="form-control @error('image') is-invalid @enderror">
    <div class="form-text">Formatos permitidos: JPG, JPEG, PNG o WEBP. Tamaño máximo: 2 MB.</div>
    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if (!empty($medicine?->image))
        <div class="mt-2">
            <small class="text-muted d-block mb-1">Imagen actual:</small>
            <img src="{{ asset('storage/' . $medicine->image) }}" alt="Imagen actual de {{ $medicine->name }}" class="img-thumbnail" style="max-height: 120px">
        </div>
    @endif
</div>
<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('medicines.index') }}" class="btn btn-secondary">Cancelar</a>
