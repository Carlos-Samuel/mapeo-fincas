<?php

namespace App\Http\Controllers;

use App\Models\Finca;
use App\Models\Lote;
use App\Models\Punto;
use App\Models\Recorrido;
use App\Models\Ruta;
use App\Models\Tipo;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FincaController extends Controller
{
    /** Portada: lista de fincas. */
    public function index(): View
    {
        $fincas = Finca::withCount(['lotes', 'puntos', 'rutas', 'recorridos'])
            ->with(['imagenes' => fn ($q) => $q->limit(1)])
            ->orderBy('nombre')
            ->get();

        return view('fincas.index', compact('fincas'));
    }

    /** Página de una finca con su mapa. */
    public function show(Finca $finca): View
    {
        $finca->load(['imagenes', 'lotes.tipo', 'puntos.tipo', 'rutas.tipo', 'recorridos.paradas']);

        return view('fincas.show', compact('finca'));
    }

    /** Todo lo que el mapa público necesita dibujar. */
    public function datos(Finca $finca): JsonResponse
    {
        $finca->load(['lotes.tipo', 'puntos.tipo', 'rutas.tipo', 'recorridos.paradas']);

        $tipoIds = $finca->lotes->pluck('tipo_id')
            ->merge($finca->puntos->pluck('tipo_id'))
            ->merge($finca->rutas->pluck('tipo_id'))
            ->filter()
            ->unique();

        $tipos = Tipo::whereIn('id', $tipoIds)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'finca' => [
                'id' => $finca->id,
                'nombre' => $finca->nombre,
                'geometria' => $finca->geometria,
            ],
            'tipos' => $tipos->map(fn (Tipo $t) => [
                'id' => $t->id,
                'nombre' => $t->nombre,
                'color' => $t->color,
                'icono' => $t->iconoUrl(),
            ])->values(),
            'lotes' => $this->coleccion($finca->lotes, fn (Lote $l) => [
                'codigo' => $l->codigo,
                'area_ha' => $l->area_ha,
            ]),
            'puntos' => $this->coleccion($finca->puntos),
            'rutas' => $this->coleccion($finca->rutas, fn (Ruta $r) => [
                'longitud_m' => $r->longitud_m,
            ]),
            // Las paradas referencian puntos por id: el mapa toma las coordenadas de ahí.
            'recorridos' => $finca->recorridos->map(fn (Recorrido $r) => [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'color' => $r->color,
                'paradas' => $r->paradas->pluck('punto_id')->values(),
            ])->values(),
        ]);
    }

    /** Geometrías de referencia para el campo de mapa del admin. */
    public function geometrias(Finca $finca): JsonResponse
    {
        $finca->load(['lotes.tipo', 'puntos.tipo', 'rutas.tipo']);
        $color = fn ($m) => $m->tipo?->color ?? '#9e9e9e';

        return response()->json([
            'finca' => ['id' => $finca->id, 'nombre' => $finca->nombre, 'geometria' => $finca->geometria],
            'lotes' => $finca->lotes->map(fn (Lote $l) => [
                'id' => $l->id, 'nombre' => $l->nombre, 'color' => $color($l), 'geometria' => $l->geometria,
            ])->values(),
            'puntos' => $finca->puntos->map(fn (Punto $p) => [
                'id' => $p->id, 'nombre' => $p->nombre, 'color' => $color($p), 'geometria' => $p->geometria,
            ])->values(),
            'rutas' => $finca->rutas->map(fn (Ruta $r) => [
                'id' => $r->id, 'nombre' => $r->nombre, 'color' => $color($r), 'geometria' => $r->geometria,
            ])->values(),
        ]);
    }

    private function coleccion($elementos, ?callable $extra = null): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $elementos->map(fn ($e) => [
                'type' => 'Feature',
                'id' => $e->id,
                'geometry' => $e->geometria,
                'properties' => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'tipo_id' => $e->tipo_id,
                    'tipo' => $e->tipo?->nombre,
                    'color' => $e->tipo?->color ?? '#9e9e9e',
                    'icono' => $e->tipo?->iconoUrl(),
                    ...($extra ? $extra($e) : []),
                ],
            ])->values()->all(),
        ];
    }
}
