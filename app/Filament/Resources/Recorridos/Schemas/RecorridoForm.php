<?php

namespace App\Filament\Resources\Recorridos\Schemas;

use App\Filament\Forms\ContenidoPanel;
use App\Models\Punto;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RecorridoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del recorrido')
                ->description('Un recorrido es una visita guiada: una secuencia de puntos de la finca que se sigue paso a paso en el mapa.')
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
                        ->live()
                        // Las paradas son puntos de la finca: si cambia la finca, se vacían.
                        ->afterStateUpdated(fn (Set $set) => $set('paradas', [])),
                    ColorPicker::make('color')
                        ->helperText('Color de la línea del recorrido en el mapa.')
                        ->default('#e91e63')
                        ->required(),
                    TextInput::make('nombre')
                        ->placeholder('Visita guiada, recorrido de riego…')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                ]),

            Section::make('Paradas')
                ->description('Elige los puntos en el orden en que se visitan (arrastra para reordenar). Para una parada nueva, crea primero el punto.')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('paradas')
                        ->hiddenLabel()
                        ->relationship()
                        ->orderColumn('orden')
                        ->schema([
                            Select::make('punto_id')
                                ->label('Punto')
                                ->options(fn (Get $get): array => Punto::query()
                                    ->where('finca_id', $get('../../finca_id'))
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                                    ->all())
                                ->searchable()
                                ->required(),
                            Textarea::make('nota')
                                ->label('Qué se ve o se hace en esta parada')
                                ->rows(2)
                                ->maxLength(2000),
                        ])
                        ->columns(2)
                        ->minItems(2)
                        ->defaultItems(0)
                        ->addActionLabel('Agregar parada')
                        ->itemLabel(fn (array $state): ?string => filled($state['punto_id'] ?? null)
                            ? Punto::find($state['punto_id'])?->nombre
                            : null)
                        ->validationMessages(['min' => 'Un recorrido necesita al menos 2 paradas.']),
                ]),

            ContenidoPanel::seccion('Se muestra en el panel derecho al abrir el recorrido, antes de empezar las paradas.'),
        ]);
    }
}
