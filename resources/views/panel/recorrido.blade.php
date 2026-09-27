{{--
    Panel de un recorrido guiado. public/js/mapa.js lee los data-* de cada .paso
    para mover el mapa a la parada activa y mostrar un paso a la vez.
--}}
@php $paradas = $recorrido->paradas->filter(fn ($p) => $p->punto)->values(); @endphp

<button class="panel__volver" type="button" data-accion="volver">← {{ $recorrido->finca->nombre }}</button>

<div class="cabecera-elemento" style="--color-tipo: {{ $recorrido->color }}">
    <div>
        <p class="etiqueta">Recorrido · {{ $paradas->count() }} paradas</p>
        <h2>{{ $recorrido->nombre }}</h2>
    </div>
</div>

@include('panel._galeria', ['imagenes' => $recorrido->imagenes])
@include('panel._contenido', ['html' => $recorrido->contenido])

@if ($paradas->isEmpty())
    <p class="panel__vacio">Este recorrido aún no tiene paradas.</p>
@else
    <div class="recorrido" data-recorrido="{{ $recorrido->id }}" data-total="{{ $paradas->count() }}">
        <div class="recorrido__nav">
            <button type="button" class="boton" data-accion="paso" data-dir="-1">‹ Anterior</button>
            <span class="recorrido__contador">Parada <b data-rol="actual">1</b> de {{ $paradas->count() }}</span>
            <button type="button" class="boton boton--primario" data-accion="paso" data-dir="1">Siguiente ›</button>
        </div>

        @foreach ($paradas as $i => $parada)
            @php $punto = $parada->punto; @endphp
            <section class="paso" data-paso="{{ $i }}" data-punto="{{ $punto->id }}" @if ($i > 0) hidden @endif>
                <h3 class="paso__titulo">
                    <span class="paso__numero" style="background: {{ $recorrido->color }}">{{ $i + 1 }}</span>
                    {{ $punto->nombre }}
                </h3>
                @if ($punto->tipo)
                    <p class="cabecera-elemento__tipo">{{ $punto->tipo->nombre }}</p>
                @endif
                @if (filled($parada->nota))
                    <p class="paso__nota">{{ $parada->nota }}</p>
                @endif
                @include('panel._galeria', ['imagenes' => $punto->imagenes])
                @include('panel._contenido', ['html' => $punto->contenido])
            </section>
        @endforeach

        <h3>Todas las paradas</h3>
        <ol class="recorrido__lista">
            @foreach ($paradas as $i => $parada)
                <li>
                    <button type="button" data-accion="ir-paso" data-paso="{{ $i }}">{{ $parada->punto->nombre }}</button>
                </li>
            @endforeach
        </ol>
    </div>
@endif
