# ARCHITECTURE — visión general

Un solo repositorio Laravel (`fincas`) con dos caras: el **sitio público** (Blade + Leaflet) y el **admin** (Filament).
No hay frontend separado, API pública externa, colas ni procesos batch.

```mermaid
flowchart LR
    V[Visitante] -->|HTML| SP["Sitio público<br/>FincaController / PanelController<br/>Blade + public/js/mapa.js"]
    A[Administrador] -->|Livewire| AD["Admin /admin<br/>Filament 4<br/>campo MapaGeometria"]
    SP -->|JSON mapa.json<br/>HTML panel/*| SP
    AD -->|fetch /api/fincas/{id}/geometrias| SP
    SP --> DB[(MySQL 8<br/>GeoJSON en columnas json)]
    AD --> DB
    AD -->|subidas| UP[/public/uploads/]
    SP -->|img| UP
    SP & AD -.CDN.-> CDN["unpkg: Leaflet, Geoman"]
    SP & AD -.teselas.-> T["Esri World Imagery / OSM"]
    AD -.búsqueda.-> N[Nominatim OSM]
```

## Componentes

| Componente | Qué hace | Detalle |
|---|---|---|
| Sitio público | Portada, página de finca, JSON del mapa, fragmentos HTML del panel | `architecture/mapa-publico.md` |
| Admin Filament | CRUD de fincas, lotes, puntos, rutas, recorridos, tipos | `architecture/admin-filament.md` |
| Base de datos | MySQL 8 en Docker (local); MySQL/MariaDB en cPanel (producción) | `architecture/base-de-datos.md` |
| Archivos | Imágenes subidas en `public/uploads` (disco `uploads`) | `domains/contenido-panel.md`, ADR-003 |

## Comunicación

- El mapa público carga **un JSON por finca** (`GET /fincas/{slug}/mapa.json`) con geometrías y tipos, y pide el panel como **HTML renderizado por Laravel** (`GET /panel/{lotes|puntos|rutas|recorridos}/{id}`). El resumen de la finca viene ya renderizado en la página.
- El campo de mapa del admin pide `GET /api/fincas/{id}/geometrias` para dibujar referencias. Es público (sin auth) porque es la misma información del sitio público.

## Persistencia

Geometrías como **GeoJSON en columnas `json`** (ADR-002). Áreas y longitudes se calculan en PHP (`app/Support/Geo.php`) en los eventos `saving` de los modelos.

## Infraestructura

- Local: `php artisan serve` en el Mac + `docker compose` (MySQL 8 + phpMyAdmin). Sin Node (ADR-001).
- Producción objetivo: hosting compartido con cPanel (`architecture/despliegue-cpanel.md`). Pendiente de verificar: aún no se ha publicado.

## Integraciones externas

Todas en el navegador, sin llaves: `integrations/mapas-y-cdn.md`.
