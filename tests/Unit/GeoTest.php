<?php

namespace Tests\Unit;

use App\Support\Geo;
use PHPUnit\Framework\TestCase;

class GeoTest extends TestCase
{
    private array $cuadro = ['type' => 'Polygon', 'coordinates' => [[[0, 0], [0.01, 0], [0.01, 0.01], [0, 0.01], [0, 0]]]];

    public function test_calcula_el_area_en_hectareas(): void
    {
        // ~1,11 km × 1,11 km en el ecuador ≈ 123,6 ha
        $this->assertEqualsWithDelta(123.6, Geo::areaHectareas($this->cuadro), 0.5);
    }

    public function test_sin_geometria_no_hay_area(): void
    {
        $this->assertNull(Geo::areaHectareas(null));
    }

    public function test_detecta_si_un_punto_esta_dentro(): void
    {
        $this->assertTrue(Geo::estaDentro(['type' => 'Point', 'coordinates' => [0.005, 0.005]], $this->cuadro));
        $this->assertFalse(Geo::estaDentro(['type' => 'Point', 'coordinates' => [0.02, 0.005]], $this->cuadro));
    }

    public function test_detecta_si_un_poligono_se_sale(): void
    {
        $dentro = ['type' => 'Polygon', 'coordinates' => [[[0.001, 0.001], [0.002, 0.001], [0.002, 0.002], [0.001, 0.001]]]];
        $fuera = ['type' => 'Polygon', 'coordinates' => [[[0.001, 0.001], [0.02, 0.001], [0.002, 0.002], [0.001, 0.001]]]];

        $this->assertTrue(Geo::estaDentro($dentro, $this->cuadro));
        $this->assertFalse(Geo::estaDentro($fuera, $this->cuadro));
    }
}
