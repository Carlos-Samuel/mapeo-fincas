@extends('layouts.publico')

@section('titulo', $finca->nombre)
@section('encabezado', $finca->nombre)
@section('subtitulo', $finca->ubicacion)
@section('volver', route('fincas.index'))

@section('contenido')
    <main class="layout">
        <div id="mapa" class="mapa" aria-label="Mapa de {{ $finca->nombre }}"></div>

        <aside
            id="panel"
            class="panel"
            aria-live="polite"
            data-url-datos="{{ route('fincas.datos', $finca) }}"
            data-url-panel-lote="{{ url('panel/lotes') }}"
            data-url-panel-punto="{{ url('panel/puntos') }}"
            data-url-panel-ruta="{{ url('panel/rutas') }}"
            data-url-panel-recorrido="{{ url('panel/recorridos') }}"
        >
            @include('panel.finca', ['finca' => $finca])
        </aside>
    </main>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="{{ asset('js/mapa.js') }}?v={{ @filemtime(public_path('js/mapa.js')) }}"></script>
@endpush
