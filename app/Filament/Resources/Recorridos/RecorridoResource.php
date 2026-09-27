<?php

namespace App\Filament\Resources\Recorridos;

use App\Filament\Resources\Recorridos\Pages\CreateRecorrido;
use App\Filament\Resources\Recorridos\Pages\EditRecorrido;
use App\Filament\Resources\Recorridos\Pages\ListRecorridos;
use App\Filament\Resources\Recorridos\Schemas\RecorridoForm;
use App\Filament\Resources\Recorridos\Tables\RecorridosTable;
use App\Models\Recorrido;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RecorridoResource extends Resource
{
    protected static ?string $model = Recorrido::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $modelLabel = 'recorrido';

    protected static ?string $pluralModelLabel = 'recorridos';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return RecorridoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecorridosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecorridos::route('/'),
            'create' => CreateRecorrido::route('/create'),
            'edit' => EditRecorrido::route('/{record}/edit'),
        ];
    }
}
