<?php

namespace App\Models;

use App\Models\Concerns\TieneImagenes;
use App\Support\Geo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    use TieneImagenes;

    protected $fillable = ['finca_id', 'tipo_id', 'codigo', 'nombre', 'geometria', 'contenido'];

    protected function casts(): array
    {
        return [
            'geometria' => 'array',
            'area_ha' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Lote $lote) {
            $lote->area_ha = Geo::areaHectareas($lote->geometria);
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
}
