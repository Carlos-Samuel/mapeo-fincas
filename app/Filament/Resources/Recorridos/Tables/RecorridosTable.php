<?php

namespace App\Filament\Resources\Recorridos\Tables;

use App\Models\Recorrido;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RecorridosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nombre')
            ->columns([
                ColorColumn::make('color')->label(''),
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('finca.nombre')->label('Finca')->sortable(),
                TextColumn::make('paradas_count')->label('Paradas')->counts('paradas'),
                TextColumn::make('updated_at')->label('Actualizado')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('finca')->relationship('finca', 'nombre')->preload(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Mapa')
                    ->icon(Heroicon::OutlinedMap)
                    ->url(fn (Recorrido $record): string => route('fincas.show', [$record->finca, 'recorrido' => $record->id]), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
