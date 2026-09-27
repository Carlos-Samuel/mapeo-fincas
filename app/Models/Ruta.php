<?php

namespace App\Models;

use App\Models\Concerns\TieneImagenes;
use App\Support\Geo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Línea dibujada en el mapa (camino, cerca, canal, tubería...). */
class Ruta extends Model
{
    use TieneImagenes;

    protected $fillable = ['finca_id', 'tipo_id', 'nombre', 'geometria', 'contenido'];

    protected function casts(): array
    {
        return [
            'geometria' => 'array',
            'longitud_m' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Ruta $ruta) {
            $ruta->longitud_m = Geo::longitudMetros($ruta->geometria);
        });
    }

    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(Tipo::class);
    }

    public function estaDentroDeFinca(): bool
    {
        return Geo::estaDentro($this->geometria, $this->finca?->geometria);
    }

    public function longitudTexto(): ?string
    {
        if ($this->longitud_m === null) {
            return null;
        }

        return $this->longitud_m >= 1000
            ? number_format($this->longitud_m / 1000, 2, ',', '.').' km'
            : number_format($this->longitud_m, 0, ',', '.').' m';
    }
}
