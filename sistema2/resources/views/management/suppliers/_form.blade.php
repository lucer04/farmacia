<div class="mb-3">
    <label for="name" class="form-label">Nombre o razón social</label>
    <input id="name" name="name" maxlength="100" required value="{{ old('name', $supplier->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="ruc" class="form-label">RUC</label>
    <input id="ruc" name="ruc" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" required value="{{ old('ruc', $supplier->ruc ?? '') }}" class="form-control @error('ruc') is-invalid @enderror">
    @error('ruc')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="address" class="form-label">Dirección</label>
    <input id="address" name="address" maxlength="150" value="{{ old('address', $supplier->address ?? '') }}" class="form-control @error('address') is-invalid @enderror">
    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Teléfono</label>
    <input id="phone" name="phone" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" value="{{ old('phone', $supplier->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="email" class="form-label">Correo electrónico</label>
    <input id="email" name="email" type="email" maxlength="100" value="{{ old('email', $supplier->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="is_active" class="form-label">Estado</label>
    <select id="is_active" name="is_active" required class="form-select @error('is_active') is-invalid @enderror">
        <option value="1" @selected((string) old('is_active', isset($supplier) ? (int) $supplier->is_active : 1) === '1')>Activo</option>
        <option value="0" @selected((string) old('is_active', isset($supplier) ? (int) $supplier->is_active : 1) === '0')>Inactivo</option>
    </select>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancelar</a>
