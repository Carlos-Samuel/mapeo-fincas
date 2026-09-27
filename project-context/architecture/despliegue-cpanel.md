# Arquitectura: despliegue en cPanel

Estado: **Pendiente de verificar** (aún no se ha publicado). Pasos detallados para humanos en `README.md` (raíz) → "Publicar en cPanel".

Puntos críticos para agentes:

- Subir con `vendor/` (el hosting puede no tener Composer) instalado con `composer install --no-dev`.
- La raíz de documentos debe apuntar a `public/` (ideal: subdominio). Si se usa `public_html` con el contenido de `public/` movido, **`public_path()` deja de coincidir** y el disco `uploads` (`public_path('uploads')`) escribiría en el lugar equivocado: habría que ajustar `$app->usePublicPath(...)` en `bootstrap/app.php`. No hacerlo sin confirmar con el dueño.
- `APP_ENV=production` exige `FilamentUser` (ya implementado).
- Ejecutar `php artisan optimize` y `php artisan filament:optimize`.
- Permisos de escritura: `storage/`, `bootstrap/cache/`, `public/uploads/`.
- PHP del hosting: 8.3+ con `intl`, `pdo_mysql`, `fileinfo`, `mbstring` (el dueño confirmó 8.3+; extensiones pendientes de verificar).
