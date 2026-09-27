<?php

namespace App\Filament\Resources\Tipos;

use App\Filament\Resources\Tipos\Pages\CreateTipo;
use App\Filament\Resources\Tipos\Pages\EditTipo;
use App\Filament\Resources\Tipos\Pages\ListTipos;
use App\Filament\Resources\Tipos\Schemas\TipoForm;
use App\Filament\Resources\Tipos\Tables\TiposTable;
use App\Models\Tipo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TipoResource extends Resource
{
    protected static ?string $model = Tipo::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $modelLabel = 'tipo';

    protected static ?string $pluralModelLabel = 'tipos';

    protected static ?string $navigationLabel = 'Tipos (capas)';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return TipoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TiposTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTipos::route('/'),
            'create' => CreateTipo::route('/create'),
            'edit' => EditTipo::route('/{record}/edit'),
        ];
    }
}
