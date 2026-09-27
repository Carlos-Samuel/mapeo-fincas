<?php

namespace App\Filament\Resources\Recorridos\Pages;

use App\Filament\Resources\Recorridos\RecorridoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecorridos extends ListRecords
{
    protected static string $resource = RecorridoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
