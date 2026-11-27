# Sistema de farmacia San Juan

Aplicación web para administrar una farmacia: inventario, clientes, usuarios, ventas,
reportes y control de acceso por roles.

## Módulos

- CRUD de roles, categorías, usuarios, clientes y medicamentos.
- CRUD de proveedores con búsqueda, filtros por estado y datos de contacto.
- Búsqueda, filtros, paginación, stock bajo y gestión de imágenes de medicamentos.
- Registro, edición y anulación de ventas con detalle, validación de stock y operaciones transaccionales.
- Dashboard con indicadores y gráficos de ventas, categorías y medicamentos.
- Reportes de ventas con filtros por fechas, cliente, vendedor y categoría; descarga en PDF y Excel.
- Inicio/cierre de sesión, permisos por rol y PWA con fallback sin conexión.

## Tecnologías

- PHP 8.3 o posterior y Laravel 13.
- MySQL/MariaDB para una instalación de uso normal; SQLite en memoria para pruebas.
- Chart.js 4, Bootstrap, Laravel DomPDF y Maatwebsite Excel.
- Node.js y npm para compilar los recursos con Vite.

## Instalación en un entorno nuevo

Desde la carpeta del proyecto:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Configura localmente la conexión de base de datos en `.env`; no compartas ese archivo.
Después crea la base vacía y ejecuta las migraciones aditivas:

```powershell
php artisan migrate
```

Para poblar una base local vacía con roles, cuentas y datos de demostración:

```powershell
php artisan db:seed
```

El seeder de demostración solo se permite en `local` o `testing`, conserva cuentas existentes
y conserva los proveedores existentes; genera ventas de muestra únicamente si el catálogo y las ventas están vacíos. No lo ejecutes
contra una base productiva.

Instala y compila los recursos del frontend, prepara el acceso a imágenes y arranca Laravel:

```powershell
npm ci
npm run build
php artisan storage:link
php artisan serve
```

El ZIP de entrega excluye bases de datos locales, archivos `.env` e imágenes cargadas para no
distribuir datos privados. Conserva una copia segura de tus imágenes si necesitas restaurarlas.

Abre la URL local mostrada por Artisan y accede mediante la pantalla de inicio de sesión.
Los usuarios de demostración se crean con el seeder local; cambia sus contraseñas antes de
compartir o desplegar el sistema. No se incluyen credenciales en este documento.

## Exportaciones

En **Reportes → Ventas**, aplica los filtros que necesites y utiliza los botones **PDF** o
**Excel**. Cada archivo incluye el periodo, los filtros, el detalle de ventas y los totales.
Las descargas requieren autenticación y permiso de Administrador o Químico farmacéutico.

## PWA e instalación

El manifest y los iconos se sirven desde `public/`; el Service Worker almacena solo recursos
estáticos públicos y una página de fallback. No guarda páginas autenticadas ni información de
ventas o clientes. El fallback sin conexión no permite operar el sistema.

Para instalarla, abre el sistema en un navegador compatible y usa la opción **Instalar** cuando
aparezca. La instalación requiere HTTPS, excepto en `localhost` o `127.0.0.1`. En iOS, la opción
de añadir a pantalla de inicio depende del navegador y puede ser manual.

## Pruebas y verificaciones

PHPUnit está configurado para SQLite `:memory:`:

```powershell
php artisan test
```

Las migraciones y seeders de instalación no se ejecutan automáticamente al correr las pruebas.
Para verificar estilo:

```powershell
vendor/bin/pint --test
```
