<?php

namespace App\Support;

/**
 * Cálculos geográficos simples sobre GeoJSON (sin PostGIS).
 * Las coordenadas GeoJSON van como [longitud, latitud].
 */
class Geo
{
    private const RADIO_TIERRA_M = 6378137;

    /** Área aproximada en hectáreas de un Polygon o MultiPolygon (error < 0,5 % a escala de finca). */
    public static function areaHectareas(?array $geometria): ?float
    {
        $poligonos = self::poligonos($geometria);
        if ($poligonos === []) {
            return null;
        }

        $total = 0.0;
        foreach ($poligonos as $anillos) {
            foreach ($anillos as $i => $anillo) {
                $area = self::areaAnillo($anillo);
                $total += $i === 0 ? $area : -$area; // anillos interiores = huecos
            }
        }

        return round($total / 10000, 2);
    }

    /** Longitud en metros de un LineString (fórmula de Haversine). */
    public static function longitudMetros(?array $geometria): ?float
    {
        if (($geometria['type'] ?? null) !== 'LineString') {
            return null;
        }

        $total = 0.0;
        $puntos = $geometria['coordinates'];
        for ($i = 0; $i < count($puntos) - 1; $i++) {
            [$lng1, $lat1] = $puntos[$i];
            [$lng2, $lat2] = $puntos[$i + 1];
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);
            $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
            $total += 2 * self::RADIO_TIERRA_M * asin(min(1, sqrt($a)));
        }

        return round($total, 1);
    }

    /** ¿El punto [lng, lat] está dentro del Polygon/MultiPolygon? */
    public static function puntoDentro(array $punto, ?array $geometria): bool
    {
        foreach (self::poligonos($geometria) as $anillos) {
            if (! self::enAnillo($punto, $anillos[0])) {
                continue;
            }
            $enHueco = false;
            foreach (array_slice($anillos, 1) as $hueco) {
                if (self::enAnillo($punto, $hueco)) {
                    $enHueco = true;
                    break;
                }
            }
            if (! $enHueco) {
                return true;
            }
        }

        return false;
    }

    /**
     * ¿Toda la geometría (Point o Polygon) queda dentro del contorno?
     * Para polígonos y líneas revisa cada vértice: suficiente para detectar lotes que se salen de la finca.
     */
    public static function estaDentro(?array $geometria, ?array $contorno): bool
    {
        if (! $geometria || ! $contorno) {
            return true; // sin datos no se puede afirmar que esté fuera
        }

        foreach (self::vertices($geometria) as $punto) {
            if (! self::puntoDentro($punto, $contorno)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int, array{0: float, 1: float}> */
    public static function vertices(?array $geometria): array
    {
        return match ($geometria['type'] ?? null) {
            'Point' => [$geometria['coordinates']],
            'LineString' => $geometria['coordinates'],
            'Polygon', 'MultiPolygon' => array_merge(...array_map(
                fn ($anillos) => $anillos[0],
                self::poligonos($geometria),
            )),
            default => [],
        };
    }

    private static function poligonos(?array $geometria): array
    {
        return match ($geometria['type'] ?? null) {
            'Polygon' => [$geometria['coordinates']],
            'MultiPolygon' => $geometria['coordinates'],
            default => [],
        };
    }

    private static function enAnillo(array $punto, array $anillo): bool
    {
        [$x, $y] = $punto;
        $dentro = false;
        $n = count($anillo);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            [$xi, $yi] = $anillo[$i];
            [$xj, $yj] = $anillo[$j];
            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi)) {
                $dentro = ! $dentro;
            }
        }

        return $dentro;
    }

    private static function areaAnillo(array $puntos): float
    {
        $n = count($puntos);
        if ($n < 3) {
            return 0.0;
        }

        $suma = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            [$lng1, $lat1] = $puntos[$i];
            [$lng2, $lat2] = $puntos[$i + 1];
            $suma += deg2rad($lng2 - $lng1) * (2 + sin(deg2rad($lat1)) + sin(deg2rad($lat2)));
        }

        return abs($suma * self::RADIO_TIERRA_M * self::RADIO_TIERRA_M / 2);
    }
}
