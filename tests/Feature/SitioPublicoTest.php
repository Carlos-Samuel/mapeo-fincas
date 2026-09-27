<?php

namespace Tests\Feature;

use App\Models\Finca;
use App\Models\Lote;
use App\Models\Punto;
use App\Models\Recorrido;
use App\Models\Ruta;
use Database\Seeders\DemoRutasSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SitioPublicoTest extends TestCase
{
    use RefreshDatabase;

    private Finca $finca;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        $this->seed([DemoSeeder::class, DemoRutasSeeder::class]);
        $this->finca = Finca::firstOrFail();
    }

    public function test_la_portada_lista_las_fincas(): void
    {
        $this->get('/')->assertOk()->assertSee('Finca El Ejemplo')->assertSee('6 lotes');
    }

    public function test_la_pagina_de_la_finca_muestra_mapa_y_resumen(): void
    {
        $this->get("/fincas/{$this->finca->slug}")
            ->assertOk()
            ->assertSee('id="mapa"', false)
            ->assertSee('Maíz Norte')
            ->assertSee('Portón de entrada');
    }

    public function test_los_datos_del_mapa_traen_lotes_puntos_y_tipos(): void
    {
        $this->getJson("/fincas/{$this->finca->slug}/mapa.json")
            ->assertOk()
            ->assertJsonCount(6, 'lotes.features')
            ->assertJsonCount(5, 'puntos.features')
            ->assertJsonCount(13, 'tipos')
            ->assertJsonCount(3, 'rutas.features')
            ->assertJsonPath('rutas.features.0.geometry.type', 'LineString')
            ->assertJsonCount(1, 'recorridos')
            ->assertJsonCount(5, 'recorridos.0.paradas')
            ->assertJsonPath('puntos.features.0.geometry.type', 'Point');
    }

    public function test_el_panel_de_un_lote_muestra_su_contenido(): void
    {
        $lote = Lote::where('codigo', 'L-01')->firstOrFail();

        $this->get("/panel/lotes/{$lote->id}")
            ->assertOk()
            ->assertSee('Maíz Norte')
            ->assertSee('Híbrido amarillo')
            ->assertSee('6,45 ha');
    }

    public function test_el_panel_de_un_punto_dice_en_que_lote_esta(): void
    {
        $pozo = Punto::where('nombre', 'Pozo del cafetal')->firstOrFail();

        $this->get("/panel/puntos/{$pozo->id}")->assertOk()->assertSee('Lote Cafetal');
    }

    public function test_el_panel_de_una_ruta_muestra_su_longitud(): void
    {
        $camino = Ruta::where('nombre', 'Camino interno')->firstOrFail();

        $this->assertEqualsWithDelta(685.7, $camino->longitud_m, 1);
        $this->get("/panel/rutas/{$camino->id}")->assertOk()->assertSee('Camino interno')->assertSee('686 m');
    }

    public function test_el_panel_del_recorrido_trae_las_paradas_en_orden(): void
    {
        $recorrido = Recorrido::firstOrFail();

        $this->get("/panel/recorridos/{$recorrido->id}")
            ->assertOk()
            ->assertSeeInOrder(['Portón de entrada', 'Casa principal', 'Pozo del cafetal', 'Bebedero del potrero', 'Bodega de insumos'])
            ->assertSee('Parada');
    }

    public function test_borrar_un_punto_lo_quita_de_los_recorridos(): void
    {
        Punto::where('nombre', 'Casa principal')->firstOrFail()->delete();

        $this->assertSame(4, Recorrido::firstOrFail()->paradas()->count());
    }

    public function test_el_seeder_de_rutas_no_duplica(): void
    {
        $this->seed(DemoRutasSeeder::class);

        $this->assertSame(3, Ruta::count());
        $this->assertSame(1, Recorrido::count());
    }

    public function test_el_contenido_se_limpia_de_scripts(): void
    {
        $lote = Lote::firstOrFail();
        $lote->update(['contenido' => '<p>Hola</p><script>alert(1)</script>']);

        $this->get("/panel/lotes/{$lote->id}")->assertOk()->assertSee('Hola')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_geometrias_de_referencia_para_el_admin(): void
    {
        $this->getJson("/api/fincas/{$this->finca->id}/geometrias")
            ->assertOk()
            ->assertJsonCount(6, 'lotes')
            ->assertJsonCount(3, 'rutas')
            ->assertJsonPath('finca.geometria.type', 'Polygon');
    }

    public function test_borrar_la_finca_borra_todo_lo_suyo(): void
    {
        $this->finca->delete();

        $this->assertDatabaseCount('lotes', 0);
        $this->assertDatabaseCount('puntos', 0);
        $this->assertDatabaseCount('rutas', 0);
        $this->assertDatabaseCount('recorridos', 0);
        $this->assertDatabaseCount('recorrido_paradas', 0);
        $this->assertDatabaseCount('imagenes', 0);
    }

    public function test_404_para_lo_que_no_existe(): void
    {
        $this->get('/fincas/no-existe')->assertNotFound();
        $this->get('/panel/lotes/999')->assertNotFound();
    }
}
