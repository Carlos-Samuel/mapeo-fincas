<?php

namespace App\Filament\Resources\Rutas\Pages;

use App\Filament\Resources\Concerns\AdvierteSiQuedaFuera;
use App\Filament\Resources\Rutas\RutaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRuta extends CreateRecord
{
    use AdvierteSiQuedaFuera;

    protected static string $resource = RutaResource::class;
}
