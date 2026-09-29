<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Campo de formulario con mapa satelital para dibujar un polígono, una línea o ubicar un punto.
 * Guarda GeoJSON (Polygon, LineString o Point). La interacción vive en public/js/admin/mapa-geometria.js.
 *
 *   MapaGeometria::make('geometria')->entidad('lote')->campoFinca('finca_id')
 */
class MapaGeometria extends Field
{
    protected string $view = 'filament.forms.components.mapa-geometria';

    /** finca | lote | punto | ruta */
    protected string | Closure $entidad = 'lote';

    /** Nombre del campo hermano que tiene el id de la finca (formularios de lote y punto). */
    protected string | Closure | null $campoFinca = null;

    /** Id de finca fijo (formulario de finca: muestra sus lotes y puntos como referencia). */
    protected int | Closure | null $fincaId = null;

    protected string | Closure $altura = '480px';

    public function entidad(string | Closure $entidad): static
    {
        $this->entidad = $entidad;

        return $this;
    }

    public function campoFinca(string | Closure | null $campo): static
    {
        $this->campoFinca = $campo;

        return $this;
    }

    public function fincaId(int | Closure | null $id): static
    {
        $this->fincaId = $id;

        return $this;
    }

    public function altura(string | Closure $altura): static
    {
        $this->altura = $altura;

        return $this;
    }

    public function getEntidad(): string
    {
        return $this->evaluate($this->entidad);
    }

    public function getModo(): string
    {
        return match ($this->getEntidad()) {
            'punto' => 'punto',
            'ruta' => 'linea',
            default => 'poligono',
        };
    }

    public function getFincaId(): ?int
    {
        return $this->evaluate($this->fincaId);
    }

    /** Ruta de estado Livewire del campo de finca, p. ej. "data.finca_id". */
    public function getCampoFincaStatePath(): ?string
    {
        $campo = $this->evaluate($this->campoFinca);

        if (blank($campo)) {
            return null;
        }

        $statePath = $this->getStatePath();

        return Str::contains($statePath, '.')
            ? Str::beforeLast($statePath, '.').'.'.$campo
            : $campo;
    }

    /**
     * Ruta de estado del campo hermano "nombre" (si existe), p. ej. "data.nombre".
     * Se usa para proponer el nombre del waypoint al cargar un GPX en un punto.
     */
    public function getCampoNombreStatePath(): ?string
    {
        if ($this->getEntidad() !== 'punto') {
            return null;
        }

        $statePath = $this->getStatePath();

        return Str::contains($statePath, '.') ? Str::beforeLast($statePath, '.').'.nombre' : 'nombre';
    }

    /** Id del registro que se está editando (para no dibujarlo dos veces como referencia). */
    public function getRegistroId(): ?int
    {
        $registro = $this->getRecord();

        return $registro instanceof Model ? $registro->getKey() : null;
    }

    public function getAltura(): string
    {
        return $this->evaluate($this->altura);
    }
}
