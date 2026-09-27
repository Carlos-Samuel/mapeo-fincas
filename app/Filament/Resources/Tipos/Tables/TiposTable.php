<?php

namespace App\Filament\Resources\Tipos\Tables;

use App\Models\Tipo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TiposTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('orden')
            ->reorderable('orden')
            ->columns([
                ImageColumn::make('icono')->label('')->disk('uploads')->imageHeight(32),
                TextColumn::make('nombre')->searchable()->sortable(),
                ColorColumn::make('color'),
                TextColumn::make('aplica_a')
                    ->label('Se usa en')
                    ->formatStateUsing(fn (string $state): string => Tipo::APLICA_A[$state] ?? $state)
                    ->badge(),
                TextColumn::make('lotes_count')->label('Lotes')->counts('lotes'),
                TextColumn::make('puntos_count')->label('Puntos')->counts('puntos'),
                TextColumn::make('rutas_count')->label('Rutas')->counts('rutas'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
