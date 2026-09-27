{{-- Cargado en el <head> del admin (ver AdminPanelProvider). Sin Node: todo por CDN o desde public/. --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.20.2/dist/leaflet-geoman.css" crossorigin="">
<link rel="stylesheet" href="{{ asset('css/admin/mapa-geometria.css') }}?v={{ @filemtime(public_path('css/admin/mapa-geometria.css')) }}">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.20.2/dist/leaflet-geoman.js" crossorigin=""></script>
<script src="{{ asset('js/admin/mapa-geometria.js') }}?v={{ @filemtime(public_path('js/admin/mapa-geometria.js')) }}"></script>
