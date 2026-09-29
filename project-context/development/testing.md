# Pruebas

- `php artisan test` (PHPUnit). Usa **SQLite en memoria** (`phpunit.xml`), no toca MySQL.
- `tests/Unit/GeoTest.php` — área, dentro/fuera.
- `tests/Feature/SitioPublicoTest.php` — portada, página, `mapa.json`, paneles (lote, punto, ruta, recorrido), saneo de HTML, borrado en cascada, idempotencia de `DemoRutasSeeder`.
- `tests/Unit/GpxTest.php`, `tests/Feature/ImportarPuntosGpxTest.php` — lector GPX, importador y acción del admin (fixture `tests/fixtures/puntos.gpx`; usa `Storage::fake('local')`).
- `tests/Feature/AdminTest.php` — pantallas del admin, crear lote/punto/ruta/recorrido, advertencias, validaciones.
- Siempre `Storage::fake('uploads')` (los seeders escriben archivos).
- Formularios con Repeater: usar `Repeater::fake()` (claves numéricas) — ya está en `AdminTest::setUp`.
- Primera ejecución en el Mac del dueño (2026-09-27): 25/26 en verde; falló "las pantallas del admin cargan" por el 404 de edición de fincas (ver `gotchas.md`), ya corregido. Pendiente de confirmar la suite completa en verde tras la corrección.
- El JS del sitio se probó con Playwright contra datos simulados (fuera del repo); no hay tests JS en el repositorio.
