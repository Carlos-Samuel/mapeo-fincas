@php
    $campoFinca = $getCampoFincaStatePath();
    $modo = $getModo();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="mapaGeometria({
            state: $wire.$entangle(@js($getStatePath())),
            fincaId: {{ $campoFinca ? '$wire.$entangle('.\Illuminate\Support\Js::from($campoFinca).')' : \Illuminate\Support\Js::from($getFincaId()) }},
            entidad: @js($getEntidad()),
            modo: @js($modo),
            registroId: @js($getRegistroId()),
            nombreStatePath: @js($getCampoNombreStatePath()),
            urlReferencias: @js(url('api/fincas')),
        })"
        class="mapa-geometria"
        {{ $getExtraAttributeBag() }}
    >
        <div class="mapa-geometria__barra">
            <input
                type="search"
                x-model="busqueda"
                x-on:keydown.enter.prevent="buscar()"
                placeholder="Buscar lugar (ej. Espinal, Tolima) o ir a unas coordenadas"
                class="mapa-geometria__buscar"
            >
            <button type="button" class="mapa-geometria__boton" x-on:click="buscar()" x-bind:disabled="buscando">
                <span x-text="buscando ? 'Buscando…' : 'Buscar'"></span>
            </button>
            <button type="button" class="mapa-geometria__boton mapa-geometria__boton--secundario" x-on:click="elegirGpx()"
                    title="{{ $modo === 'punto' ? 'Usar un waypoint de un archivo GPX' : 'Cargar la forma desde un archivo GPX (recorrido, ruta o waypoints)' }}">
                Cargar GPX
            </button>
            <input type="file" x-ref="archivoGpx" accept=".gpx,application/gpx+xml,application/xml,text/xml" class="mapa-geometria__oculto" x-on:change="cargarGpx($event)">
            <button type="button" class="mapa-geometria__boton mapa-geometria__boton--peligro" x-on:click="borrar()" x-show="tieneGeometria">
                {{ match ($modo) { 'punto' => 'Quitar punto', 'linea' => 'Borrar línea', default => 'Borrar forma' } }}
            </button>
        </div>

        @if ($modo === 'punto')
            <div class="mapa-geometria__barra">
                <input
                    type="text"
                    x-model="coordenadas"
                    x-on:keydown.enter.prevent="ubicarCoordenadas()"
                    placeholder="Coordenadas del punto: 4.151234, -74.884567  ó  4°09'04.4&quot;N 74°53'04.4&quot;W"
                    class="mapa-geometria__buscar"
                    aria-label="Coordenadas del punto"
                >
                <button type="button" class="mapa-geometria__boton" x-on:click="ubicarCoordenadas()">Ubicar punto</button>
            </div>
            <p class="mapa-geometria__error" x-show="errorCoordenadas" x-text="errorCoordenadas"></p>
        @endif

        <div class="mapa-geometria__waypoints" x-show="waypointsGpx.length" x-cloak>
            <template x-for="(w, i) in waypointsGpx" :key="i">
                <button type="button" class="mapa-geometria__waypoint" x-on:click="usarWaypoint(w)">
                    <strong x-text="w.nombre"></strong>
                    <small x-text="w.lat.toFixed(6) + ', ' + w.lng.toFixed(6)"></small>
                </button>
            </template>
        </div>
        <p class="mapa-geometria__gpx" x-show="mensajeGpx" x-text="mensajeGpx"></p>
        <p class="mapa-geometria__error" x-show="errorGpx" x-text="errorGpx"></p>

        <div x-ref="mapa" class="mapa-geometria__mapa" style="height: {{ $getAltura() }}"></div>

        <p class="mapa-geometria__ayuda" x-text="ayuda"></p>
        <p class="mapa-geometria__info" x-show="info" x-text="info"></p>
        <p class="mapa-geometria__advertencia" x-show="advertencia" x-text="advertencia"></p>
    </div>
</x-dynamic-component>
