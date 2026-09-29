<?php

namespace Tests\Feature;

use App\Filament\Resources\Fincas\Pages\EditFinca;
use App\Models\Finca;
use App\Models\Punto;
use App\Models\User;
use App\Support\ImportadorPuntosGpx;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportarPuntosGpxTest extends TestCase
{
    use RefreshDatabase;

    private Finca $finca;

    private string $xml;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        Storage::fake('local');
        $this->seed(DemoSeeder::class);
        $this->finca = Finca::firstOrFail();
        $this->xml = file_get_contents(base_path('tests/fixtures/puntos.gpx'));
    }

    public function test_crea_puntos_omite_repetidos_y_cuenta_los_de_afuera(): void
    {
        $r = ImportadorPuntosGpx::importar($this->finca, $this->xml);

        // "Casa principal" ya existe en la finca demo → se omite; "Bocatoma" queda fuera del contorno.
        $this->assertSame(['creados' => 2, 'omitidos' => 1, 'fuera' => 1], $r);
        $pozo = Punto::where('nombre', 'Pozo 2')->firstOrFail();
        $this->assertSame(4.1525, $pozo->latitud);
        $this->assertStringContainsString('Nuevo pozo', $pozo->contenido);
    }

    public function test_falla_si_no_hay_waypoints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ImportadorPuntosGpx::importar($this->finca, '<gpx xmlns="http://www.topografix.com/GPX/1/1"></gpx>');
    }

    public function test_la_accion_del_admin_importa_y_borra_el_archivo(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditFinca::class, ['record' => $this->finca->getRouteKey()])
            ->callAction('importarPuntosGpx', data: [
                'archivo' => UploadedFile::fake()->createWithContent('puntos.gpx', $this->xml),
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('Se crearon 2 puntos');

        $this->assertDatabaseHas('puntos', ['nombre' => 'Bocatoma', 'finca_id' => $this->finca->id]);
        $this->assertSame([], Storage::disk('local')->allFiles('gpx-temporal'));
    }
}
