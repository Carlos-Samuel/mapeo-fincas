<div class="panel__cabecera">
    <p class="etiqueta">Finca</p>
    <h2>{{ $finca->nombre }}</h2>
    <p class="panel__meta">
        @if ($finca->area_ha)
            {{ number_format($finca->area_ha, 2, ',', '.') }} ha ·
        @endif
        {{ $finca->lotes->count() }} {{ Str::plural('lote', $finca->lotes->count()) }} ·
        {{ $finca->puntos->count() }} {{ Str::plural('punto', $finca->puntos->count()) }}
        @if ($finca->rutas->isNotEmpty())
            · {{ $finca->rutas->count() }} {{ Str::plural('ruta', $finca->rutas->count()) }}
        @endif
    </p>
</div>

@include('panel._galeria', ['imagenes' => $finca->imagenes])
@include('panel._contenido', ['html' => $finca->contenido])

@if ($finca->lotes->isNotEmpty())
    <h3>Lotes</h3>
    <ul class="lista">
        @foreach ($finca->lotes->sortBy(['codigo', 'nombre']) as $lote)
            <li>
                <button type="button" class="lista__item" data-accion="ver" data-clase="lote" data-id="{{ $lote->id }}">
                    <span class="punto-color" style="background: {{ $lote->tipo?->color ?? '#9e9e9e' }}"></span>
                    @if ($icono = $lote->tipo?->iconoUrl())
                        <img src="{{ $icono }}" alt="" width="24" height="24">
                    @endif
                    <span class="lista__texto">
                        <strong>{{ $lote->nombre }}</strong>
                        <small>{{ collect([$lote->codigo, $lote->tipo?->nombre ?? 'Sin tipo'])->filter()->join(' · ') }}</small>
                    </span>
                    @if ($lote->area_ha)
                        <span class="lista__extra">{{ number_format($lote->area_ha, 2, ',', '.') }} ha</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>
@endif

@if ($finca->puntos->isNotEmpty())
    <h3>Puntos de referencia</h3>
    <ul class="lista">
        @foreach ($finca->puntos->sortBy('nombre') as $punto)
            <li>
                <button type="button" class="lista__item" data-accion="ver" data-clase="punto" data-id="{{ $punto->id }}">
                    <span class="punto-color" style="background: {{ $punto->tipo?->color ?? '#9e9e9e' }}"></span>
                    @if ($icono = $punto->tipo?->iconoUrl())
                        <img src="{{ $icono }}" alt="" width="24" height="24">
                    @endif
                    <span class="lista__texto">
                        <strong>{{ $punto->nombre }}</strong>
                        <small>{{ $punto->tipo?->nombre ?? 'Sin tipo' }}</small>
                    </span>
                </button>
            </li>
        @endforeach
    </ul>
@endif

@if ($finca->recorridos->isNotEmpty())
    <h3>Recorridos</h3>
    <ul class="lista">
        @foreach ($finca->recorridos->sortBy('nombre') as $recorrido)
            <li>
                <button type="button" class="lista__item" data-accion="ver" data-clase="recorrido" data-id="{{ $recorrido->id }}">
                    <span class="punto-color" style="background: {{ $recorrido->color }}"></span>
                    <span class="lista__texto">
                        <strong>{{ $recorrido->nombre }}</strong>
                        <small>{{ $recorrido->paradas->count() }} paradas</small>
                    </span>
                    <span class="lista__extra">Ver ›</span>
                </button>
            </li>
        @endforeach
    </ul>
@endif

@if ($finca->rutas->isNotEmpty())
    <h3>Rutas</h3>
    <ul class="lista">
        @foreach ($finca->rutas->sortBy('nombre') as $ruta)
            <li>
                <button type="button" class="lista__item" data-accion="ver" data-clase="ruta" data-id="{{ $ruta->id }}">
                    <span class="linea-color" style="background: {{ $ruta->tipo?->color ?? '#9e9e9e' }}"></span>
                    @if ($icono = $ruta->tipo?->iconoUrl())
                        <img src="{{ $icono }}" alt="" width="24" height="24">
                    @endif
                    <span class="lista__texto">
                        <strong>{{ $ruta->nombre }}</strong>
                        <small>{{ $ruta->tipo?->nombre ?? 'Sin tipo' }}</small>
                    </span>
                    @if ($largo = $ruta->longitudTexto())
                        <span class="lista__extra">{{ $largo }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>
@endif

@if ($finca->lotes->isEmpty() && $finca->puntos->isEmpty() && $finca->rutas->isEmpty())
    <p class="panel__vacio">Esta finca aún no tiene lotes, puntos ni rutas.</p>
@endif
