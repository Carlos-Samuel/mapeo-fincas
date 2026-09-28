# Arquitectura: despliegue (StackCP / Colombia Imagina)

Estado: **publicado y funcionando** en https://software-cs.com desde 2026-09-28. Pasos detallados para humanos en `README.md` (raíz) → "Publicar en cPanel".

Puntos críticos para agentes:

- Subir con `vendor/` (el hosting puede no tener Composer) instalado con `composer install --no-dev`.
- Hosting real: **Colombia Imagina**, que usa **StackCP (plataforma 20i), no cPanel**. Dominio `software-cs.com` → `~/public_html` (el sitio HTML anterior quedó renombrado como `~/public_html2`).
- SSH: `software-cs.com@ssh.us.stackcp.com`, solo con llave pública registrada en StackCP → SSH Access (la primera llave tarda hasta 30 min en activarse; antes de eso el nodo interno responde "Permission denied" aunque la llave sea aceptada por la pasarela).
- Home: `/home/sites/18a/b/b684a721e4/`. PHP CLI 8.3 (`/usr/php83/usr/bin/php`), Composer 2.8 y Git disponibles; extensiones intl, pdo_mysql, mbstring, fileinfo activas. `~/.htaccess` fija `AddHandler x-httpd-php83` para todo el home (no tocar).
- Base de datos: host **remoto** `sdb-86.hosting.stackcp.net` (no `localhost`); base `mapeo-fincas-353130303395`, usuario `mapeo-fincas` (la contraseña solo en el `.env` del servidor).
- Despliegue oficial (2026-09-28): `git clone` del repo `Carlos-Samuel/mapeo-fincas` en `~/fincas` + `composer install --no-dev` + `.env` + `migrate --force` + **enlace simbólico `~/public_html -> ~/fincas/public`**. Si el hosting no siguiera symlinks (403), alternativa: copiar `public/` a `public_html/` y cambiar `/../` por `/../fincas/` en su `index.php`.
- Estructura en el servidor: proyecto en `~/fincas/`; **contenido de `public/` copiado a `~/public_html/`**, con `public_html/index.php` apuntando a `../fincas/` (reemplazar `/../` por `/../fincas/`).
- `public/index.php` llama `$app->usePublicPath(__DIR__)` (desde 2026-09-27): así `public_path()` y el disco `uploads` apuntan a `public_html/` en el hosting y a `public/` en local. Sin esto, las imágenes subidas desde el admin irían a `~/fincas/public/uploads` (invisible en la web).
- Ojo: comandos de consola (`php artisan …`) **no** pasan por `index.php`, así que para ellos `public_path()` sigue siendo `~/fincas/public`. No correr seeders ni `filament:assets` en el servidor esperando que escriban en `public_html` (o copiar después).
- Cada actualización de archivos de `public/` (JS, CSS, assets de Filament) hay que copiarla también a `public_html/`.
- `APP_ENV=production` exige `FilamentUser` (ya implementado).
- Ejecutar `php artisan optimize` y `php artisan filament:optimize`.
- Permisos de escritura: `storage/`, `bootstrap/cache/`, `public/uploads/`.
- PHP del hosting: 8.3+ con `intl`, `pdo_mysql`, `fileinfo`, `mbstring` (el dueño confirmó 8.3+; extensiones pendientes de verificar).

## Flujo de actualización (oficial)

1. Local: cambiar código → `php artisan test` → `git add -A && git commit -m "…" && git push`.
2. Servidor (SSH): `~/deploy.sh` (script en el home del servidor, fuera del repo), que hace:
   `php artisan down` → `git pull --ff-only` → `composer install --no-dev --optimize-autoloader` → `php artisan migrate --force` → `php artisan optimize` → `php artisan filament:optimize` → `php artisan up`.
3. Reglas:
   - **Nunca editar código directamente en el servidor**: rompe `git pull`. Excepción: `.env`.
   - Tras cambiar `.env` en el servidor: `php artisan optimize` (la config está cacheada; si no, el cambio no se ve).
   - El contenido (fincas, lotes, fotos en `public/uploads`) vive solo en la base y el disco del servidor: no viaja por Git. Respaldar con export de phpMyAdmin + descarga de `public/uploads`.
   - Revertir un cambio: `git revert <commit>` en local + push + deploy (no hacer `reset` en el servidor).
