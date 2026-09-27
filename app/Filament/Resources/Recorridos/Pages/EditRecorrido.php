<?php

namespace App\Filament\Resources\Recorridos\Pages;

use App\Filament\Resources\Fincas\FincaResource;
use App\Filament\Resources\Recorridos\RecorridoResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditRecorrido extends EditRecord
{
    protected static string $resource = RecorridoResource::class;

    protected function getHeaderActions(): array
    {
        $registro = $this->getRecord();

        return [
            Action::make('ver')
                ->label('Ver en el mapa')
                ->icon(Heroicon::OutlinedMap)
                ->color('gray')
                ->url(route('fincas.show', [$registro->finca, 'recorrido' => $registro->id]), shouldOpenInNewTab: true),
            Action::make('finca')
                ->label('Ir a la finca')
                ->color('gray')
                ->url(FincaResource::getUrl('edit', ['record' => $registro->finca_id])),
            DeleteAction::make(),
        ];
    }
}
