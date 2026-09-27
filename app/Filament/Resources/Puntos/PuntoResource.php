<?php

namespace App\Filament\Resources\Puntos;

use App\Filament\Resources\Puntos\Pages\CreatePunto;
use App\Filament\Resources\Puntos\Pages\EditPunto;
use App\Filament\Resources\Puntos\Pages\ListPuntos;
use App\Filament\Resources\Puntos\Schemas\PuntoForm;
use App\Filament\Resources\Puntos\Tables\PuntosTable;
use App\Models\Punto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PuntoResource extends Resource
{
    protected static ?string $model = Punto::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $modelLabel = 'punto';

    protected static ?string $pluralModelLabel = 'puntos';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return PuntoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PuntosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPuntos::route('/'),
            'create' => CreatePunto::route('/create'),
            'edit' => EditPunto::route('/{record}/edit'),
        ];
    }
}
