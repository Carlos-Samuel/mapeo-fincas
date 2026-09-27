<?php

namespace App\Filament\Resources\Rutas\Schemas;

use App\Filament\Forms\Components\MapaGeometria;
use App\Filament\Forms\ContenidoPanel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RutaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de la ruta')
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
                        ->helperText('Da el color y la capa en el mapa (camino, cerca, tubería…).')
                        ->relationship('tipo', 'nombre', modifyQueryUsing: fn (Builder $query) => $query->paraRutas())
                        ->searchable()
                        ->preload(),
                    TextInput::make('nombre')
                        ->placeholder('Camino interno, cerca norte, tubería del pozo…')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                ]),

            Section::make('Trazado de la ruta')
                ->description('Elige primero la finca: su contorno, lotes, puntos y otras rutas aparecen como guía y los vértices se "imantan".')
                ->columnSpanFull()
                ->schema([
                    MapaGeometria::make('geometria')
                        ->hiddenLabel()
                        ->entidad('ruta')
                        ->campoFinca('finca_id')
                        ->required()
                        ->validationMessages(['required' => 'Dibuja la ruta en el mapa.']),
                ]),

            ContenidoPanel::seccion('Se muestra en el panel derecho del mapa al hacer clic en esta ruta.'),
        ]);
    }
}
