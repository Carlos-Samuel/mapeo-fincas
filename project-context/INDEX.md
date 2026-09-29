# INDEX — router de conocimiento

Busca la fila de tu tarea y lee **solo** esos documentos.

| Si la tarea trata de… | Lee |
|---|---|
| Qué es el producto, actores, vocabulario | `PROJECT.md` |
| Visión general técnica, cómo encaja todo | `ARCHITECTURE.md` |
| Dónde está cada cosa en el código, comandos | `repositories/fincas.md` |
| Fincas, lotes, puntos, contornos, "dentro/fuera de la finca", áreas | `domains/territorio.md` |
| Rutas (líneas) o recorridos guiados (paradas) | `domains/rutas-y-recorridos.md` |
| Tipos, íconos, colores, capas del mapa | `domains/tipos-y-capas.md` |
| Texto enriquecido, galería, panel derecho, imágenes subidas | `domains/contenido-panel.md` |
| Admin, formularios, recursos de Filament | `architecture/admin-filament.md` |
| Dibujar en el mapa del admin (campo `MapaGeometria`) | `architecture/admin-filament.md` → sección "Campo MapaGeometria" |
| Coordenadas escritas a mano, archivos GPX, importar puntos | `domains/importacion-gpx-y-coordenadas.md` |
| Fuentes de imagen satelital / resolución del mapa | `integrations/mapas-y-cdn.md` |
| Mapa público, `mapa.js`, panel, enlaces `?lote=` | `architecture/mapa-publico.md` |
| Tablas, migraciones, GeoJSON, MySQL/MariaDB, seeders demo | `architecture/base-de-datos.md` |
| Docker local, phpMyAdmin | `architecture/base-de-datos.md` + `development/conventions.md` |
| Publicar / actualizar producción (StackCP, SSH, git pull, deploy.sh) | `architecture/despliegue-cpanel.md` |
| Leaflet, Geoman, Esri, OpenStreetMap, Nominatim, CDN | `integrations/mapas-y-cdn.md` |
| ¿Por qué no hay Node / por qué MySQL / por qué `public/uploads`? | `decisions/` (ADR-001…004) |
| Estilo de código, idioma, nombres | `development/conventions.md` |
| Pruebas automáticas | `development/testing.md` |
| Errores raros, trampas conocidas | `development/gotchas.md` |
| Qué se está haciendo ahora / deuda técnica | `current/active-work.md`, `current/technical-debt.md` |

## Decisiones (ADR)

- `decisions/ADR-001-sin-node.md` — sin Node/npm/Vite.
- `decisions/ADR-002-mysql-geojson.md` — MySQL/MariaDB con GeoJSON en columnas `json`, sin PostGIS.
- `decisions/ADR-003-uploads-en-public.md` — imágenes en `public/uploads`, sin `storage:link`.
- `decisions/ADR-004-contexto-dentro-del-repo.md` — `project-context/` vive dentro del repositorio.
