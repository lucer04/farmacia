<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acceso al sistema de farmacia San Juan">
    <meta name="theme-color" content="#0d6efd">
    <meta name="service-worker" content="{{ asset('sw.js') }}">
    <title>FARM SJB | Iniciar sesión</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ asset('pwa/icon-192.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <main class="login-wrapper d-flex">
        <aside class="login-brand bg-dark text-white d-none d-lg-flex flex-column justify-content-between p-5"
               aria-hidden="true">
            <div class="d-flex align-items-center">
                <div class="brand-icon rounded-circle bg-primary d-flex align-items-center justify-content-center me-3"
                     style="width: 44px; height: 44px">
                    <i class="bi bi-plus-lg text-white fs-5 fw-bold"></i>
                </div>
                <div class="lh-sm">
                    <div class="text-white fw-bold fs-5">San Juan</div>
                    <div class="sidebar-section-title text-white-50 mt-1">Sistema de Venta de Medicamentos</div>
                </div>
            </div>

            <div class="login-brand-copy">
                <h1 class="display-6 fw-bold mb-3">Gestiona tu farmacia de forma simple y segura.</h1>
                <p class="text-white-50 mb-0">Ventas, inventario y clientes en un solo lugar.</p>
            </div>

            <ul class="list-unstyled d-flex flex-column gap-3 mb-0 small">
                <li class="text-white-50"><i class="bi bi-shield-check text-primary me-2"></i>Acceso protegido por roles</li>
                <li class="text-white-50"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Reportes para el negocio</li>
                <li class="text-white-50"><i class="bi bi-box-seam text-primary me-2"></i>Control de stock</li>
            </ul>
        </aside>

        <section class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
            <div class="login-card w-100">
                <div class="d-flex d-lg-none align-items-center justify-content-center mb-4">
                    <div class="brand-icon rounded-circle bg-primary d-flex align-items-center justify-content-center me-2"
                         style="width: 40px; height: 40px" aria-hidden="true">
                        <i class="bi bi-plus-lg text-white fw-bold"></i>
                    </div>
                    <span class="fw-bold fs-5 text-dark">San Juan</span>
                </div>

                <div class="mb-4">
                    <h2 class="h3 fw-bold text-dark mb-1">Bienvenido</h2>
                    <p class="text-secondary small mb-0">Ingresa tus credenciales para continuar.</p>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mb-3 d-none" data-pwa-install>
                    Instalar aplicación
                </button>

                @if (session('ok'))
                    <div class="alert alert-success small py-2" role="status">{{ session('ok') }}</div>
                @endif

                @error('email')
                    <div class="alert alert-danger small py-2" role="alert">{{ $message }}</div>
                @enderror

                <form action="{{ route('login.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Correo electrónico</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-person text-secondary"></i></span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                   autocomplete="username" autofocus
                                   class="form-control border-start-0 @error('email') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Contraseña</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lock text-secondary"></i></span>
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                   class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror">
                            <button class="input-group-text bg-white" type="button" id="togglePassword"
                                    aria-label="Mostrar contraseña">
                                <i class="bi bi-eye text-secondary" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                               value="1" @checked(old('remember'))>
                        <label class="form-check-label small text-secondary" for="remember">Mantener sesión iniciada</label>
                    </div>

                    <button class="btn btn-primary text-white w-100 py-2 fw-semibold" type="submit" id="loginSubmit">
                        <span class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                        <span class="btn-label">Iniciar sesión</span>
                    </button>
                </form>

                <p class="text-center text-secondary small mt-5 mb-0">
                    &copy; {{ date('Y') }} FARM SJB
                </p>
            </div>
        </section>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', () => {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            togglePassword.setAttribute('aria-label', showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
            togglePassword.querySelector('i').className = `bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'} text-secondary`;
        });

        togglePassword.form.addEventListener('submit', () => {
            const submit = document.getElementById('loginSubmit');
            submit.disabled = true;
            submit.querySelector('.spinner-border').classList.remove('d-none');
            submit.querySelector('.btn-label').textContent = 'Ingresando...';
        });
    </script>
    <script src="{{ asset('js/pwa.js') }}" defer></script>
</body>
</html>
