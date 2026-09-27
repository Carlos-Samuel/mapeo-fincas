@extends('layouts.publico')

@section('titulo', 'Fincas')
@section('encabezado', 'Fincas')

@section('contenido')
    <main class="portada">
        @forelse ($fincas as $finca)
            <a class="tarjeta" href="{{ route('fincas.show', $finca) }}">
                <div class="tarjeta__imagen">
                    @if ($portada = $finca->imagenes->first())
                        <img src="{{ $portada->url() }}" alt="" loading="lazy">
                    @else
                        <span aria-hidden="true">🗺️</span>
                    @endif
                </div>
                <div class="tarjeta__cuerpo">
                    <h2>{{ $finca->nombre }}</h2>
                    @if ($finca->ubicacion)
                        <p class="tarjeta__sub">{{ $finca->ubicacion }}</p>
                    @endif
                    <p class="tarjeta__meta">
                        @if ($finca->area_ha)
                            {{ number_format($finca->area_ha, 2, ',', '.') }} ha ·
                        @endif
                        {{ $finca->lotes_count }} {{ Str::plural('lote', $finca->lotes_count) }} ·
                        {{ $finca->puntos_count }} {{ Str::plural('punto', $finca->puntos_count) }}
                        @if ($finca->rutas_count)
                            · {{ $finca->rutas_count }} {{ Str::plural('ruta', $finca->rutas_count) }}
                        @endif
                        @if ($finca->recorridos_count)
                            · {{ $finca->recorridos_count }} {{ Str::plural('recorrido', $finca->recorridos_count) }}
                        @endif
                    </p>
                </div>
            </a>
        @empty
            <div class="portada__vacia">
                <p>Todavía no hay fincas.</p>
                <p><a href="{{ url('/admin') }}">Entra al administrador</a> para crear la primera.</p>
            </div>
        @endforelse
    </main>
@endsection
