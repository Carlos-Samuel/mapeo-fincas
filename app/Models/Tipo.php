<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Tipo extends Model
{
    public const APLICA_A = [
        'lote' => 'Lotes',
        'punto' => 'Puntos',
        'ambos' => 'Lotes y puntos',
        'ruta' => 'Rutas',
        'todos' => 'Lotes, puntos y rutas',
    ];

    protected $fillable = ['nombre', 'aplica_a', 'icono', 'color', 'orden'];

    protected static function booted(): void
    {
        static::updated(function (Tipo $tipo) {
            if ($tipo->wasChanged('icono') && $tipo->getOriginal('icono')) {
                Storage::disk('uploads')->delete($tipo->getOriginal('icono'));
            }
        });
        static::deleted(fn (Tipo $tipo) => $tipo->icono && Storage::disk('uploads')->delete($tipo->icono));
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function puntos(): HasMany
    {
        return $this->hasMany(Punto::class);
    }

    /** Tipos que se pueden asignar a lotes o a puntos. */
    public function scopeParaLotes(Builder $query): void
    {
        $query->whereIn('aplica_a', ['lote', 'ambos', 'todos'])->orderBy('orden')->orderBy('nombre');
    }

    public function scopeParaPuntos(Builder $query): void
    {
        $query->whereIn('aplica_a', ['punto', 'ambos', 'todos'])->orderBy('orden')->orderBy('nombre');
    }

    public function scopeParaRutas(Builder $query): void
    {
        $query->whereIn('aplica_a', ['ruta', 'todos'])->orderBy('orden')->orderBy('nombre');
    }

    public function rutas(): HasMany
    {
        return $this->hasMany(Ruta::class);
    }

    public function iconoUrl(): ?string
    {
        return $this->icono ? Storage::disk('uploads')->url($this->icono) : null;
    }
}
