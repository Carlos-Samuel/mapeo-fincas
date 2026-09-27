<?php

namespace App\Filament\Resources\Tipos\Pages;

use App\Filament\Resources\Tipos\TipoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipo extends EditRecord
{
    protected static string $resource = TipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
