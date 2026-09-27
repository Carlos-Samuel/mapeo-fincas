<?php

namespace App\Filament\Resources\Tipos\Schemas;

use App\Models\Tipo;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TipoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tipo')
                ->description('Los tipos dan el ícono y el color a lotes, puntos y rutas, y son las capas que se pueden prender y apagar en el mapa.')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(100),
                    Select::make('aplica_a')
                        ->label('Se usa en')
                        ->options(Tipo::APLICA_A)
                        ->default('ambos')
                        ->required(),
                    ColorPicker::make('color')
                        ->helperText('Color del polígono o de la línea en el mapa.')
                        ->default('#4caf50')
                        ->required(),
                    TextInput::make('orden')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Orden en listas y en el control de capas.'),
                    FileUpload::make('icono')
                        ->label('Ícono')
                        ->helperText('SVG o PNG cuadrado, idealmente con fondo transparente (se muestra a ~36 px).')
                        ->disk('uploads')
                        ->directory('tipos')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp', 'image/jpeg'])
                        ->maxSize(1024)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
