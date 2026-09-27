# ADR-002 - MySQL/MariaDB con GeoJSON en columnas json (sin PostGIS)

Status: Aceptada
Date: 2026-09-24

## Context
Inicialmente se pidió PostgreSQL. Al saber que el hosting es cPanel compartido (normalmente solo MySQL/MariaDB), el dueño pidió cambiar a MySQL. También descartó PostGIS explícitamente.

## Decision
Guardar geometrías como GeoJSON en columnas `json`. Cálculos (área, longitud, dentro/fuera) en PHP (`app/Support/Geo.php`) y en JS para la vista previa.

## Reasons
Compatibilidad con MySQL y MariaDB de hosting compartido; las necesidades espaciales son simples (una finca, decenas de elementos).

## Consequences
- No hay consultas espaciales en SQL (ej. "lotes que tocan esta ruta"). Si se necesitan, se hacen en PHP o se reconsidera el tipo de columna.
- Lógica geográfica duplicada PHP/JS: mantener ambas en sincronía.
- En MariaDB `json` es alias de `LONGTEXT`; Laravel lo maneja con el cast `array`.

## Affected repositories
fincas
