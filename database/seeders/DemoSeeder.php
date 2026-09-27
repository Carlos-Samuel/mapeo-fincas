<?php

namespace Database\Seeders;

use App\Models\Finca;
use App\Models\Tipo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/** Carga la "Finca El Ejemplo" con sus tipos, lotes, puntos, textos e imágenes. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $datos = require database_path('data/finca_demo.php');
        $disco = Storage::disk('uploads');

        // Copia un archivo de database/data/demo a public/uploads y devuelve su ruta relativa.
        $copiar = function (string $origen, string $carpeta) use ($disco): string {
            $destino = $carpeta.'/demo-'.basename($origen);
            $disco->put($destino, file_get_contents(database_path("data/demo/{$origen}")));

            return $destino;
        };

        $tipos = [];
        foreach ($datos['tipos'] as $clave => [$nombre, $aplicaA, $color, $icono]) {
            $tipos[$clave] = Tipo::create([
                'nombre' => $nombre,
                'aplica_a' => $aplicaA,
                'color' => $color,
                'icono' => $copiar("iconos/{$icono}", 'tipos'),
                'orden' => count($tipos),
            ]);
        }

        $f = $datos['finca'];
        $finca = Finca::create([
            'nombre' => $f['nombre'],
            'ubicacion' => $f['ubicacion'],
            'geometria' => ['type' => 'Polygon', 'coordinates' => $f['contorno']],
            'contenido' => $f['contenido'],
        ]);

        $agregarImagenes = function ($modelo, array $imagenes) use ($copiar) {
            foreach ($imagenes as $orden => [$archivo, $descripcion]) {
                $modelo->imagenes()->create([
                    'ruta' => $copiar("galeria/{$archivo}", 'galeria'),
                    'descripcion' => $descripcion,
                    'orden' => $orden,
                ]);
            }
        };

        $agregarImagenes($finca, $f['imagenes']);

        foreach ($datos['lotes'] as $l) {
            $lote = $finca->lotes()->create([
                'codigo' => $l['codigo'],
                'nombre' => $l['nombre'],
                'tipo_id' => $tipos[$l['tipo']]->id,
                'geometria' => ['type' => 'Polygon', 'coordinates' => $l['coordenadas']],
                'contenido' => $l['contenido'],
            ]);
            $agregarImagenes($lote, $l['imagenes']);
        }

        foreach ($datos['puntos'] as $p) {
            $finca->puntos()->create([
                'nombre' => $p['nombre'],
                'tipo_id' => $tipos[$p['tipo']]->id,
                'geometria' => ['type' => 'Point', 'coordinates' => $p['coordenadas']],
                'contenido' => $p['contenido'],
            ]);
        }
    }
}
