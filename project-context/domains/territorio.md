# Dominio: territorio (fincas, lotes, puntos)

## Conceptos

- **Finca** (`fincas`): nombre, `slug` único (URL pública), ubicación, `geometria` (Polygon, contorno), `area_ha`, `contenido`.
- **Lote** (`lotes`): pertenece a una finca; `tipo_id` opcional; `codigo` (ej. L-01), `geometria` Polygon, `area_ha`.
- **Punto** (`puntos`): pertenece a una finca; `tipo_id` opcional; `geometria` Point; `latitud`/`longitud` redundantes para lectura/tablas.

Rutas y recorridos: `domains/rutas-y-recorridos.md`.

## Reglas

| Regla | Dónde se implementa |
|---|---|
| `area_ha` se recalcula en cada guardado (finca y lote) | `Finca::booted`, `Lote::booted` → `Geo::areaHectareas` |
| `latitud`/`longitud` del punto se copian de `geometria` al guardar | `Punto::booted` |
| `slug` se genera del nombre si viene vacío; único con sufijo `-2`, `-3`…; **no cambia** si luego cambia el nombre | `Finca::slugUnico` |
| Lote/punto/ruta fuera del contorno: **advertir y guardar** (no bloquear) | JS en vivo (`mapa-geometria.js` → `estaDentro`) + al guardar (`AdvierteSiQuedaFuera`) |
| "Dentro" = todos los vértices dentro del contorno (no revisa cruces de aristas) | `Geo::estaDentro` / JS `estaDentro` |
| Un punto muestra "Ubicado en Lote X" si cae dentro de un lote | `Punto::loteQueLoContiene`, `PanelController::punto` |
| Borrar finca borra lotes, puntos, rutas, recorridos e imágenes **uno a uno** (para que se borren los archivos) | `Finca::booted` (deleting) + `TieneImagenes` |
| La geometría es obligatoria en el formulario (finca, lote, punto) | `->required()` en los Schemas |

## Endpoints

- `GET /fincas/{slug}` página; `GET /fincas/{slug}/mapa.json` datos; `GET /panel/lotes/{id}`, `GET /panel/puntos/{id}`.
- `GET /api/fincas/{id}/geometrias` referencias para el admin.

## Restricciones / comportamientos especiales

- Cálculo de área: fórmula esférica (la misma de Turf/Leaflet.draw), error < 0,5 % a escala de finca. Existe en PHP y duplicada en JS (`areaHa`) para mostrarla mientras se dibuja.
- Un lote puede quedar sin tipo → color gris `#9e9e9e`, capa "Sin tipo".
- No hay jerarquía de sub-lotes ni puntos dentro de lotes a nivel de datos: la relación punto→lote es geométrica y se calcula al vuelo.
