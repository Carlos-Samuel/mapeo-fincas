# Convenciones

- **Idioma:** dominio, modelos, tablas, columnas, vistas, textos y comentarios en **español** (`Finca`, `Lote`, `geometria`, `contenido`). Código del framework y nombres técnicos en inglés cuando vienen de Laravel/Filament.
- **Sin Node** (ADR-001): no agregar dependencias npm ni `@vite`. Librerías de navegador por CDN con versión fija.
- **Geometrías:** siempre GeoJSON `[lng, lat]`. Nuevos derivados (área, longitud) se calculan en `saving` del modelo con `App\Support\Geo`.
- **Nuevo tipo de elemento en el mapa** (como se hizo con rutas): modelo + migración + `TieneImagenes` + recurso Filament con `ContenidoPanel::seccion` + `MapaGeometria` + `AdvierteSiQuedaFuera` + salida en `FincaController::datos` y `geometrias` + `PanelController` + ruta `panel.*` + manejo en `mapa.js` (`CLASES`, `capas`, selección) + listado en `panel/finca.blade.php` + seeder demo + tests.
- **HTML de contenido:** siempre por `panel/_contenido.blade.php` (saneado).
- **Archivos:** disco `uploads`, nunca `public` ni `local` para contenido visible.
- **Recursos Filament:** estructura v4 (`Resource`, `Pages`, `Schemas`, `Tables`), labels en español en el Resource (`$modelLabel`, `$pluralModelLabel`).
- **Commits:** no hay convención definida (Pendiente de verificar si el dueño usa git para este proyecto).
