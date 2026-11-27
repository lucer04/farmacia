<div class="mb-3">
    <label for="name" class="form-label">Nombre</label>
    <input id="name" name="name" maxlength="150" required value="{{ old('name', $customer->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="dni" class="form-label">DNI (8 dígitos)</label>
    <input id="dni" name="dni" inputmode="numeric" maxlength="8" required value="{{ old('dni', $customer->dni ?? '') }}" class="form-control @error('dni') is-invalid @enderror">
    @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Teléfono</label>
    <input id="phone" name="phone" inputmode="numeric" maxlength="9" required value="{{ old('phone', $customer->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="age" class="form-label">Edad</label>
    <input id="age" name="age" type="number" min="0" max="120" required value="{{ old('age', $customer->age ?? '') }}" class="form-control @error('age') is-invalid @enderror">
    @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancelar</a>
