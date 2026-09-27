<?php

namespace App\Models;

use App\Models\Concerns\TieneImagenes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Recorrido guiado: paradas ordenadas sobre puntos existentes de la misma finca. */
class Recorrido extends Model
{
    use TieneImagenes;

    protected $fillable = ['finca_id', 'nombre', 'color', 'contenido'];

    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(RecorridoParada::class)->orderBy('orden');
    }
}
