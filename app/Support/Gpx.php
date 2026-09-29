<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;

/**
 * Lector mínimo de GPX 1.0 / 1.1 (waypoints, recorridos y rutas).
 * Equivalente en el navegador: GeoHerramientas.leerGpx (public/js/geo-herramientas.js).
 * Coordenadas devueltas como [longitud, latitud].
 */
class Gpx
{
    /**
     * @return array{
     *   waypoints: list<array{nombre: string, descripcion: string, lng: float, lat: float}>,
     *   tracks: list<array{nombre: string, puntos: list<array{0: float, 1: float}>}>,
     *   rutas: list<array{nombre: string, puntos: list<array{0: float, 1: float}>}>
     * }
     */
    public static function leer(string $xml): array
    {
        $doc = new DOMDocument;
        $anterior = libxml_use_internal_errors(true);
        // LIBXML_NONET: nunca descargar nada; sin LIBXML_NOENT no se expanden entidades externas.
        $ok = trim($xml) !== '' && $doc->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($anterior);

        if (! $ok || $doc->documentElement?->localName !== 'gpx') {
            throw new InvalidArgumentException('El archivo no es un GPX válido.');
        }

        $waypoints = [];
        foreach ($doc->getElementsByTagNameNS('*', 'wpt') as $i => $wpt) {
            if ($p = self::punto($wpt)) {
                $waypoints[] = [
                    'nombre' => self::hijo($wpt, 'name') ?: 'Punto '.($i + 1),
                    'descripcion' => self::hijo($wpt, 'desc') ?: self::hijo($wpt, 'cmt'),
                    'lng' => $p[0],
                    'lat' => $p[1],
                ];
            }
        }

        $lineas = function (string $contenedor, string $etiquetaPunto, string $prefijo) use ($doc): array {
            $res = [];
            foreach ($doc->getElementsByTagNameNS('*', $contenedor) as $i => $el) {
                $puntos = [];
                foreach ($el->getElementsByTagNameNS('*', $etiquetaPunto) as $pt) {
                    if ($p = self::punto($pt)) {
                        $puntos[] = $p;
                    }
                }
                if ($puntos) {
                    $res[] = ['nombre' => self::hijo($el, 'name') ?: $prefijo.' '.($i + 1), 'puntos' => $puntos];
                }
            }

            return $res;
        };

        return [
            'waypoints' => $waypoints,
            'tracks' => $lineas('trk', 'trkpt', 'Recorrido'),
            'rutas' => $lineas('rte', 'rtept', 'Ruta'),
        ];
    }

    /** @return array{0: float, 1: float}|null */
    private static function punto(DOMElement $el): ?array
    {
        $lat = filter_var($el->getAttribute('lat'), FILTER_VALIDATE_FLOAT);
        $lng = filter_var($el->getAttribute('lon'), FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
            return null;
        }

        return [round($lng, 7), round($lat, 7)];
    }

    private static function hijo(DOMElement $el, string $nombre): string
    {
        foreach ($el->childNodes as $hijo) {
            if ($hijo instanceof DOMElement && $hijo->localName === $nombre) {
                return trim($hijo->textContent);
            }
        }

        return '';
    }
}
