<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Punto;
use App\Models\Recorrido;
use App\Models\Ruta;
use Illuminate\View\View;

/** Fragmentos HTML del panel derecho del mapa público. */
class PanelController extends Controller
{
    public function lote(Lote $lote): View
    {
        $lote->load(['tipo', 'imagenes', 'finca']);

        return view('panel.elemento', [
            'elemento' => $lote,
            'clase' => 'lote',
            'etiqueta' => trim('Lote '.($lote->codigo ?? '')),
            'datos' => array_filter([
                'Tipo' => $lote->tipo?->nombre,
                'Área' => $lote->area_ha !== null ? number_format($lote->area_ha, 2, ',', '.').' ha' : null,
            ]),
        ]);
    }

    public function punto(Punto $punto): View
    {
        $punto->load(['tipo', 'imagenes', 'finca.lotes']);
        $lote = $punto->loteQueLoContiene();

        return view('panel.elemento', [
            'elemento' => $punto,
            'clase' => 'punto',
            'etiqueta' => 'Punto de referencia',
            'datos' => array_filter([
                'Tipo' => $punto->tipo?->nombre,
                'Ubicado en' => $lote ? "Lote {$lote->nombre}" : null,
                'Coordenadas' => $punto->latitud !== null
                    ? number_format($punto->latitud, 6, '.', '').', '.number_format($punto->longitud, 6, '.', '')
                    : null,
            ]),
        ]);
    }

    public function ruta(Ruta $ruta): View
    {
        $ruta->load(['tipo', 'imagenes', 'finca']);

        return view('panel.elemento', [
            'elemento' => $ruta,
            'clase' => 'ruta',
            'etiqueta' => 'Ruta',
            'datos' => array_filter([
                'Tipo' => $ruta->tipo?->nombre,
                'Longitud' => $ruta->longitudTexto(),
            ]),
        ]);
    }

    public function recorrido(Recorrido $recorrido): View
    {
        $recorrido->load(['imagenes', 'finca', 'paradas.punto.tipo', 'paradas.punto.imagenes']);

        return view('panel.recorrido', compact('recorrido'));
    }
}
