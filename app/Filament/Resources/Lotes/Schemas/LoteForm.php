<?php

namespace App\Filament\Resources\Lotes\Schemas;

use App\Filament\Forms\Components\MapaGeometria;
use App\Filament\Forms\ContenidoPanel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class LoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del lote')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('finca_id')
                        ->label('Finca')
                        ->relationship('finca', 'nombre')
                        ->default(fn (): ?int => request()->integer('finca_id') ?: null)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),
                    Select::make('tipo_id')
                        ->label('Tipo')
                        ->helperText('Da el ícono, el color y la capa en el mapa.')
                        ->relationship('tipo', 'nombre', modifyQueryUsing: fn (Builder $query) => $query->paraLotes())
                        ->searchable()
                        ->preload(),
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(150),
                    TextInput::make('codigo')
                        ->label('Código')
                        ->placeholder('L-01')
                        ->maxLength(30),
                ]),

            Section::make('Forma del lote')
                ->description('Elige primero la finca: su contorno y los demás lotes aparecen como guía y los bordes se "imantan".')
                ->columnSpanFull()
                ->schema([
                    MapaGeometria::make('geometria')
                        ->hiddenLabel()
                        ->entidad('lote')
                        ->campoFinca('finca_id')
                        ->required()
                        ->validationMessages(['required' => 'Dibuja el lote en el mapa.']),
                ]),

            ContenidoPanel::seccion('Se muestra en el panel derecho del mapa al hacer clic en este lote.'),
        ]);
    }
}
