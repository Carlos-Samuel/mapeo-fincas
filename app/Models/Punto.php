<?php

namespace App\Models;

use App\Models\Concerns\TieneImagenes;
use App\Support\Geo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Punto extends Model
{
    use TieneImagenes;

    protected $fillable = ['finca_id', 'tipo_id', 'nombre', 'geometria', 'contenido'];

    protected function casts(): array
    {
        return [
            'geometria' => 'array',
            'latitud' => 'float',
            'longitud' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Punto $punto) {
            [$lng, $lat] = $punto->geometria['coordinates'] ?? [null, null];
            $punto->longitud = $lng;
            $punto->latitud = $lat;
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

    /** Lote en el que cae el punto, si alguno. */
    public function loteQueLoContiene(): ?Lote
    {
        return $this->finca?->lotes
            ->first(fn (Lote $lote) => Geo::puntoDentro($this->geometria['coordinates'] ?? [0, 0], $lote->geometria));
    }
}
