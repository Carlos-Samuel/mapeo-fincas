<?php

namespace App\Filament\Resources\Fincas\Schemas;

use App\Filament\Forms\Components\MapaGeometria;
use App\Filament\Forms\ContenidoPanel;
use App\Models\Finca;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FincaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de la finca')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(150),
                    TextInput::make('ubicacion')
                        ->label('Ubicación')
                        ->placeholder('Vereda, municipio, departamento')
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->label('Dirección web')
                        ->prefix(url('/fincas').'/')
                        ->helperText('Déjalo vacío para generarlo a partir del nombre. Si lo cambias, los enlaces viejos dejan de funcionar.')
                        ->alphaDash()
                        ->maxLength(150)
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),
                ]),

            Section::make('Contorno de la finca')
                ->description('Dibuja el límite de la finca sobre la imagen satelital. Sus lotes, puntos y rutas aparecen como referencia.')
                ->columnSpanFull()
                ->schema([
                    MapaGeometria::make('geometria')
                        ->hiddenLabel()
                        ->entidad('finca')
                        ->fincaId(fn (?Finca $record): ?int => $record?->id)
                        ->required()
                        ->validationMessages(['required' => 'Dibuja el contorno de la finca en el mapa.']),
                ]),

            ContenidoPanel::seccion('Se muestra en el panel derecho del mapa cuando no hay ningún lote o punto seleccionado.'),
        ]);
    }
}
