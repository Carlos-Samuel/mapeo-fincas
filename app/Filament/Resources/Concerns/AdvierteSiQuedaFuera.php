<?php

namespace App\Filament\Resources\Concerns;

use Filament\Notifications\Notification;

/**
 * Para las páginas de crear/editar lotes y puntos:
 * si el elemento queda por fuera del contorno de su finca, avisa (pero sí guarda).
 */
trait AdvierteSiQuedaFuera
{
    protected function afterCreate(): void
    {
        $this->advertirSiQuedaFuera();
    }

    protected function afterSave(): void
    {
        $this->advertirSiQuedaFuera();
    }

    protected function advertirSiQuedaFuera(): void
    {
        $registro = $this->getRecord()->load('finca');

        if ($registro->estaDentroDeFinca()) {
            return;
        }

        Notification::make()
            ->warning()
            ->title('Guardado, pero queda por fuera de la finca')
            ->body("«{$registro->nombre}» no está completamente dentro del contorno de «{$registro->finca->nombre}».")
            ->persistent()
            ->send();
    }
}
