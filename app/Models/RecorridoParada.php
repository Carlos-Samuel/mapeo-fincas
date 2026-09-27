<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecorridoParada extends Model
{
    protected $table = 'recorrido_paradas';

    protected $fillable = ['recorrido_id', 'punto_id', 'orden', 'nota'];

    public function recorrido(): BelongsTo
    {
        return $this->belongsTo(Recorrido::class);
    }

    public function punto(): BelongsTo
    {
        return $this->belongsTo(Punto::class);
    }
}
