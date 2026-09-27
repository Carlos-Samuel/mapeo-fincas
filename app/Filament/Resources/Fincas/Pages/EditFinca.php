<?php

namespace App\Filament\Resources\Fincas\Pages;

use App\Filament\Resources\Fincas\FincaResource;
use App\Filament\Resources\Lotes\LoteResource;
use App\Filament\Resources\Puntos\PuntoResource;
use App\Filament\Resources\Recorridos\RecorridoResource;
use App\Filament\Resources\Rutas\RutaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditFinca extends EditRecord
{
    protected static string $resource = FincaResource::class;

    protected function getHeaderActions(): array
    {
        $finca = $this->getRecord();

        return [
            Action::make('agregarLote')
                ->label('Agregar lote')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(LoteResource::getUrl('create', ['finca_id' => $finca->id])),
            Action::make('agregarPunto')
                ->label('Agregar punto')
                ->icon(Heroicon::OutlinedMapPin)
                ->color('gray')
                ->url(PuntoResource::getUrl('create', ['finca_id' => $finca->id])),
            Action::make('agregarRuta')
                ->label('Agregar ruta')
                ->icon(Heroicon::OutlinedArrowTrendingUp)
                ->color('gray')
                ->url(RutaResource::getUrl('create', ['finca_id' => $finca->id])),
            ActionGroup::make([
                Action::make('agregarRecorrido')
                    ->label('Agregar recorrido')
                    ->icon(Heroicon::OutlinedFlag)
                    ->url(RecorridoResource::getUrl('create', ['finca_id' => $finca->id])),
                Action::make('ver')
                    ->label('Ver mapa público')
                    ->icon(Heroicon::OutlinedMap)
                    ->url(route('fincas.show', $finca), shouldOpenInNewTab: true),
                Action::make('verLotes')
                    ->label('Ver lotes de esta finca')
                    ->url(LoteResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                Action::make('verPuntos')
                    ->label('Ver puntos de esta finca')
                    ->url(PuntoResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                Action::make('verRutas')
                    ->label('Ver rutas de esta finca')
                    ->url(RutaResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                Action::make('verRecorridos')
                    ->label('Ver recorridos de esta finca')
                    ->url(RecorridoResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                DeleteAction::make()
                    ->modalDescription('Se borrarán también todos sus lotes, puntos, rutas, recorridos e imágenes. Esta acción no se puede deshacer.'),
            ]),
        ];
    }
}
