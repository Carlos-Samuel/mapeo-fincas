<?php

namespace App\Support;

use App\Models\Finca;

/** Crea puntos de una finca a partir de los waypoints de un GPX. */
class ImportadorPuntosGpx
{
    /**
     * @return array{creados: int, omitidos: int, fuera: int}
     *   omitidos = waypoints cuyo nombre ya existe como punto en la finca.
     *   fuera    = puntos creados que quedaron por fuera del contorno.
     */
    public static function importar(Finca $finca, string $xml, ?int $tipoId = null): array
    {
        $waypoints = Gpx::leer($xml)['waypoints'];

        if ($waypoints === []) {
            throw new \InvalidArgumentException('El GPX no trae waypoints (puntos marcados).');
        }

        $existentes = $finca->puntos()->pluck('nombre')->map(fn ($n) => mb_strtolower(trim($n)))->all();
        $creados = $omitidos = $fuera = 0;

        foreach ($waypoints as $w) {
            $clave = mb_strtolower(trim($w['nombre']));
            if (in_array($clave, $existentes, true)) {
                $omitidos++;

                continue;
            }

            $geometria = ['type' => 'Point', 'coordinates' => [$w['lng'], $w['lat']]];
            $finca->puntos()->create([
                'nombre' => mb_substr($w['nombre'], 0, 150),
                'tipo_id' => $tipoId,
                'geometria' => $geometria,
                'contenido' => $w['descripcion'] !== '' ? '<p>'.e($w['descripcion']).'</p>' : null,
            ]);

            $existentes[] = $clave;
            $creados++;
            if (! Geo::estaDentro($geometria, $finca->geometria)) {
                $fuera++;
            }
        }

        return compact('creados', 'omitidos', 'fuera');
    }
}
