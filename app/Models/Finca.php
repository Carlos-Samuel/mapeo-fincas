<?php

namespace App\Models;

use App\Models\Concerns\TieneImagenes;
use App\Support\Geo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Finca extends Model
{
    use TieneImagenes;

    protected $fillable = ['nombre', 'slug', 'ubicacion', 'geometria', 'contenido'];

    protected function casts(): array
    {
        return [
            'geometria' => 'array',
            'area_ha' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Finca $finca) {
            $finca->area_ha = Geo::areaHectareas($finca->geometria);

            if (blank($finca->slug)) {
                $finca->slug = static::slugUnico($finca->nombre, $finca->id);
            }
        });

        // Borrar uno a uno para que lotes y puntos limpien sus imágenes.
        static::deleting(function (Finca $finca) {
            $finca->lotes()->get()->each->delete();
            $finca->recorridos()->get()->each->delete();
            $finca->rutas()->get()->each->delete();
            $finca->puntos()->get()->each->delete();
        });
    }

    public static function slugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre) ?: 'finca';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignorarId, fn ($q) => $q->whereKeyNot($ignorarId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function puntos(): HasMany
    {
        return $this->hasMany(Punto::class);
    }

    public function rutas(): HasMany
    {
        return $this->hasMany(Ruta::class);
    }

    public function recorridos(): HasMany
    {
        return $this->hasMany(Recorrido::class);
    }
}
