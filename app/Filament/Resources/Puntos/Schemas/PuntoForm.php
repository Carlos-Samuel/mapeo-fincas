<?php

namespace App\Filament\Resources\Puntos\Schemas;

use App\Filament\Forms\Components\MapaGeometria;
use App\Filament\Forms\ContenidoPanel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PuntoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del punto')
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
                        ->relationship('tipo', 'nombre', modifyQueryUsing: fn (Builder $query) => $query->paraPuntos())
                        ->searchable()
                        ->preload(),
                    TextInput::make('nombre')
                        ->placeholder('Casa principal, pozo 2, bodega…')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                ]),

            Section::make('Ubicación del punto')
                ->description('Elige primero la finca: su contorno, lotes y otros puntos aparecen como guía.')
                ->columnSpanFull()
                ->schema([
                    MapaGeometria::make('geometria')
                        ->hiddenLabel()
                        ->entidad('punto')
                        ->campoFinca('finca_id')
                        ->required()
                        ->validationMessages(['required' => 'Ubica el punto en el mapa.']),
                ]),

            ContenidoPanel::seccion('Se muestra en el panel derecho del mapa al hacer clic en este punto.'),
        ]);
    }
}
