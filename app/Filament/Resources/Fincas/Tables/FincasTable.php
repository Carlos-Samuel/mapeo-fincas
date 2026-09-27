<?php

namespace App\Filament\Resources\Fincas\Tables;

use App\Models\Finca;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FincasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nombre')
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('ubicacion')->label('Ubicación')->searchable()->toggleable(),
                TextColumn::make('area_ha')
                    ->label('Área')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ha')
                    ->sortable(),
                TextColumn::make('lotes_count')->label('Lotes')->counts('lotes'),
                TextColumn::make('puntos_count')->label('Puntos')->counts('puntos'),
                TextColumn::make('rutas_count')->label('Rutas')->counts('rutas')->toggleable(),
                TextColumn::make('updated_at')->label('Actualizada')->since()->sortable()->toggleable(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver mapa')
                    ->icon(Heroicon::OutlinedMap)
                    ->url(fn (Finca $record): string => route('fincas.show', $record), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
