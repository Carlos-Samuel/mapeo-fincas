<?php

namespace App\Filament\Resources\Lotes\Pages;

use App\Filament\Resources\Concerns\AdvierteSiQuedaFuera;
use App\Filament\Resources\Lotes\LoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLote extends CreateRecord
{
    use AdvierteSiQuedaFuera;

    protected static string $resource = LoteResource::class;
}
