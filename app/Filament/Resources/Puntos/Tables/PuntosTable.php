<?php

namespace App\Filament\Resources\Puntos\Tables;

use App\Models\Punto;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PuntosTable
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
                TextColumn::make('latitud')->numeric(decimalPlaces: 6)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitud')->numeric(decimalPlaces: 6)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('imagenes_count')->label('Fotos')->counts('imagenes')->toggleable(),
                TextColumn::make('updated_at')->label('Actualizado')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('finca')->relationship('finca', 'nombre')->preload(),
                SelectFilter::make('tipo')->relationship('tipo', 'nombre')->preload(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Mapa')
                    ->icon(Heroicon::OutlinedMap)
                    ->url(fn (Punto $record): string => route('fincas.show', [$record->finca, 'punto' => $record->id]), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
