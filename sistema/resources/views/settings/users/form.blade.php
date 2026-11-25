
<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <label for="name" class="form-label">Nombre completo</label>
        <input
            type="text"
            name="name"
            id="name"
            class="form-control"
            value="{{ old('name', $user->name ?? '') }}"
            required
        >
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="dni" class="form-label">DNI</label>
        <input
            type="text"
            name="dni"
            id="dni"
            class="form-control"
            value="{{ old('dni', $user->dni ?? '') }}"
            maxlength="8"
            required
        >
        @error('dni')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input
            type="email"
            name="email"
            id="email"
            class="form-control"
            value="{{ old('email', $user->email ?? '') }}"
            required
        >
        @error('email')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="role_id" class="form-label">Rol</label>
        <select name="role_id" id="role_id" class="form-select" required>
            <option value="">Seleccionar rol</option>

            @foreach ($roles as $role)
                <option
                    value="{{ $role->id }}"
                    @selected(old('role_id', $user->role_id ?? '') == $role->id)
                >
                    {{ $role->nombre }}
                </option>
            @endforeach
        </select>
        @error('role_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            autocomplete="new-password"
            {{ isset($user) ? '' : 'required' }}
        >
        @if (isset($user))
            <small class="text-muted">
                Deja este campo vacío si no deseas cambiar la contraseña.
            </small>
        @endif
        @error('password')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">
            Confirmar contraseña
        </label>
        <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            class="form-control"
            autocomplete="new-password"
            {{ isset($user) ? '' : 'required' }}
        >
    </div>
</div>
