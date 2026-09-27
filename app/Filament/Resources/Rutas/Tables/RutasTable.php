<?php

namespace App\Filament\Resources\Rutas\Tables;

use App\Models\Ruta;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RutasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nombre')
            ->columns([
                ColorColumn::make('tipo.color')->label(''),
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('tipo.nombre')->label('Tipo')->badge()->placeholder('Sin tipo'),
                TextColumn::make('finca.nombre')->label('Finca')->sortable(),
                TextColumn::make('longitud_m')
                    ->label('Longitud')
                    ->formatStateUsing(fn (Ruta $record): ?string => $record->longitudTexto())
                    ->sortable(),
                TextColumn::make('updated_at')->label('Actualizada')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('finca')->relationship('finca', 'nombre')->preload(),
                SelectFilter::make('tipo')->relationship('tipo', 'nombre')->preload(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Mapa')
                    ->icon(Heroicon::OutlinedMap)
                    ->url(fn (Ruta $record): string => route('fincas.show', [$record->finca, 'ruta' => $record->id]), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
