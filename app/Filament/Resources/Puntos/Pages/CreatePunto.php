<?php

namespace App\Filament\Resources\Puntos\Pages;

use App\Filament\Resources\Concerns\AdvierteSiQuedaFuera;
use App\Filament\Resources\Puntos\PuntoResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePunto extends CreateRecord
{
    use AdvierteSiQuedaFuera;

    protected static string $resource = PuntoResource::class;
}
