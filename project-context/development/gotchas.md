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

### Slug vs id en rutas
Repository: fincas
Area: rutas web
Problem: `route('fincas.show', $finca)` genera el slug; el endpoint del admin usa id.
Reason: `Finca::getRouteKeyName()` = `slug`; `/api/fincas/{finca:id}/geometrias` fuerza id.
What to do: No cambiar uno sin el otro; el JS del admin arma la URL con el id.

### El admin de fincas daba 404 al editar
Repository: fincas
Area: admin, `FincaResource`
Problem: `/admin/fincas/{id}/edit` respondía 404 (y el botón «Ir a la finca» de lotes/puntos/rutas también).
Reason: Filament usa `getRouteKeyName()` del modelo para resolver el registro; `Finca` devuelve `slug`, así que buscaba una finca con slug "1".
What to do: `FincaResource::$recordRouteKeyName = 'id'` (aplicado 2026-09-27). Cualquier recurso nuevo cuyo modelo cambie la clave de ruta necesita lo mismo.

### `make:filament-user` es interactivo
Repository: fincas
Area: instalación
Problem: Pide Name, Email y Password (la contraseña no se ve al escribir); si se pegan varios comandos juntos parece que "se colgó".
Reason: Comando interactivo de Filament.
What to do: Correr los comandos de instalación uno por uno.
