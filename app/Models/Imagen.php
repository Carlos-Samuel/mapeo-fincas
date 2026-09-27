<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $fillable = ['ruta', 'descripcion', 'orden'];

    protected static function booted(): void
    {
        // Mantener limpia la carpeta public/uploads.
        static::updated(function (Imagen $imagen) {
            if ($imagen->wasChanged('ruta') && $imagen->getOriginal('ruta')) {
                Storage::disk('uploads')->delete($imagen->getOriginal('ruta'));
            }
        });
        static::deleted(fn (Imagen $imagen) => Storage::disk('uploads')->delete($imagen->ruta));
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return Storage::disk('uploads')->url($this->ruta);
    }
}
