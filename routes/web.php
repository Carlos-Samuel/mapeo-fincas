<?php

use App\Http\Controllers\FincaController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

// Sitio público
Route::get('/', [FincaController::class, 'index'])->name('fincas.index');
Route::get('/fincas/{finca}', [FincaController::class, 'show'])->name('fincas.show');
Route::get('/fincas/{finca}/mapa.json', [FincaController::class, 'datos'])->name('fincas.datos');

// Contenido del panel derecho (HTML listo para insertar)
Route::get('/panel/lotes/{lote}', [PanelController::class, 'lote'])->name('panel.lote');
Route::get('/panel/puntos/{punto}', [PanelController::class, 'punto'])->name('panel.punto');
Route::get('/panel/rutas/{ruta}', [PanelController::class, 'ruta'])->name('panel.ruta');
Route::get('/panel/recorridos/{recorrido}', [PanelController::class, 'recorrido'])->name('panel.recorrido');

// Geometrías de referencia para el mapa del admin (por id)
Route::get('/api/fincas/{finca:id}/geometrias', [FincaController::class, 'geometrias'])->name('fincas.geometrias');
