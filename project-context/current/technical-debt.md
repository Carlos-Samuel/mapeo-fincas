# Deuda técnica

- Imágenes insertadas dentro del texto enriquecido (`public/uploads/contenido/`) no se borran al borrar el elemento.
- "Dentro de la finca" solo revisa vértices, no cruces de aristas (un lote cóncavo podría salirse sin advertencia).
- Lógica geográfica duplicada en PHP (`Geo.php`) y JS (`mapa-geometria.js`).
- La base no garantiza que las paradas de un recorrido sean puntos de la misma finca (solo el formulario).
- `DemoSeeder` no es idempotente.
- Sin tests de JS en el repositorio.
- Dependencia de CDNs públicos (unpkg) en tiempo de ejecución.
