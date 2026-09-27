# Arquitectura: mapa público

- Layout `resources/views/layouts/publico.blade.php`; portada `fincas/index`; página `fincas/show` con `#mapa` + `<aside id="panel">`.
- `public/js/mapa.js` (IIFE, `L` global del CDN) — flujo:
  1. `fetch(mapa.json)` → dibuja contorno (no interactivo), lotes (polígonos), puntos (`divIcon` con el ícono del tipo), rutas (líneas) y prepara recorridos (ocultos).
  2. Agrupa por tipo en `featureGroup`s → control de capas.
  3. Hover: tooltip `sticky` con ícono (lotes/rutas); tooltip normal en puntos.
  4. Clic → `seleccionar(clase, id)` → resalta, actualiza la URL (`history.replaceState`) y carga `/panel/{clase}s/{id}` como HTML.
  5. Volver / Esc / clic en mapa vacío → restaura el HTML del resumen guardado al cargar (`HTML_RESUMEN`).
- Deep link: al cargar lee `?lote=`, `?punto=`, `?ruta=`, `?recorrido=` (el primero que exista).
- Recorridos: al seleccionar se agrega su grupo (línea + números), el panel trae un `<section class="paso">` por parada; `irAParada(i)` muestra una, marca el punto y mueve el mapa.
- Todo el contenido dinámico se escapa en JS (`esc`) o viene ya saneado del servidor.
- Responsive: bajo 900 px el panel pasa debajo del mapa y al seleccionar se hace scroll al panel.
- Estilos en `public/css/sitio.css` (sin Tailwind). Versionado de caché con `?v=filemtime`.
