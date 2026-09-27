<?php

/*
|--------------------------------------------------------------------------
| Finca demo
|--------------------------------------------------------------------------
| Datos de ejemplo que carga DemoSeeder (php artisan migrate:fresh --seed).
| Todo esto se puede editar o borrar luego desde el admin.
| Coordenadas GeoJSON: [longitud, latitud].
*/

$tabla = fn (array $filas) => '<table><tbody>'.implode('', array_map(
    fn ($k, $v) => "<tr><th><p>{$k}</p></th><td><p>{$v}</p></td></tr>",
    array_keys($filas),
    $filas,
)).'</tbody></table>';

return [

    'tipos' => [
        // clave => [nombre, aplica_a, color, archivo de ícono]
        'maiz' => ['Maíz', 'lote', '#f2c14e', 'maiz.svg'],
        'cafe' => ['Café', 'lote', '#a0522d', 'cafe.svg'],
        'platano' => ['Plátano', 'lote', '#7cb342', 'platano.svg'],
        'frijol' => ['Fríjol', 'lote', '#c0392b', 'frijol.svg'],
        'pasto' => ['Pasto', 'lote', '#2e9e6b', 'pasto.svg'],
        'casa' => ['Casa', 'punto', '#e65100', 'casa.svg'],
        'pozo' => ['Pozo / agua', 'punto', '#1e88e5', 'pozo.svg'],
        'bodega' => ['Bodega', 'punto', '#546e7a', 'bodega.svg'],
        'bebedero' => ['Bebedero', 'punto', '#00acc1', 'bebedero.svg'],
        'entrada' => ['Entrada', 'punto', '#8e24aa', 'entrada.svg'],
    ],

    'finca' => [
        'nombre' => 'Finca El Ejemplo',
        'ubicacion' => 'Espinal, Tolima (ubicación de muestra)',
        'contorno' => [[
            [-74.8886, 4.1540], [-74.8796, 4.1541], [-74.8793, 4.1494], [-74.8885, 4.1492], [-74.8886, 4.1540],
        ]],
        'contenido' => '<p>Finca de demostración con <strong>seis lotes</strong> y <strong>cinco puntos de referencia</strong>. '
            .'Pasa el cursor sobre el mapa para ver qué hay en cada zona y haz clic para ver su detalle.</p>'
            .'<h3>Cómo está organizada</h3><ul>'
            .'<li><p>Franja norte: maíz, café y fríjol.</p></li>'
            .'<li><p>Franja sur: maíz, plátano y el potrero.</p></li>'
            .'<li><p>El agua sale del pozo del cafetal y llega por manguera al bebedero del potrero.</p></li></ul>'
            .'<blockquote><p>Todo este texto se edita desde el administrador, en la finca → «Contenido del panel lateral».</p></blockquote>',
        'imagenes' => [
            ['maiz-1.svg', 'Vista general'],
        ],
    ],

    'lotes' => [
        [
            'codigo' => 'L-01', 'nombre' => 'Maíz Norte', 'tipo' => 'maiz',
            'coordenadas' => [[[-74.8880, 4.1530], [-74.8850, 4.1532], [-74.8848, 4.1515], [-74.8879, 4.1513], [-74.8880, 4.1530]]],
            'contenido' => '<p>Maíz amarillo híbrido para venta a la cooperativa.</p><h3>Siembra actual</h3>'
                .$tabla([
                    'Variedad' => 'Híbrido amarillo (ej. DK-7088)',
                    'Fecha de siembra' => '10 de julio de 2026',
                    'Cosecha estimada' => '5 de diciembre de 2026',
                    'Etapa' => 'Vegetativo (V10)',
                    'Densidad' => '62.500 plantas/ha',
                    'Rendimiento esperado' => '7–8 t/ha',
                ])
                .'<h3>Labores recientes</h3><ul><li><p>20 ago: segunda fertilización nitrogenada.</p></li><li><p>Riego por gravedad cada 8 días.</p></li></ul>',
            'imagenes' => [['maiz-1.svg', 'Vista general del lote'], ['maiz-2.svg', 'Detalle de la mazorca']],
        ],
        [
            'codigo' => 'L-02', 'nombre' => 'Maíz Sur', 'tipo' => 'maiz',
            'coordenadas' => [[[-74.8879, 4.1513], [-74.8848, 4.1515], [-74.8847, 4.1500], [-74.8878, 4.1498], [-74.8879, 4.1513]]],
            'contenido' => '<p>Maíz blanco para consumo de la finca (arepa y mazamorra).</p>'
                .$tabla([
                    'Variedad' => 'ICA V-305',
                    'Fecha de siembra' => '1 de junio de 2026',
                    'Cosecha estimada' => '20 de octubre de 2026',
                    'Etapa' => 'Llenado de grano',
                ])
                .'<p><strong>Ojo:</strong> monitorear gusano cogollero en los bordes.</p>',
            'imagenes' => [['maiz-2.svg', 'Mazorcas en llenado']],
        ],
        [
            'codigo' => 'L-03', 'nombre' => 'Cafetal', 'tipo' => 'cafe',
            'coordenadas' => [[[-74.8850, 4.1532], [-74.8825, 4.1535], [-74.8822, 4.1518], [-74.8848, 4.1515], [-74.8850, 4.1532]]],
            'contenido' => $tabla([
                'Variedad' => 'Castillo',
                'Sembrado' => 'Abril de 2023',
                'Densidad' => '5.000 árboles/ha',
                'Próxima cosecha' => 'Octubre de 2026',
            ]).'<p>Sombrío transitorio con plátano en los bordes. El pozo principal está dentro de este lote.</p>',
            'imagenes' => [['cafe-1.svg', 'Cerezas maduras']],
        ],
        [
            'codigo' => 'L-04', 'nombre' => 'Platanera', 'tipo' => 'platano',
            'coordenadas' => [[[-74.8848, 4.1515], [-74.8822, 4.1518], [-74.8820, 4.1502], [-74.8847, 4.1500], [-74.8848, 4.1515]]],
            'contenido' => $tabla(['Variedad' => 'Hartón', 'Sembrado' => 'Noviembre de 2025', 'Primer racimo' => 'Noviembre de 2026']),
            'imagenes' => [['platano-1.svg', 'Racimo en desarrollo']],
        ],
        [
            'codigo' => 'L-05', 'nombre' => 'Frijolar', 'tipo' => 'frijol',
            'coordenadas' => [[[-74.8825, 4.1535], [-74.8805, 4.1536], [-74.8803, 4.1520], [-74.8822, 4.1518], [-74.8825, 4.1535]]],
            'contenido' => '<p>Fríjol cargamanto rojo en rotación después del maíz del semestre anterior.</p>',
            'imagenes' => [],
        ],
        [
            'codigo' => 'L-06', 'nombre' => 'Potrero Bajo', 'tipo' => 'pasto',
            'coordenadas' => [[[-74.8822, 4.1518], [-74.8803, 4.1520], [-74.8800, 4.1500], [-74.8820, 4.1502], [-74.8822, 4.1518]]],
            'contenido' => '<p>Brachiaria en descanso. <strong>Entra el ganado el 5 de octubre.</strong></p>',
            'imagenes' => [],
        ],
    ],

    'puntos' => [
        ['nombre' => 'Casa principal', 'tipo' => 'casa', 'coordenadas' => [-74.8840, 4.1538],
            'contenido' => '<p>Vivienda del mayordomo. Aquí se guardan las llaves de la bodega.</p>'],
        ['nombre' => 'Pozo del cafetal', 'tipo' => 'pozo', 'coordenadas' => [-74.8835, 4.1525],
            'contenido' => '<p>Pozo profundo con bomba sumergible.</p>'.$tabla(['Profundidad' => '32 m', 'Caudal' => '1,5 L/s'])],
        ['nombre' => 'Bodega de insumos', 'tipo' => 'bodega', 'coordenadas' => [-74.8882, 4.1495],
            'contenido' => '<p>Fertilizantes y herramienta menor.</p>'],
        ['nombre' => 'Bebedero del potrero', 'tipo' => 'bebedero', 'coordenadas' => [-74.8811, 4.1509],
            'contenido' => '<p>Se alimenta del pozo del cafetal por manguera de 1".</p>'],
        ['nombre' => 'Portón de entrada', 'tipo' => 'entrada', 'coordenadas' => [-74.8797, 4.1515],
            'contenido' => '<p>Acceso desde la vía veredal.</p>'],
    ],
];
