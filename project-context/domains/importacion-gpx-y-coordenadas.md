# Dominio: coordenadas escritas e importación GPX

Agregado el 2026-09-28.

## Coordenadas escritas (puntos)

- En el formulario de **punto**, campo «Coordenadas del punto» + botón «Ubicar punto» (y Enter). También el buscador del mapa acepta coordenadas en cualquier modo (solo mueve el mapa).
- Formatos aceptados (`GeoHerramientas.leerCoordenadas`, `public/js/geo-herramientas.js`):
  - Decimal: `4.15123, -74.88456` · `4.15123 -74.88456` · `4.15123;-74.88456`
  - Decimal con coma: `4,15123 -74,88456` (solo si no hay puntos y los números van separados por espacio o `;`)
  - GMS / GM: `4°09'04.4"N 74°53'04.4"W` · `N 4 09 04 O 74 53 04` · `4°09.074'N 74°53.07'W` (acepta `O` de Oeste)
- **Regla:** sin letras N/S/E/W se asume **latitud primero**. Con letras, el orden no importa.
- Validaciones: minutos/segundos < 60, |lat| ≤ 90, |lng| ≤ 180. El resultado se muestra normalizado en decimal en el mismo campo.
- Solo existe en el navegador (no hay parser PHP de coordenadas).

## GPX en los formularios (finca, lote, ruta, punto)

- Botón «Cargar GPX» en el campo `MapaGeometria`. Se procesa **en el navegador** (`GeoHerramientas.leerGpx` + `geometriaDesdeGpx`); nada se sube al servidor: el resultado queda como forma editable y se guarda como cualquier dibujo.
- Prioridad de fuente: **track más largo → ruta (`rte`) más larga → waypoints unidos en orden**.
- Polígonos (finca, lote): se cierra el anillo automáticamente; se descarta el último punto si coincide (< 0,5 m) con el primero. Mínimo 3 vértices.
- Líneas (ruta): mínimo 2 puntos.
- **Simplificación Douglas-Peucker en metros**: tolerancia 0,5 m (quita ruido del GPS); si quedan más de 300 vértices (polígono) / 500 (línea) se sube la tolerancia ×1,6 hasta máx. ~50 m. Se informa "simplificado de N a M vértices".
- Punto: usa los waypoints. Si hay uno, lo ubica; si hay varios, muestra la lista para elegir. Si el campo «nombre» está vacío, propone el nombre del waypoint (`$wire.set`, vía `MapaGeometria::getCampoNombreStatePath`).
- Límite: 10 MB por archivo.

## Importar muchos puntos de una vez

- Acción «Importar puntos (GPX)» en el menú (⋯) de **Editar finca** (`EditFinca::importarPuntosGpx`).
- Lado servidor: `App\Support\Gpx::leer` (DOMDocument, `LIBXML_NONET`, sin expandir entidades) + `App\Support\ImportadorPuntosGpx::importar`.
- Reglas: cada waypoint → un punto; **se omite** si ya hay un punto con el mismo nombre en la finca (sin distinguir mayúsculas); `<desc>`/`<cmt>` pasa a `contenido`; tipo opcional para todos; avisa cuántos quedaron fuera del contorno.
- El archivo se sube al disco `local` (`storage/app/private/gpx-temporal`) y **se borra** después de importar (también si falla).

## Duplicación consciente

El lector GPX existe en JS (formularios, vista previa sin subir el archivo) y en PHP (importación masiva). Si se cambia el formato aceptado, cambiar ambos. Tests: `tests/Unit/GpxTest.php`, `tests/Feature/ImportarPuntosGpxTest.php`, fixture `tests/fixtures/puntos.gpx`.
