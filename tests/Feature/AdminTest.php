<?php

namespace Tests\Feature;

use App\Filament\Resources\Lotes\Pages\CreateLote;
use App\Filament\Resources\Puntos\Pages\CreatePunto;
use App\Filament\Resources\Recorridos\Pages\CreateRecorrido;
use App\Filament\Resources\Rutas\Pages\CreateRuta;
use App\Models\Recorrido;
use App\Models\Ruta;
use App\Models\Finca;
use App\Models\Lote;
use App\Models\User;
use Database\Seeders\DemoRutasSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private Finca $finca;

    /** @var callable Deshace Repeater::fake() (claves numéricas en vez de UUID en los tests). */
    private $deshacerRepeaterFake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deshacerRepeaterFake = Repeater::fake();
        Storage::fake('uploads');
        $this->seed([DemoSeeder::class, DemoRutasSeeder::class]);
        $this->finca = Finca::firstOrFail();
        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        ($this->deshacerRepeaterFake)();
        parent::tearDown();
    }

    public function test_sin_login_redirige_al_login(): void
    {
        auth()->logout();
        $this->get('/admin/fincas')->assertRedirect('/admin/login');
    }

    public function test_las_pantallas_del_admin_cargan(): void
    {
        foreach (['/admin', '/admin/fincas', '/admin/lotes', '/admin/puntos', '/admin/rutas', '/admin/recorridos', '/admin/tipos',
            '/admin/lotes/create', '/admin/puntos/create', '/admin/rutas/create', '/admin/recorridos/create',
            "/admin/fincas/{$this->finca->id}/edit"] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_crea_un_lote_y_calcula_su_area(): void
    {
        Livewire::test(CreateLote::class)
            ->fillForm([
                'finca_id' => $this->finca->id,
                'nombre' => 'Lote nuevo',
                'geometria' => ['type' => 'Polygon', 'coordinates' => [[[-74.8870, 4.1520], [-74.8860, 4.1520], [-74.8860, 4.1510], [-74.8870, 4.1520]]]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $lote = Lote::where('nombre', 'Lote nuevo')->firstOrFail();
        $this->assertGreaterThan(0, $lote->area_ha);
    }

    public function test_el_lote_requiere_dibujo(): void
    {
        Livewire::test(CreateLote::class)
            ->fillForm(['finca_id' => $this->finca->id, 'nombre' => 'Sin forma', 'geometria' => null])
            ->call('create')
            ->assertHasFormErrors(['geometria' => 'required']);
    }

    public function test_advierte_pero_guarda_un_punto_fuera_de_la_finca(): void
    {
        Livewire::test(CreatePunto::class)
            ->fillForm([
                'finca_id' => $this->finca->id,
                'nombre' => 'Bocatoma',
                'geometria' => ['type' => 'Point', 'coordinates' => [-74.8700, 4.1600]],
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Guardado, pero queda por fuera de la finca');

        $this->assertDatabaseHas('puntos', ['nombre' => 'Bocatoma', 'latitud' => 4.16]);
    }

    public function test_crea_una_ruta_y_calcula_su_longitud(): void
    {
        Livewire::test(CreateRuta::class)
            ->fillForm([
                'finca_id' => $this->finca->id,
                'nombre' => 'Camino nuevo',
                'geometria' => ['type' => 'LineString', 'coordinates' => [[-74.8870, 4.1520], [-74.8870, 4.1510]]],
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotNotified('Guardado, pero queda por fuera de la finca');

        $this->assertEqualsWithDelta(111.3, Ruta::where('nombre', 'Camino nuevo')->firstOrFail()->longitud_m, 0.5);
    }

    public function test_advierte_si_la_ruta_se_sale(): void
    {
        Livewire::test(CreateRuta::class)
            ->fillForm([
                'finca_id' => $this->finca->id,
                'nombre' => 'Vía al pueblo',
                'geometria' => ['type' => 'LineString', 'coordinates' => [[-74.8870, 4.1520], [-74.8700, 4.1600]]],
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Guardado, pero queda por fuera de la finca');
    }

    public function test_crea_un_recorrido_con_paradas_en_orden(): void
    {
        $puntos = $this->finca->puntos()->orderBy('id')->pluck('id');

        Livewire::test(CreateRecorrido::class)
            ->fillForm([
                'finca_id' => $this->finca->id,
                'nombre' => 'Recorrido corto',
                'color' => '#ff0000',
                'paradas' => [
                    ['punto_id' => $puntos[2], 'nota' => 'primera'],
                    ['punto_id' => $puntos[0], 'nota' => 'segunda'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $paradas = Recorrido::where('nombre', 'Recorrido corto')->firstOrFail()->paradas;
        $this->assertSame([$puntos[2], $puntos[0]], $paradas->pluck('punto_id')->all());
    }

    public function test_el_recorrido_necesita_dos_paradas(): void
    {
        Livewire::test(CreateRecorrido::class)
            ->fillForm([
                'finca_id' => $this->finca->id,
                'nombre' => 'Solo una',
                'color' => '#ff0000',
                'paradas' => [['punto_id' => $this->finca->puntos()->value('id')]],
            ])
            ->call('create')
            ->assertHasFormErrors(['paradas']);
    }
}
