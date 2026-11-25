<div class="mb-3">
    <label for="nombre" class="form-label">Nombre de la categoria</label>
    <input type="text" name="nombre" id="nombre"
        class="form-control @error('nombre') is-invalid @enderror"
        value="{{ old('nombre', $categoria->nombre ?? '') }}" required maxlength="50">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripcion</label>
    <textarea name="descripcion" id="descripcion" rows="3"
            class="form-control @error('descripcion') is-invalid @enderror" maxlength="255">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>