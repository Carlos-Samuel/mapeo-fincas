/*
 * Capas base del mapa (sitio público y admin). Expone window.CapasBase.
 *
 *   const { bases, control } = CapasBase.agregar(mapa, { overlays, collapsed, conControl });
 *
 * Fuentes gratuitas y permitidas, sin llaves:
 *  - Esri World Imagery        (satélite estándar)
 *  - Esri World Imagery Clarity (beta; en muchas zonas rurales trae imágenes más nítidas o más recientes)
 *  - OpenStreetMap              (mapa de calles)
 * La calidad depende de la zona: por eso el visitante puede cambiar de fuente
 * y la elección se recuerda en el navegador (localStorage).
 * NO usar teselas de Google/Bing sin API oficial (lo prohíben sus términos).
 */
(function (global) {
    'use strict';

    const CLAVE = 'fincas.capaBase';
    const ZOOM_MAX = 21;      // se puede acercar más de lo que trae la imagen…
    const ZOOM_NATIVO = 19;   // …ampliando la última tesela disponible

    function etiquetas() {
        return L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
            { maxZoom: ZOOM_MAX, maxNativeZoom: ZOOM_NATIVO },
        );
    }

    function crearBases() {
        return {
            'Satélite': L.layerGroup([
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: ZOOM_MAX,
                    maxNativeZoom: ZOOM_NATIVO,
                    attribution: 'Imágenes &copy; Esri, Maxar, Earthstar Geographics',
                }),
                etiquetas(),
            ]),
            'Satélite nítido (beta)': L.layerGroup([
                L.tileLayer('https://clarity.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: ZOOM_MAX,
                    maxNativeZoom: ZOOM_NATIVO,
                    attribution: 'Imágenes &copy; Esri (Clarity), Maxar',
                }),
                etiquetas(),
            ]),
            'Mapa': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: ZOOM_MAX,
                maxNativeZoom: 19,
                attribution: '&copy; colaboradores de OpenStreetMap',
            }),
        };
    }

    function leerPreferencia() {
        try {
            return localStorage.getItem(CLAVE);
        } catch (e) {
            return null;
        }
    }

    function guardarPreferencia(nombre) {
        try {
            localStorage.setItem(CLAVE, nombre);
        } catch (e) {
            /* modo privado: no pasa nada */
        }
    }

    /** Pone la capa base preferida. Con conControl:false, el control lo crea quien llama (usando `bases`). */
    function agregar(mapa, { overlays = null, collapsed = false, position = 'topright', conControl = true } = {}) {
        const bases = crearBases();
        const preferida = leerPreferencia();
        const inicial = bases[preferida] ? preferida : 'Satélite';
        bases[inicial].addTo(mapa);

        mapa.on('baselayerchange', (e) => guardarPreferencia(e.name));
        mapa.options.maxZoom = ZOOM_MAX;

        const control = conControl ? L.control.layers(bases, overlays, { position, collapsed }).addTo(mapa) : null;
        return { bases, control, inicial };
    }

    global.CapasBase = { agregar, crearBases, ZOOM_MAX };
})(window);
