<div class="mb-3">
    <label for="role_id" class="form-label">Rol</label>
    <select id="role_id" name="role_id" required class="form-select @error('role_id') is-invalid @enderror">
        <option value="">Seleccione un rol</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? '') == $role->id)>{{ $role->name }}</option>
        @endforeach
    </select>
    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($roles->isEmpty())
        <div class="form-text text-danger">Debe existir al menos un rol para registrar usuarios.</div>
    @endif
</div>
<div class="mb-3">
    <label for="name" class="form-label">Nombre</label>
    <input id="name" name="name" required value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="dni" class="form-label">DNI (8 dígitos)</label>
    <input id="dni" name="dni" inputmode="numeric" maxlength="8" required value="{{ old('dni', $user->dni ?? '') }}" class="form-control @error('dni') is-invalid @enderror">
    @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Teléfono</label>
    <input id="phone" name="phone" maxlength="15" value="{{ old('phone', $user->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="tuition" class="form-label">Colegiatura</label>
    <input id="tuition" name="tuition" maxlength="20" value="{{ old('tuition', $user->tuition ?? '') }}" class="form-control @error('tuition') is-invalid @enderror">
    @error('tuition')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="email" class="form-label">Correo electrónico</label>
    <input id="email" name="email" type="email" required value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="password" class="form-label">Contraseña @isset($user)<span class="text-muted">(dejar en blanco para conservarla)</span>@endisset</label>
    <input id="password" name="password" type="password" autocomplete="new-password" @empty($user) required @endempty class="form-control @error('password') is-invalid @enderror">
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @empty($user) required @endempty class="form-control">
</div>
<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
