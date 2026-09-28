# Trampas conocidas

### GeoJSON va [longitud, latitud]
Repository: fincas
Area: geometrías
Problem: Coordenadas invertidas ubican todo en otro continente.
Reason: GeoJSON usa [lng, lat]; Leaflet usa [lat, lng].
What to do: Guardar siempre [lng, lat]; convertir solo al crear objetos Leaflet (`L.latLng(lat, lng)`).

### Objetos Leaflet dentro del estado de Alpine
Repository: fincas
Area: admin, `public/js/admin/mapa-geometria.js`
Problem: Leaflet falla o se vuelve lento si el mapa/capas se guardan como propiedades reactivas de Alpine.
Reason: Alpine envuelve las propiedades en Proxies.
What to do: Mantenerlos en variables de cierre (`let mapa`, `capaEdicion`…) como ya está hecho.

### El mapa del admin no debe re-renderizarse
Repository: fincas
Area: admin, vista `mapa-geometria.blade.php`
Problem: Al cambiar un campo `live()` (ej. finca), Livewire re-renderiza el formulario y destruiría el mapa.
Reason: Morphing de Livewire.
What to do: Mantener `wire:ignore` en el contenedor; los datos dinámicos llegan por `$entangle` o `fetch`.

### Capas de Leaflet encima de menús de Filament
Repository: fincas
Area: admin CSS
Problem: Los panes de Leaflet (z-index 400+) tapan dropdowns y modales.
Reason: Contexto de apilamiento.
What to do: `isolation: isolate` en `.mapa-geometria__mapa` (ya aplicado).

### Enter en el buscador envía el formulario
Repository: fincas
Area: admin
Problem: Presionar Enter en el buscador del mapa guardaba el registro.
Reason: Es un input dentro del `<form>` de Filament.
What to do: `x-on:keydown.enter.prevent` (ya aplicado).

### Referencias no se "imantan"
Repository: fincas
Area: admin, Geoman
Problem: Con `pmIgnore: true` Geoman también ignora la capa para el snapping.
Reason: Regla de Geoman: si `snapIgnore` no está definido, hereda de `pmIgnore`.
What to do: Poner `snapIgnore: false` explícito en capas de referencia (ya aplicado).

### Varias galerías en un mismo panel
Repository: fincas
Area: sitio público, `mapa.js`
Problem: Las miniaturas cambiaban la imagen de la primera galería.
Reason: `querySelector` global en el panel; el panel del recorrido tiene una galería por parada.
What to do: Buscar dentro de `el.closest('.galeria')` (ya aplicado).

### `DemoSeeder` duplica
Repository: fincas
Area: seeders
Problem: `php artisan db:seed` sobre una base con datos crea otra "Finca El Ejemplo".
Reason: `DemoSeeder` no es idempotente.
What to do: Para agregar solo lo nuevo usar `--class=DemoRutasSeeder`; para reiniciar, `migrate:fresh --seed`.

### Fincas: slug solo en rutas públicas, id en todo lo demás
Repository: fincas
Area: rutas, admin (`FincaResource`), modelo `Finca`
Problem: 404 al editar fincas en el admin. Primer intento (2026-09-27): `Finca::getRouteKeyName() = 'slug'` + `FincaResource::$recordRouteKeyName = 'id'` → el admin **resolvía** por id pero **generaba** enlaces con el slug (`/admin/fincas/{slug}/edit`) → 404 en producción (2026-09-28). El test no lo vio porque armaba la URL a mano con el id.
Reason: `getRouteKeyName()` afecta la generación de URLs en todo Laravel/Filament, no solo la resolución.
What to do: `Finca` usa la clave por defecto (id). Las rutas públicas declaran el slug explícito: `/fincas/{finca:slug}` y `/fincas/{finca:slug}/mapa.json`; `route('fincas.show', $finca)` genera el slug solo (Laravel respeta el binding field). No volver a poner `getRouteKeyName()` en `Finca`. Test de regresión: `AdminTest::test_el_enlace_editar_de_una_finca_funciona`.

### `make:filament-user` es interactivo
Repository: fincas
Area: instalación
Problem: Pide Name, Email y Password (la contraseña no se ve al escribir); si se pegan varios comandos juntos parece que "se colgó".
Reason: Comando interactivo de Filament.
What to do: Correr los comandos de instalación uno por uno.
