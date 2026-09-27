<?php

namespace App\Models\Concerns;

use App\Models\Imagen;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** Galería de imágenes para fincas, lotes y puntos. */
trait TieneImagenes
{
    public static function bootTieneImagenes(): void
    {
        // Al borrar el elemento se borran sus imágenes (y los archivos, ver Imagen).
        static::deleting(function ($modelo) {
            $modelo->imagenes()->get()->each->delete();
        });
    }

    public function imagenes(): MorphMany
    {
        return $this->morphMany(Imagen::class, 'imageable')->orderBy('orden');
    }
}
