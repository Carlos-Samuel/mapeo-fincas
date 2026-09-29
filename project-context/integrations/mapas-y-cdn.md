# Integraciones: mapas y CDN

Todas se usan **desde el navegador**, sin llaves ni cuentas. Si alguna cae, el mapa sigue funcionando parcialmente.

| Servicio | Uso | Dónde |
|---|---|---|
| unpkg — Leaflet 1.9.4 | Librería de mapas (sitio y admin) | `layouts/publico.blade.php`, `fincas/show.blade.php`, `filament/mapa-assets.blade.php` |
| unpkg — @geoman-io/leaflet-geoman-free 2.20.2 | Herramientas de dibujo del admin | `filament/mapa-assets.blade.php` |
| Esri World Imagery + World_Boundaries_and_Places | Capa base «Satélite» + etiquetas | `public/js/capas-base.js` |
| Esri World Imagery **Clarity** (beta) — `clarity.maptiles.arcgis.com` | Capa base «Satélite nítido (beta)»: en muchas zonas rurales imagen más nítida/reciente | `public/js/capas-base.js` |
| OpenStreetMap tiles | Capa base «Mapa» | `public/js/capas-base.js` |
| Nominatim (OSM) | Buscador de lugares en el admin | `mapa-geometria.js` → `buscar()` |

## Notas

- Versiones fijadas en las URLs; para actualizar, cambiar la URL y probar el dibujo en el admin.
- Esri y Nominatim tienen políticas de uso razonable; Nominatim pide poco volumen (uso manual del admin está bien). Pendiente de verificar condiciones de Esri para uso público/comercial.
- Leaflet se usa como global `L`; Geoman se engancha en `map.pm`.
- Zoom máximo 21 con `maxNativeZoom: 19` (Esri no tiene más detalle; se amplía la última tesela).

## Resolución del mapa (2026-09-28)

- Todas las capas base viven en **`public/js/capas-base.js`** (`CapasBase.agregar(mapa)`), compartido por el sitio público y el admin. La capa elegida se recuerda por navegador (`localStorage`, clave `fincas.capaBase`).
- La nitidez depende de la imagen que exista para la zona, no del código: por eso se ofrece elegir fuente. Pendiente de verificar en la finca real cuál se ve mejor.
- **Prohibido** usar teselas de Google o Bing sin su API oficial (términos de uso). Opciones de pago/cuenta descartadas por el dueño por ahora: Mapbox, Google Maps API, ortomosaico de dron.
