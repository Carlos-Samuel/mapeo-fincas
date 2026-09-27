@php $tipo = $elemento->tipo; @endphp

<button class="panel__volver" type="button" data-accion="volver">← {{ $elemento->finca->nombre }}</button>

<div class="cabecera-elemento" style="--color-tipo: {{ $tipo?->color ?? '#9e9e9e' }}">
    @if ($icono = $tipo?->iconoUrl())
        <img src="{{ $icono }}" alt="" width="48" height="48">
    @endif
    <div>
        <p class="etiqueta">{{ $etiqueta }}</p>
        <h2>{{ $elemento->nombre }}</h2>
        @if ($tipo)
            <p class="cabecera-elemento__tipo">{{ $tipo->nombre }}</p>
        @endif
    </div>
</div>

@include('panel._galeria', ['imagenes' => $elemento->imagenes])

@if ($datos)
    <dl class="ficha">
        @foreach ($datos as $clave => $valor)
            <dt>{{ $clave }}</dt>
            <dd>{{ $valor }}</dd>
        @endforeach
    </dl>
@endif

@include('panel._contenido', ['html' => $elemento->contenido])

@if (blank($elemento->contenido) && $elemento->imagenes->isEmpty())
    <p class="panel__vacio">Aún no hay descripción para {{ ['lote' => 'este lote', 'punto' => 'este punto', 'ruta' => 'esta ruta'][$clase] ?? 'este elemento' }}.</p>
@endif
