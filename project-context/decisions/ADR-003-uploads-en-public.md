# ADR-003 - Imágenes subidas en public/uploads

Status: Aceptada
Date: 2026-09-24

## Context
Laravel sirve `storage/app/public` con el enlace simbólico `storage:link`; muchos hostings cPanel no permiten crearlo.

## Decision
Disco `uploads` (`config/filesystems.php`) con raíz `public_path('uploads')` y URL `APP_URL/uploads`. Todo FileUpload/RichEditor usa `->disk('uploads')`.

## Reasons
Funciona en cualquier hosting sin symlinks.

## Consequences
- `APP_URL` debe ser correcto en cada entorno o las URLs de imágenes fallan.
- `public/uploads/` está en `.gitignore` (solo se versiona su `.gitignore`).
- Si la carpeta pública cambia de lugar (cPanel con `public_html`), ver `architecture/despliegue-cpanel.md`.

## Affected repositories
fincas
