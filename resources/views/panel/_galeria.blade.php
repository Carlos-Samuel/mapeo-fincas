@if ($imagenes->isNotEmpty())
    @php $primera = $imagenes->first(); @endphp
    <div class="galeria">
        <figure class="galeria__principal">
            <img src="{{ $primera->url() }}" alt="{{ $primera->descripcion }}">
            <figcaption @class(['oculto' => blank($primera->descripcion)])>{{ $primera->descripcion }}</figcaption>
        </figure>
        @if ($imagenes->count() > 1)
            <div class="galeria__minis">
                @foreach ($imagenes as $imagen)
                    <button type="button" @class(['galeria__mini', 'activa' => $loop->first]) data-accion="miniatura"
                            data-src="{{ $imagen->url() }}" data-desc="{{ $imagen->descripcion }}"
                            aria-label="{{ $imagen->descripcion ?: 'Imagen '.$loop->iteration }}">
                        <img src="{{ $imagen->url() }}" alt="" loading="lazy">
                    </button>
                @endforeach
            </div>
        @endif
    </div>
@endif
