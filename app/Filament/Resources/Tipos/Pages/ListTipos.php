<?php

namespace App\Filament\Resources\Tipos\Pages;

use App\Filament\Resources\Tipos\TipoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipos extends ListRecords
{
    protected static string $resource = TipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
