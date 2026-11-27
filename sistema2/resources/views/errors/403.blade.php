<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FARM SJB | Acceso denegado</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="bg-light d-flex min-vh-100 align-items-center justify-content-center p-3">
    <main class="card border-0 shadow-sm text-center p-4" style="max-width: 460px">
        <h1 class="h3 fw-bold">Acceso denegado</h1>
        <p class="text-secondary">
            Tu rol no tiene permiso para abrir esta página. Si crees que es un error, habla con el administrador.
        </p>
        <a href="{{ route('dashboard.index') }}" class="btn btn-primary text-white">Volver al inicio</a>
    </main>
</body>
</html>
