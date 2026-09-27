# Integraciones: mapas y CDN

Todas se usan **desde el navegador**, sin llaves ni cuentas. Si alguna cae, el mapa sigue funcionando parcialmente.

| Servicio | Uso | Dónde |
|---|---|---|
| unpkg — Leaflet 1.9.4 | Librería de mapas (sitio y admin) | `layouts/publico.blade.php`, `fincas/show.blade.php`, `filament/mapa-assets.blade.php` |
| unpkg — @geoman-io/leaflet-geoman-free 2.20.2 | Herramientas de dibujo del admin | `filament/mapa-assets.blade.php` |
| Esri World Imagery + World_Boundaries_and_Places | Fondo satelital y etiquetas | `mapa.js`, `mapa-geometria.js` |
| OpenStreetMap tiles | Capa alternativa "Mapa" | ídem |
| Nominatim (OSM) | Buscador de lugares en el admin | `mapa-geometria.js` → `buscar()` |

## Notas

- Versiones fijadas en las URLs; para actualizar, cambiar la URL y probar el dibujo en el admin.
- Esri y Nominatim tienen políticas de uso razonable; Nominatim pide poco volumen (uso manual del admin está bien). Pendiente de verificar condiciones de Esri para uso público/comercial.
- Leaflet se usa como global `L`; Geoman se engancha en `map.pm`.
- Zoom máximo 21 con `maxNativeZoom: 19` (Esri no tiene más detalle; se amplía la última tesela).
