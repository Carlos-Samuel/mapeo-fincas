<?php

/*
| Rutas y recorrido de ejemplo para "Finca El Ejemplo" (DemoRutasSeeder).
| Coordenadas GeoJSON: [longitud, latitud].
*/

return [
    'tipos' => [
        // clave => [nombre, aplica_a, color, ícono]
        'camino' => ['Camino', 'ruta', '#8d6e63', 'camino.svg'],
        'tuberia' => ['Tubería / riego', 'ruta', '#1e88e5', 'tuberia.svg'],
        'cerca' => ['Cerca', 'ruta', '#fbc02d', 'cerca.svg'],
    ],

    'rutas' => [
        [
            'nombre' => 'Camino interno', 'tipo' => 'camino',
            'coordenadas' => [[-74.8797, 4.1515], [-74.8803, 4.15195], [-74.8823, 4.1518], [-74.8824, 4.1536], [-74.8840, 4.1538]],
            'contenido' => '<p>Camino destapado del portón a la casa. Transitable en carro en verano; en invierno solo en moto o a caballo.</p>',
        ],
        [
            'nombre' => 'Tubería pozo → bebedero', 'tipo' => 'tuberia',
            'coordenadas' => [[-74.8835, 4.1525], [-74.8822, 4.1518], [-74.8811, 4.1509]],
            'contenido' => '<p>Manguera de 1" enterrada a 40 cm. Llave de paso junto al pozo.</p>',
        ],
        [
            'nombre' => 'Cerca del potrero', 'tipo' => 'cerca',
            'coordenadas' => [[-74.8822, 4.1518], [-74.8803, 4.1520], [-74.8800, 4.1500], [-74.8820, 4.1502]],
            'contenido' => '<p>Alambre de púas, 4 hilos. Revisar el tramo sur después de lluvias.</p>',
        ],
    ],

    'recorridos' => [
        [
            'nombre' => 'Visita guiada',
            'color' => '#e91e63',
            'contenido' => '<p>Recorrido de unos <strong>40 minutos</strong> a pie para conocer la finca. Usa «Siguiente» para avanzar.</p>',
            // [nombre del punto existente, nota]
            'paradas' => [
                ['Portón de entrada', 'Bienvenida. Desde aquí se ve el potrero y el frijolar.'],
                ['Casa principal', 'Presentación del mayordomo y explicación de la finca.'],
                ['Pozo del cafetal', 'De aquí sale el agua para todo el sistema de riego.'],
                ['Bebedero del potrero', 'Final de la tubería. Se muestra la rotación de potreros.'],
                ['Bodega de insumos', 'Cierre de la visita.'],
            ],
        ],
    ],
];
