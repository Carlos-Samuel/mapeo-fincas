# Arquitectura: despliegue en cPanel

Estado: **Pendiente de verificar** (aún no se ha publicado). Pasos detallados para humanos en `README.md` (raíz) → "Publicar en cPanel".

Puntos críticos para agentes:

- Subir con `vendor/` (el hosting puede no tener Composer) instalado con `composer install --no-dev`.
- Hosting real: **Colombia Imagina** (cPanel), dominio principal → `public_html` (el sitio HTML anterior quedó renombrado como `public_html2`).
- Estructura en el servidor: proyecto en `~/fincas/`; **contenido de `public/` copiado a `~/public_html/`**, con `public_html/index.php` apuntando a `../fincas/` (reemplazar `/../` por `/../fincas/`).
- `public/index.php` llama `$app->usePublicPath(__DIR__)` (desde 2026-09-27): así `public_path()` y el disco `uploads` apuntan a `public_html/` en el hosting y a `public/` en local. Sin esto, las imágenes subidas desde el admin irían a `~/fincas/public/uploads` (invisible en la web).
- Ojo: comandos de consola (`php artisan …`) **no** pasan por `index.php`, así que para ellos `public_path()` sigue siendo `~/fincas/public`. No correr seeders ni `filament:assets` en el servidor esperando que escriban en `public_html` (o copiar después).
- Cada actualización de archivos de `public/` (JS, CSS, assets de Filament) hay que copiarla también a `public_html/`.
- `APP_ENV=production` exige `FilamentUser` (ya implementado).
- Ejecutar `php artisan optimize` y `php artisan filament:optimize`.
- Permisos de escritura: `storage/`, `bootstrap/cache/`, `public/uploads/`.
- PHP del hosting: 8.3+ con `intl`, `pdo_mysql`, `fileinfo`, `mbstring` (el dueño confirmó 8.3+; extensiones pendientes de verificar).
