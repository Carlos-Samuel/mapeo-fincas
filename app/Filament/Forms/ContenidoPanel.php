<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Lo que se muestra en el panel derecho del mapa público: texto enriquecido + galería.
 * Se reutiliza en los formularios de finca, lote y punto.
 */
class ContenidoPanel
{
    public static function seccion(string $descripcion): Section
    {
        return Section::make('Contenido del panel lateral')
            ->description($descripcion)
            ->columnSpanFull()
            ->schema([
                Repeater::make('imagenes')
                    ->label('Galería de imágenes')
                    ->relationship()
                    ->orderColumn('orden')
                    ->schema([
                        FileUpload::make('ruta')
                            ->label('Imagen')
                            ->image()
                            ->disk('uploads')
                            ->directory('galeria')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->required(),
                        TextInput::make('descripcion')
                            ->label('Descripción (pie de foto)')
                            ->maxLength(255),
                    ])
                    ->grid(3)
                    ->defaultItems(0)
                    ->addActionLabel('Agregar imagen')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['descripcion'] ?? null),

                RichEditor::make('contenido')
                    ->label('Descripción')
                    ->helperText('Texto libre: usa títulos, listas, tablas y negritas para organizar la información.')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList', 'blockquote', 'horizontalRule'],
                        ['table', 'attachFiles'],
                        ['undo', 'redo'],
                    ])
                    ->fileAttachmentsDisk('uploads')
                    ->fileAttachmentsDirectory('contenido')
                    ->fileAttachmentsVisibility('public'),
            ]);
    }
}
