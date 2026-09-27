<?php

namespace App\Filament\Resources\Fincas\Pages;

use App\Filament\Resources\Fincas\FincaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinca extends CreateRecord
{
    protected static string $resource = FincaResource::class;

    /** Tras crear la finca, quedarse editándola para empezar a agregar lotes y puntos. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
