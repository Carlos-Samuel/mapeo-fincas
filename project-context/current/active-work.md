# Trabajo en curso

_Actualizado: 2026-09-28_

- **Nuevo (sin desplegar aún):** coordenadas escritas en puntos, GPX en formularios, importación masiva de puntos GPX y capa «Satélite nítido (beta)». Falta: `php artisan test` local, commit/push y `~/deploy.sh`.

- **Producción:** publicado en https://software-cs.com (StackCP). Flujo de actualización en `architecture/despliegue-cpanel.md`.

- **Rutas y recorridos** implementados en código (migración `2026_09_27_000001`). En el Mac del dueño falta:
  1. `php artisan migrate`
  2. `php artisan db:seed --class=DemoRutasSeeder` (opcional, datos demo)
  3. Probar en el admin: crear ruta (dibujar línea), crear recorrido (paradas).
- `php artisan test`: 25/26 en la primera corrida; corregido el 404 de `/admin/fincas/{id}/edit`. Falta volver a correrla.
- Siguiente fase acordada: cargar la **finca real** dibujándola en el admin (antigua "fase 5").
