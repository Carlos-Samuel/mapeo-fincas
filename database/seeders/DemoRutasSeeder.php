<?php

namespace Database\Seeders;

use App\Models\Finca;
use App\Models\Tipo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Agrega rutas y un recorrido de ejemplo a "Finca El Ejemplo".
 * Se puede correr sobre una base que ya tiene la finca demo:
 *   php artisan db:seed --class=DemoRutasSeeder
 */
class DemoRutasSeeder extends Seeder
{
    public function run(): void
    {
        $finca = Finca::where('nombre', 'Finca El Ejemplo')->first();

        if (! $finca) {
            $this->command?->warn('No existe "Finca El Ejemplo": corre primero DemoSeeder.');

            return;
        }

        if ($finca->rutas()->exists() || $finca->recorridos()->exists()) {
            $this->command?->info('La finca demo ya tiene rutas o recorridos: no se agrega nada.');

            return;
        }

        $datos = require database_path('data/demo_rutas.php');
        $disco = Storage::disk('uploads');

        $tipos = [];
        foreach ($datos['tipos'] as $clave => [$nombre, $aplicaA, $color, $icono]) {
            $ruta = 'tipos/demo-'.$icono;
            $disco->put($ruta, file_get_contents(database_path("data/demo/iconos/{$icono}")));

            $tipos[$clave] = Tipo::firstOrCreate(
                ['nombre' => $nombre],
                ['aplica_a' => $aplicaA, 'color' => $color, 'icono' => $ruta, 'orden' => Tipo::max('orden') + 1],
            );
        }

        foreach ($datos['rutas'] as $r) {
            $finca->rutas()->create([
                'nombre' => $r['nombre'],
                'tipo_id' => $tipos[$r['tipo']]->id,
                'geometria' => ['type' => 'LineString', 'coordinates' => $r['coordenadas']],
                'contenido' => $r['contenido'],
            ]);
        }

        $puntos = $finca->puntos()->pluck('id', 'nombre');

        foreach ($datos['recorridos'] as $rec) {
            $recorrido = $finca->recorridos()->create([
                'nombre' => $rec['nombre'],
                'color' => $rec['color'],
                'contenido' => $rec['contenido'],
            ]);

            foreach ($rec['paradas'] as $orden => [$nombrePunto, $nota]) {
                if ($puntoId = $puntos[$nombrePunto] ?? null) {
                    $recorrido->paradas()->create(['punto_id' => $puntoId, 'orden' => $orden, 'nota' => $nota]);
                }
            }
        }
    }
}
