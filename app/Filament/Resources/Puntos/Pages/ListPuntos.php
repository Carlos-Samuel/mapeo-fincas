<?php

namespace App\Filament\Resources\Puntos\Pages;

use App\Filament\Resources\Puntos\PuntoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPuntos extends ListRecords
{
    protected static string $resource = PuntoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
