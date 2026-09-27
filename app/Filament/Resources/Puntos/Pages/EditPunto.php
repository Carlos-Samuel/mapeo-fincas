<?php

namespace App\Filament\Resources\Puntos\Pages;

use App\Filament\Resources\Concerns\AdvierteSiQuedaFuera;
use App\Filament\Resources\Fincas\FincaResource;
use App\Filament\Resources\Puntos\PuntoResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPunto extends EditRecord
{
    use AdvierteSiQuedaFuera;

    protected static string $resource = PuntoResource::class;

    protected function getHeaderActions(): array
    {
        $registro = $this->getRecord();

        return [
            Action::make('ver')
                ->label('Ver en el mapa')
                ->icon(Heroicon::OutlinedMap)
                ->color('gray')
                ->url(route('fincas.show', [$registro->finca, 'punto' => $registro->id]), shouldOpenInNewTab: true),
            Action::make('finca')
                ->label('Ir a la finca')
                ->color('gray')
                ->url(FincaResource::getUrl('edit', ['record' => $registro->finca_id])),
            DeleteAction::make(),
        ];
    }
}
