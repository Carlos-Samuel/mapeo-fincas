# Arquitectura: admin (Filament 4)

- Panel `admin` en `/admin`, login de Filament, color primario verde. Proveedor: `app/Providers/Filament/AdminPanelProvider.php` (registrado en `bootstrap/providers.php`).
- `User implements FilamentUser` y `canAccessPanel()` devuelve `true`: **todo usuario registrado es admin**. Usuarios se crean con `php artisan make:filament-user`.
- Recursos (estructura v4: `Resource`, `Pages/`, `Schemas/*Form`, `Tables/*Table`): Fincas (1), Lotes (2), Puntos (3), Rutas (4), Recorridos (5), Tipos (6) — número = orden en el menú.
- Crear finca redirige a su edición; la edición tiene acciones "Agregar lote/punto/ruta/recorrido" que abren el formulario con `?finca_id=` (el `Select` usa `->default(request()->integer('finca_id'))`).
- Enlaces "Ver lotes de esta finca" usan el filtro por URL `?filters[finca][value]=ID` (en v4 el parámetro es `filters`).
- Idioma: `APP_LOCALE=es` (Filament trae su traducción; validaciones en `lang/es/validation.php`).

## Campo MapaGeometria

`app/Filament/Forms/Components/MapaGeometria.php` (Field de Filament) + vista Blade + `public/js/admin/mapa-geometria.js` (componente Alpine `mapaGeometria`).

- API: `->entidad('finca'|'lote'|'punto'|'ruta')`, `->campoFinca('finca_id')` (campo hermano con la finca) o `->fincaId(fn ($record) => …)` (formulario de finca), `->altura('480px')`.
- Modos: `poligono` (Geoman: polígono/rectángulo/editar/mover/borrar), `linea` (Geoman polyline), `punto` (clic en el mapa + marcador arrastrable, sin Geoman).
- Estado: GeoJSON (`Polygon`, `LineString`, `Point`) enlazado con `$wire.$entangle(statePath)`. El id de la finca también se enlaza con `$entangle('data.finca_id')` y un `$watch` recarga las referencias al cambiarla.
- Referencias: `GET /api/fincas/{id}/geometrias` → contorno (línea blanca discontinua), lotes, rutas y puntos, **no editables** (`pmIgnore: true`) pero **imantables** (`snapIgnore: false`). Excluye el propio registro (`registroId`).
- Muestra área (ha) o longitud (m/km) y la advertencia "queda por fuera" en vivo.
- Buscador: Nominatim o coordenadas «lat, lng».
- Assets (Leaflet, Geoman, JS/CSS del campo) se inyectan en `<head>` con el render hook `PanelsRenderHook::HEAD_END` → `resources/views/filament/mapa-assets.blade.php`. El JS registra `Alpine.data` en `alpine:init`, por eso debe cargar **antes** que Livewire/Alpine (sin `defer`).
- El contenedor tiene `wire:ignore` (Livewire no debe re-renderizar el mapa) y `isolation: isolate` (para que las capas de Leaflet no tapen menús/modales).

Pendiente de verificar: el campo se probó con Leaflet real y Alpine/Geoman simulados; falta confirmar en el admin real el dibujo con Geoman (crear, editar vértices, arrastrar, borrar) y el `$entangle`.
