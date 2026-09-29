<?php

namespace Tests\Unit;

use App\Support\Gpx;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GpxTest extends TestCase
{
    public function test_lee_waypoints_recorridos_y_rutas(): void
    {
        $gpx = Gpx::leer(file_get_contents(__DIR__.'/../fixtures/puntos.gpx'));

        $this->assertCount(3, $gpx['waypoints']);
        $this->assertSame('Pozo 2', $gpx['waypoints'][0]['nombre']);
        $this->assertSame('Nuevo pozo', $gpx['waypoints'][0]['descripcion']);
        $this->assertSame([-74.8835, 4.1525], [$gpx['waypoints'][0]['lng'], $gpx['waypoints'][0]['lat']]);
        $this->assertSame('Borde del lote', $gpx['tracks'][0]['nombre']);
        $this->assertCount(3, $gpx['tracks'][0]['puntos']);
        $this->assertCount(2, $gpx['rutas'][0]['puntos']);
    }

    public function test_acepta_gpx_1_0_sin_nombre(): void
    {
        $xml = '<gpx version="1.0" xmlns="http://www.topografix.com/GPX/1/0"><wpt lat="1" lon="2"/></gpx>';

        $this->assertSame('Punto 1', Gpx::leer($xml)['waypoints'][0]['nombre']);
    }

    public function test_rechaza_lo_que_no_es_gpx(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Gpx::leer('<html><body>hola</body></html>');
    }
}
