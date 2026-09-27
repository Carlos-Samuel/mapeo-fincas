# Dominio: rutas y recorridos

Se agregaron el 2026-09-27. Son dos cosas distintas:

| | Ruta | Recorrido |
|---|---|---|
| Qué es | Línea dibujada (camino, cerca, tubería…) | Visita guiada: paradas ordenadas sobre **puntos existentes** |
| Tabla | `rutas` | `recorridos` + `recorrido_paradas` |
| Geometría | `geometria` LineString propia | No tiene: la línea se arma uniendo los puntos de sus paradas |
| Tipo | Sí (`tipo_id`, tipos con `aplica_a` = `ruta` o `todos`) | No; tiene su propio `color` |
| Medida | `longitud_m` (Haversine, se calcula al guardar) | — |
| Validación de contorno | Advierte si un vértice queda fuera (igual que lotes/puntos) | No aplica (sus puntos ya se validaron) |
| Panel | `panel/elemento.blade.php` | `panel/recorrido.blade.php` (paso a paso) |

## Reglas

- Una parada referencia un `punto_id` de **la misma finca**. El formulario solo ofrece puntos de la finca elegida (`RecorridoForm`, `$get('../../finca_id')`) y **vacía las paradas si cambia la finca**. La base no impone "misma finca" (Pendiente: no hay restricción en BD).
- Mínimo 2 paradas (`minItems(2)`).
- Borrar un punto borra sus paradas (FK `cascadeOnDelete`); el recorrido queda con menos paradas.
- En el mapa público los recorridos **no son capas**: se dibujan solo al seleccionarlos (línea discontinua + números) y se ocultan al volver.
- Un recorrido con < 2 paradas con punto no se dibuja en el mapa (`mapa.js`), aunque su panel sí carga.
- `Tipo::APLICA_A`: `lote`, `punto`, `ambos` (= lote + punto, valor histórico), `ruta`, `todos` (= lote + punto + ruta).

## Código

- Modelos: `app/Models/Ruta.php`, `Recorrido.php`, `RecorridoParada.php`.
- Migración: `database/migrations/2026_09_27_000001_create_rutas_y_recorridos_tables.php`.
- Admin: `app/Filament/Resources/Rutas/`, `app/Filament/Resources/Recorridos/`. `MapaGeometria` con `->entidad('ruta')` activa el modo línea (`getModo()` = `linea`).
- Público: `PanelController::ruta|recorrido`, rutas `panel.ruta`, `panel.recorrido`; en `public/js/mapa.js` ver `irAParada`, `marcarParada`, `capas.recorrido`.
- Demo: `DemoRutasSeeder` + `database/data/demo_rutas.php` (idempotente: no hace nada si la finca demo ya tiene rutas o recorridos).
