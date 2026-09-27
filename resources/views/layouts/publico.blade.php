<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', config('app.name'))</title>
    {{-- Sin Node/Vite: Leaflet por CDN; CSS/JS propios directo desde public/ --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="{{ asset('css/sitio.css') }}?v={{ @filemtime(public_path('css/sitio.css')) }}">
    @stack('head')
</head>
<body>
    <header class="barra">
        <div class="barra__titulo">
            @hasSection('volver')
                <a class="barra__volver" href="@yield('volver')" aria-label="Volver">←</a>
            @endif
            <div>
                <h1>@yield('encabezado', config('app.name'))</h1>
                @hasSection('subtitulo')
                    <p class="barra__sub">@yield('subtitulo')</p>
                @endif
            </div>
        </div>
    </header>

    @yield('contenido')

    @stack('scripts')
</body>
</html>
