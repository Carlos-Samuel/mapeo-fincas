# Fincas

Mapas interactivos de fincas. Cada finca tiene **lotes** (polígonos), **puntos de referencia** (casa, pozo…),
**rutas** (líneas: caminos, cercas, tuberías) y **recorridos** (visitas guiadas por paradas).
Todo se crea desde el administrador: se dibuja sobre la imagen satelital y se le agrega texto e imágenes.

- **Sitio público** (`/`): lista de fincas y una página con el mapa por cada finca. Al pasar el cursor aparece
  el ícono del tipo, al hacer clic el panel derecho muestra la descripción y la galería. Las capas se prenden y apagan por tipo.
- **Administrador** (`/admin`, Filament): fincas, lotes, puntos, rutas, recorridos y tipos (catálogo de íconos y colores).

> Memoria técnica del proyecto para agentes de IA: `project-context/` (empezar por `project-context/AGENTS.md`).

Stack: Laravel 13 · Filament 4 · MySQL 8 · Leaflet + Leaflet-Geoman (por CDN). **No usa Node ni npm.**

---

## Instalación local (Mac)

Necesitas **PHP 8.3+** con las extensiones `pdo_mysql` e `intl`, **Composer** y **Docker Desktop**.
Si usas Laravel Herd, ya trae todo eso.

```bash
cp .env.example .env              # luego cambia DB_PASSWORD y DB_ROOT_PASSWORD
docker compose up -d              # MySQL + phpMyAdmin
composer install                  # también publica los archivos de Filament en public/
php artisan key:generate
php artisan migrate --seed        # crea las tablas y la finca demo
php artisan make:filament-user    # crea tu usuario administrador
php artisan serve
```

| Qué | Dónde |
|---|---|
| Sitio público | http://127.0.0.1:8000 |
| Administrador | http://127.0.0.1:8000/admin |
| phpMyAdmin | http://localhost:8080 (usuario y clave: `DB_USERNAME` / `DB_PASSWORD`) |

> Si el puerto 3306 u 8080 ya está ocupado en tu Mac, cambia `DB_PORT` o `PHPMYADMIN_PORT` en `.env`.

Comandos útiles:

```bash
docker compose stop                  # apaga la base (los datos se conservan)
php artisan migrate:fresh --seed     # reinicia la base con la finca demo (¡borra todo!)
php artisan db:seed --class=DemoRutasSeeder   # agrega rutas y recorrido demo a una base ya sembrada
php artisan test                     # pruebas automáticas (usan SQLite en memoria, no tocan MySQL)
```

---

## Cómo se usa

1. **Tipos**: crea los tipos que necesites (Maíz, Café, Casa, Pozo…) con su ícono (SVG o PNG) y color.
   Cada tipo dice si se usa en lotes, en puntos o en ambos. En el mapa público cada tipo es una capa.
2. **Finca**: nombre, ubicación y contorno. Usa el buscador del mapa (lugar o coordenadas «4.15, -74.88»)
   y dibuja con las herramientas de la izquierda. Al guardar quedas en la edición de la finca, con los botones
   **Agregar lote** y **Agregar punto**.
3. **Lotes y puntos**: elige la finca y dibuja. El contorno y los demás lotes salen como guía, y los bordes se
   "imantan" para que los lotes vecinos queden pegados. Si algo queda por fuera de la finca, te avisa, pero guarda igual.
4. **Rutas**: igual que un lote, pero se dibuja una línea («Dibujar línea»; clic en el último vértice para terminar). Calcula la longitud.
5. **Recorridos**: elige la finca y agrega paradas (puntos existentes) en orden, con una nota por parada.
   En el mapa público se siguen con «Anterior / Siguiente».
6. **Contenido del panel lateral** (en finca, lote y punto): galería de imágenes (arrastrar para ordenar)
   y descripción con títulos, listas, tablas, enlaces e imágenes.

El área se calcula sola. Los puntos muestran en qué lote están.

---

## Estructura

| Qué | Dónde |
|---|---|
| Modelos y relaciones | `app/Models/` (`Finca`, `Lote`, `Punto`, `Tipo`, `Imagen`) |
| Cálculos geográficos (área, dentro/fuera) | `app/Support/Geo.php` |
| Admin (formularios y tablas) | `app/Filament/Resources/` |
| Campo de mapa del admin | `app/Filament/Forms/Components/MapaGeometria.php` + `public/js/admin/mapa-geometria.js` |
| Sitio público | `app/Http/Controllers/`, `resources/views/fincas/`, `resources/views/panel/` |
| JS/CSS del mapa público | `public/js/mapa.js`, `public/css/sitio.css` (se editan y se recarga, sin compilar) |
| Datos de la finca demo | `database/data/finca_demo.php` + `database/data/demo/` |
| Imágenes subidas | `public/uploads/` (no usa `storage:link`) |

Geometrías: se guardan como GeoJSON en columnas `json` (`[longitud, latitud]`). Funciona igual en MySQL y MariaDB.

---

## Publicar en cPanel

1. **En tu Mac**, prepara las dependencias sin las de desarrollo:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. **Base de datos**: en cPanel → *Bases de datos MySQL*, crea la base y el usuario, y asígnale todos los privilegios.
3. **PHP**: en *Select PHP Version* / *MultiPHP Manager* elige **8.3 o superior** y activa `intl`, `pdo_mysql`, `fileinfo` y `mbstring`.
4. **Sube el proyecto** completo (incluida `vendor/`, sin `node_modules` ni `.env`) a una carpeta **fuera** de
   `public_html`, por ejemplo `~/fincas`.
5. **Carpeta pública**: lo ideal es un subdominio (ej. `mapa.tudominio.com`) cuya *raíz de documentos* sea `~/fincas/public`.
   Si tiene que ser el dominio principal y cPanel no deja cambiar la raíz, pregunta antes de mover archivos: hay que ajustar
   `public_path` para que las imágenes subidas queden en el lugar correcto.
6. **`.env` en el servidor**: copia `.env.example` y ajusta:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://mapa.tudominio.com
   DB_HOST=localhost
   DB_DATABASE=...   DB_USERNAME=...   DB_PASSWORD=...   (los de cPanel)
   ```
7. **Con Terminal de cPanel** (si la tienes):
   ```bash
   php artisan key:generate
   php artisan migrate --force          # sin --seed para no cargar la finca demo
   php artisan make:filament-user
   php artisan optimize && php artisan filament:optimize
   ```
   Sin Terminal: exporta la base desde phpMyAdmin de tu Mac e impórtala en el de cPanel, y copia `APP_KEY` de tu `.env` local.
8. Permisos de escritura en `storage/`, `bootstrap/cache/` y `public/uploads/`.
