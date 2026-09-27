# Dominio: tipos y capas

- Tabla `tipos`: `nombre`, `aplica_a`, `icono` (ruta en disco `uploads`), `color`, `orden`.
- El tipo da **ícono** (hover, marcadores, lista) y **color** (polígono/línea/borde del marcador) y define la **capa** del control de capas del mapa público.
- `aplica_a` filtra qué tipos ofrece cada formulario: scopes `Tipo::paraLotes|paraPuntos|paraRutas` (ver valores en `domains/rutas-y-recorridos.md`).
- Recorridos no usan tipos.
- Solo aparecen en el control de capas los tipos usados en esa finca (`FincaController::datos`). Elementos sin tipo → capa "Sin tipo".
- El control de capas se colapsa en pantallas < 900 px o con más de 8 capas.
- Íconos aceptados: SVG, PNG, WebP, JPEG ≤ 1 MB (`TipoForm`). Se muestran a ~22–48 px.
- Al cambiar o borrar el ícono se borra el archivo anterior (`Tipo::booted`).
- Borrar un tipo deja sus lotes/puntos/rutas sin tipo (`nullOnDelete`).
