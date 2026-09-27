# Arquitectura: base de datos

- **Local:** MySQL 8.0 en Docker (`docker-compose.yml`), con phpMyAdmin en `localhost:${PHPMYADMIN_PORT:-8080}`. Compose lee `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `DB_PORT` del `.env` de Laravel. Datos en el volumen `mysql_data` (`docker compose down -v` los borra).
- **Producción:** MySQL o MariaDB de cPanel. Pendiente de verificar qué motor y versión tiene el hosting; el código evita funciones exclusivas de uno u otro.
- **Tests:** SQLite en memoria (`phpunit.xml`).

## Tablas del dominio

`tipos`, `fincas`, `lotes`, `puntos`, `rutas`, `recorridos`, `recorrido_paradas`, `imagenes` (polimórfica). Migraciones en `database/migrations/2026_09_24_000001_*` y `2026_09_27_000001_*`.

- Geometrías: columnas `json` con GeoJSON **[longitud, latitud]** (ADR-002). No se consulta dentro del JSON: todo cálculo es en PHP/JS.
- Derivados que se guardan: `fincas.area_ha`, `lotes.area_ha`, `rutas.longitud_m`, `puntos.latitud/longitud`.
- FKs: lotes/puntos/rutas/recorridos → finca `cascadeOnDelete`; → tipo `nullOnDelete`; paradas → recorrido y → punto `cascadeOnDelete`. `imagenes` no tiene FK (polimórfica): se limpian con eventos de modelo.

## Seeders

- `DatabaseSeeder` → `DemoSeeder` (finca demo, 10 tipos, 6 lotes, 5 puntos) + `DemoRutasSeeder` (3 tipos de ruta, 3 rutas, 1 recorrido).
- Copian íconos/fotos de `database/data/demo/` a `public/uploads` con prefijo `demo-`.
- No crean usuarios (por seguridad): `make:filament-user`.
- `DemoSeeder` **no es idempotente** (correrlo dos veces duplica la finca). `DemoRutasSeeder` sí.
