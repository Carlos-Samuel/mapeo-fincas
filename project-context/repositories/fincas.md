# Repositorio: fincas

- **Ruta:** raíz del workspace (esta carpeta `project-context/` está dentro). En el Mac del dueño: `~/Documents/fincas`.
- **Tecnología:** PHP 8.3+ · Laravel 13 · Filament 4 (Livewire 3, Alpine) · MySQL 8 · Leaflet 1.9.4 + Leaflet-Geoman 2.20.2 por CDN.
- **Sin Node/npm/Vite** (ADR-001). JS y CSS propios se editan directo en `public/`.

## Responsabilidades

- Sitio público de mapas y panel lateral.
- Admin de contenido (Filament) con dibujo de geometrías.
- Cálculos geográficos simples (área, longitud, dentro/fuera).

## Dónde está cada cosa

| Área | Ruta |
|---|---|
| Modelos | `app/Models/` — `Finca`, `Lote`, `Punto`, `Ruta`, `Recorrido`, `RecorridoParada`, `Tipo`, `Imagen`; trait `Concerns/TieneImagenes` |
| Geo (área, longitud, punto en polígono) | `app/Support/Geo.php` (PHP) y `public/js/geo-herramientas.js` (JS: además coordenadas y GPX) |
| GPX en servidor | `app/Support/Gpx.php`, `app/Support/ImportadorPuntosGpx.php` |
| Capas base del mapa (satélite, satélite nítido, calles) | `public/js/capas-base.js` |
| Admin: recursos | `app/Filament/Resources/{Fincas,Lotes,Puntos,Rutas,Recorridos,Tipos}/` (Resource, Pages, Schemas, Tables) |
| Admin: sección común de contenido | `app/Filament/Forms/ContenidoPanel.php` |
| Admin: campo de mapa | `app/Filament/Forms/Components/MapaGeometria.php` + `resources/views/filament/forms/components/mapa-geometria.blade.php` + `public/js/admin/mapa-geometria.js` + `public/css/admin/mapa-geometria.css` |
| Admin: panel y assets por CDN | `app/Providers/Filament/AdminPanelProvider.php`, `resources/views/filament/mapa-assets.blade.php` |
| Advertencia "fuera de la finca" | `app/Filament/Resources/Concerns/AdvierteSiQuedaFuera.php` |
| Sitio público | `routes/web.php`, `app/Http/Controllers/{FincaController,PanelController}.php`, `resources/views/{layouts,fincas,panel}/`, `public/js/mapa.js`, `public/css/sitio.css` |
| Datos demo | `database/data/finca_demo.php`, `database/data/demo_rutas.php`, `database/data/demo/` (íconos y fotos), `database/seeders/` |
| Docker | `docker-compose.yml` (lee `.env`) |
| Traducciones | `lang/es/validation.php` (solo reglas usadas) |

## Comandos

```bash
docker compose up -d                          # MySQL + phpMyAdmin (localhost:8080)
php artisan migrate                           # aplicar migraciones nuevas
php artisan db:seed --class=DemoRutasSeeder   # rutas/recorrido demo sobre una base ya sembrada
php artisan migrate:fresh --seed              # reiniciar todo con datos demo (¡borra!)
php artisan make:filament-user                # crear administrador
php artisan serve                             # http://127.0.0.1:8000  (admin en /admin)
php artisan test                              # SQLite en memoria
```

## Consume / lo consumen

- Consume: CDN unpkg, teselas Esri/OSM, Nominatim (todo desde el navegador).
- Nadie lo consume por API; `/api/fincas/{id}/geometrias` es interno del admin.

## Comportamientos no evidentes

- La finca usa **id** como clave de ruta (admin, API); solo las rutas públicas piden el **slug** explícito (`{finca:slug}`). Ver `development/gotchas.md`.
- `composer install` ejecuta `filament:upgrade`, que publica los assets de Filament en `public/js|css|fonts/filament` (necesarios en producción).
- Ver `development/gotchas.md`.
