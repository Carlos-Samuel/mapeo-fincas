# PROJECT — Fincas

## Propósito

Sitio web con **mapas interactivos de fincas** sobre imagen satelital. Cada finca se describe con zonas (lotes),
lugares (puntos), líneas (rutas) y visitas guiadas (recorridos). Todo el contenido se administra sin tocar código.

## Actores

- **Administrador** (hoy una sola persona, el dueño del proyecto): entra a `/admin`, crea y edita todo. Cualquier usuario registrado es administrador.
- **Visitante**: ve el sitio público sin iniciar sesión.

## Capacidades

- Portada con la lista de fincas; una página con mapa por finca (`/fincas/{slug}`).
- Pasar el cursor sobre un elemento muestra el ícono de su tipo; al hacer clic, el **panel derecho** muestra su descripción y galería (no es un modal).
- Capas por tipo que se prenden y apagan. Enlaces directos a un elemento (`?lote=`, `?punto=`, `?ruta=`, `?recorrido=`).
- Recorridos guiados paso a paso (Anterior / Siguiente) sobre puntos de la finca.
- Admin: dibujar contornos, lotes, rutas y ubicar puntos sobre el mapa; texto enriquecido y galería para cada elemento; catálogo de tipos.

## Conceptos y terminología

| Término | Significado |
|---|---|
| Finca | Región grande con contorno (polígono). Contiene todo lo demás. |
| Lote | Zona (polígono) dentro de una finca: maíz, café, potrero… |
| Punto (de referencia) | Lugar puntual: casa, pozo, bodega, portón… |
| Ruta | Línea dibujada: camino, cerca, tubería, canal… Tiene longitud. |
| Recorrido | Visita guiada: secuencia ordenada de **puntos existentes** con una nota por parada. |
| Parada | Un paso de un recorrido (punto + nota + orden). |
| Tipo | Catálogo con nombre, ícono y color. Define la capa del mapa. Se asigna a lotes, puntos y rutas (no a recorridos). |
| Contenido del panel | Texto enriquecido + galería de imágenes de una finca, lote, punto, ruta o recorrido. |
| Contorno | Geometría de la finca; se usa para advertir cuando algo queda por fuera. |

## Reglas funcionales clave

- Lotes, puntos y rutas pertenecen a **una** finca. Si quedan por fuera del contorno, se **advierte pero se guarda**.
- No hay borradores: lo guardado se ve de inmediato en el sitio público.
- Borrar una finca borra todo lo suyo (lotes, puntos, rutas, recorridos, imágenes y archivos).

El detalle de cada regla está en `domains/`.
