@php
    $campoFinca = $getCampoFincaStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="mapaGeometria({
            state: $wire.$entangle(@js($getStatePath())),
            fincaId: {{ $campoFinca ? '$wire.$entangle('.\Illuminate\Support\Js::from($campoFinca).')' : \Illuminate\Support\Js::from($getFincaId()) }},
            entidad: @js($getEntidad()),
            modo: @js($getModo()),
            registroId: @js($getRegistroId()),
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
                placeholder="Buscar lugar (ej. Espinal, Tolima) o pegar coordenadas «4.15, -74.88»"
                class="mapa-geometria__buscar"
            >
            <button type="button" class="mapa-geometria__boton" x-on:click="buscar()" x-bind:disabled="buscando">
                <span x-text="buscando ? 'Buscando…' : 'Buscar'"></span>
            </button>
            <button type="button" class="mapa-geometria__boton mapa-geometria__boton--peligro" x-on:click="borrar()" x-show="tieneGeometria">
                {{ match ($getModo()) { 'punto' => 'Quitar punto', 'linea' => 'Borrar línea', default => 'Borrar forma' } }}
            </button>
        </div>

        <div x-ref="mapa" class="mapa-geometria__mapa" style="height: {{ $getAltura() }}"></div>

        <p class="mapa-geometria__ayuda" x-text="ayuda"></p>
        <p class="mapa-geometria__info" x-show="info" x-text="info"></p>
        <p class="mapa-geometria__advertencia" x-show="advertencia" x-text="advertencia"></p>
    </div>
</x-dynamic-component>
