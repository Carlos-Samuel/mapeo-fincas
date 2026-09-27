# Dominio: contenido del panel lateral

Cada finca, lote, punto, ruta y recorrido tiene:

- `contenido`: HTML del RichEditor de Filament (títulos h2/h3, listas, tablas, enlaces, citas, imágenes adjuntas).
- Galería: tabla polimórfica `imagenes` (`imageable_type/id`, `ruta`, `descripcion`, `orden`) vía trait `TieneImagenes`.

## Reglas

- Formulario común: `app/Filament/Forms/ContenidoPanel.php` (Repeater con `relationship()` + `orderColumn('orden')`, RichEditor).
- **Siempre renderizar con** `RichContentRenderer::make($html)->fileAttachmentsDisk('uploads')->toHtml()` (sanea el HTML contra XSS). Ya está en `resources/views/panel/_contenido.blade.php`: reutilizar ese partial, no imprimir `{!! $contenido !!}` directo.
- Todos los archivos van al disco `uploads` → `public/uploads/{galeria,tipos,contenido}` (ADR-003).
- Al borrar una imagen o cambiar su archivo, se borra el archivo físico (`Imagen::booted`). Las imágenes insertadas dentro del texto (carpeta `contenido/`) **no** se limpian: pueden quedar huérfanas.
- El panel de la finca (resumen) se renderiza en el servidor dentro de `fincas/show`; los demás se piden por fetch a `/panel/...` y se insertan como HTML.
- Una sola página puede tener **varias galerías** (recorrido: una por parada); el JS de miniaturas actúa sobre la galería del botón pulsado.
