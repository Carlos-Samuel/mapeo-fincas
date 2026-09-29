/*
 * Campo "MapaGeometria" del admin (Filament).
 * Dibuja/edita un polígono (fincas, lotes), una línea (rutas) o ubica un punto (puntos) y lo guarda como GeoJSON.
 * Extras: ubicar un punto escribiendo coordenadas (decimal o GMS) y cargar la forma desde un archivo GPX.
 *
 * Requiere, cargados antes (resources/views/filament/mapa-assets.blade.php):
 *   Leaflet, Leaflet-Geoman, public/js/capas-base.js y public/js/geo-herramientas.js
 */
document.addEventListener('alpine:init', () => {
    const G = window.GeoHerramientas;

    window.Alpine.data('mapaGeometria', (cfg) => {
        // Objetos de Leaflet fuera del estado reactivo de Alpine (los proxies los rompen).
        let mapa = null;
        let capaEdicion = null;   // polígono, línea o marcador que se está editando
        let capaRef = null;       // finca, lotes, rutas y puntos de referencia
        let contornoFinca = null; // GeoJSON del contorno para validar
        let peticion = 0;

        const COLOR_EDICION = '#16a34a';
        const estiloEdicion = () => ({
            color: COLOR_EDICION,
            fillColor: COLOR_EDICION,
            fillOpacity: 0.3,
            weight: cfg.modo === 'linea' ? 5 : 3,
        });

        return {
            state: cfg.state,
            fincaId: cfg.fincaId,
            modo: cfg.modo,
            busqueda: '',
            buscando: false,
            tieneGeometria: false,
            info: '',
            advertencia: '',
            ayuda: '',
            // Coordenadas escritas (modo punto)
            coordenadas: '',
            errorCoordenadas: '',
            // GPX
            mensajeGpx: '',
            errorGpx: '',
            waypointsGpx: [],

            init() {
                this.ayuda = {
                    punto: 'Haz clic en el mapa, escribe las coordenadas o carga un GPX con waypoints. Puedes arrastrar el marcador para moverlo.',
                    linea: 'Usa «Dibujar línea» (izquierda): clic en cada vértice y clic en el último para terminar. O carga un GPX. Luego puedes editar vértices o mover la línea.',
                    poligono: 'Dibuja con las herramientas de la izquierda (polígono o rectángulo) o carga un GPX. Luego puedes editar vértices o mover la forma.',
                }[cfg.modo];

                this.$nextTick(() => {
                    this.crearMapa();
                    this.cargarGeometriaInicial();
                    this.cargarReferencias();
                });

                if (cfg.entidad !== 'finca') {
                    this.$watch('fincaId', () => this.cargarReferencias());
                }
            },

            destroy() {
                mapa?.remove();
                mapa = null;
            },

            /* ---------------- Mapa ---------------- */
            crearMapa() {
                mapa = L.map(this.$refs.mapa, { maxZoom: CapasBase.ZOOM_MAX }).setView([4.6, -74.1], 6);
                CapasBase.agregar(mapa);
                L.control.scale({ imperial: false }).addTo(mapa);

                capaRef = L.featureGroup().addTo(mapa);

                if (cfg.modo === 'punto') {
                    mapa.on('click', (e) => {
                        this.ponerMarcador(e.latlng);
                        this.sincronizar();
                    });
                } else {
                    mapa.pm.setLang('es');
                    mapa.pm.addControls({
                        position: 'topleft',
                        drawMarker: false,
                        drawCircleMarker: false,
                        drawPolyline: cfg.modo === 'linea',
                        drawCircle: false,
                        drawText: false,
                        drawRectangle: cfg.modo === 'poligono',
                        drawPolygon: cfg.modo === 'poligono',
                        editMode: true,
                        dragMode: true,
                        cutPolygon: false,
                        removalMode: true,
                        rotateMode: false,
                    });
                    mapa.pm.setPathOptions(estiloEdicion());

                    mapa.on('pm:create', (e) => {
                        // Solo una forma: la nueva reemplaza a la anterior.
                        this.reemplazarCapa(e.layer);
                        this.mensajeGpx = '';
                        this.sincronizar();
                    });
                    mapa.on('pm:remove', (e) => {
                        if (e.layer === capaEdicion) {
                            capaEdicion = null;
                            this.sincronizar();
                        }
                    });
                }

                // El mapa puede crearse oculto (secciones, pestañas): recalcular tamaño al mostrarse.
                new ResizeObserver(() => mapa?.invalidateSize()).observe(this.$refs.mapa);
            },

            reemplazarCapa(capa) {
                if (capaEdicion && capaEdicion !== capa) mapa.removeLayer(capaEdicion);
                capaEdicion = capa;
                this.escucharEdicion(capaEdicion);
            },

            escucharEdicion(capa) {
                capa.on('pm:edit pm:dragend pm:markerdragend', () => this.sincronizar());
            },

            ponerMarcador(latlng) {
                if (capaEdicion) {
                    capaEdicion.setLatLng(latlng);
                    return;
                }
                capaEdicion = L.marker(latlng, { draggable: true, pmIgnore: true }).addTo(mapa);
                capaEdicion.on('dragend', () => this.sincronizar());
            },

            /** Pone una geometría GeoJSON (Polygon/LineString) como forma editable y encuadra. */
            ponerForma(geometria) {
                const capa = L.geoJSON(geometria, { style: estiloEdicion() }).getLayers()[0];
                capa.addTo(mapa);
                this.reemplazarCapa(capa);
                mapa.fitBounds(capa.getBounds(), { padding: [30, 30] });
            },

            cargarGeometriaInicial() {
                const g = this.state;
                if (!g || !g.type) {
                    this.actualizarTextos();
                    return;
                }

                if (g.type === 'Point') {
                    const [lng, lat] = g.coordinates;
                    this.ponerMarcador(L.latLng(lat, lng));
                    mapa.setView([lat, lng], 17);
                } else {
                    this.ponerForma(g);
                }
                this.actualizarTextos();
            },

            /* ---------------- Coordenadas escritas (modo punto) ---------------- */
            ubicarCoordenadas() {
                this.errorCoordenadas = '';
                try {
                    const { lat, lng } = G.leerCoordenadas(this.coordenadas);
                    this.ponerMarcador(L.latLng(lat, lng));
                    mapa.setView([lat, lng], Math.max(mapa.getZoom(), 17));
                    this.sincronizar();
                } catch (e) {
                    this.errorCoordenadas = e.message;
                }
            },

            /* ---------------- GPX ---------------- */
            elegirGpx() {
                this.$refs.archivoGpx.value = '';
                this.$refs.archivoGpx.click();
            },

            async cargarGpx(evento) {
                const archivo = evento.target.files?.[0];
                this.errorGpx = '';
                this.mensajeGpx = '';
                this.waypointsGpx = [];
                if (!archivo) return;

                try {
                    if (archivo.size > 10 * 1024 * 1024) throw new Error('El archivo pesa más de 10 MB.');
                    const gpx = G.leerGpx(await archivo.text());

                    if (cfg.modo === 'punto') {
                        if (!gpx.waypoints.length) {
                            throw new Error('El GPX no trae waypoints (puntos marcados). Para un recorrido usa lotes o rutas.');
                        }
                        if (gpx.waypoints.length === 1) {
                            this.usarWaypoint(gpx.waypoints[0]);
                        } else {
                            this.waypointsGpx = gpx.waypoints;
                            this.mensajeGpx = `El archivo trae ${gpx.waypoints.length} waypoints: elige cuál usar. (Para crearlos todos de una vez, usa «Importar puntos (GPX)» en la finca.)`;
                        }
                        return;
                    }

                    const r = G.geometriaDesdeGpx(gpx, cfg.modo);
                    this.ponerForma(r.geometria);
                    this.sincronizar();
                    const simplificado = r.final < r.original ? `, simplificado de ${r.original} a ${r.final} vértices` : ` (${r.final} vértices)`;
                    this.mensajeGpx = `Cargado desde ${archivo.name}: ${r.origen}${simplificado}. Revísalo y ajústalo antes de guardar.`;
                } catch (e) {
                    this.errorGpx = e.message;
                }
            },

            usarWaypoint(w) {
                this.waypointsGpx = [];
                this.ponerMarcador(L.latLng(w.lat, w.lng));
                mapa.setView([w.lat, w.lng], Math.max(mapa.getZoom(), 17));
                this.sincronizar();
                this.mensajeGpx = `Ubicado en el waypoint «${w.nombre}».`;

                // Si el nombre del punto está vacío, proponer el del waypoint.
                if (cfg.nombreStatePath && w.nombre && !this.$wire.get(cfg.nombreStatePath)) {
                    this.$wire.set(cfg.nombreStatePath, w.nombre, false);
                }
            },

            /* ---------------- Referencias (finca, otros lotes, rutas y puntos) ---------------- */
            async cargarReferencias() {
                const ticket = ++peticion;
                capaRef.clearLayers();
                contornoFinca = null;

                if (!this.fincaId) {
                    this.actualizarTextos();
                    return;
                }

                let datos;
                try {
                    const r = await fetch(`${cfg.urlReferencias}/${this.fincaId}/geometrias`, { headers: { Accept: 'application/json' } });
                    if (!r.ok) throw new Error(r.status);
                    datos = await r.json();
                } catch (e) {
                    console.error('No se pudieron cargar las referencias', e);
                    return;
                }
                if (ticket !== peticion || !mapa) return;

                // No editables, pero sí "imantados": al dibujar, los vértices se pegan a bordes vecinos.
                const noEditable = { pmIgnore: true, snapIgnore: false, interactive: true };

                if (datos.finca.geometria) {
                    contornoFinca = datos.finca.geometria;
                    if (cfg.entidad !== 'finca') {
                        L.geoJSON(datos.finca.geometria, {
                            ...noEditable,
                            style: { color: '#ffffff', weight: 3, dashArray: '8 6', fill: false },
                        })
                            .bindTooltip(`Finca: ${esc(datos.finca.nombre)}`, { sticky: true })
                            .addTo(capaRef);
                    }
                }

                datos.lotes
                    .filter((l) => !(cfg.entidad === 'lote' && l.id === cfg.registroId))
                    .forEach((l) => {
                        L.geoJSON(l.geometria, {
                            ...noEditable,
                            style: { color: l.color, fillColor: l.color, weight: 1.5, fillOpacity: 0.15 },
                        })
                            .bindTooltip(`Lote: ${esc(l.nombre)}`, { sticky: true })
                            .addTo(capaRef);
                    });

                (datos.rutas ?? [])
                    .filter((r) => !(cfg.entidad === 'ruta' && r.id === cfg.registroId))
                    .forEach((r) => {
                        L.geoJSON(r.geometria, { ...noEditable, style: { color: r.color, weight: 3, opacity: 0.8, dashArray: '6 6' } })
                            .bindTooltip(`Ruta: ${esc(r.nombre)}`, { sticky: true })
                            .addTo(capaRef);
                    });

                datos.puntos
                    .filter((p) => !(cfg.entidad === 'punto' && p.id === cfg.registroId))
                    .forEach((p) => {
                        const [lng, lat] = p.geometria.coordinates;
                        L.circleMarker([lat, lng], { ...noEditable, radius: 5, color: '#fff', weight: 1.5, fillColor: p.color, fillOpacity: 1 })
                            .bindTooltip(`Punto: ${esc(p.nombre)}`)
                            .addTo(capaRef);
                    });

                capaRef.bringToBack();

                // Si aún no hay nada dibujado, centrar en la finca.
                if (!capaEdicion && capaRef.getLayers().length) {
                    mapa.fitBounds(capaRef.getBounds(), { padding: [30, 30] });
                }
                this.actualizarTextos();
            },

            /* ---------------- Estado ---------------- */
            sincronizar() {
                if (!capaEdicion) {
                    this.state = null;
                } else if (cfg.modo === 'punto') {
                    const { lat, lng } = capaEdicion.getLatLng();
                    this.state = { type: 'Point', coordinates: [G.redondear(lng), G.redondear(lat)] };
                } else {
                    const geo = capaEdicion.toGeoJSON().geometry;
                    this.state = { type: geo.type, coordinates: redondearCoords(geo.coordinates) };
                }
                this.actualizarTextos();
            },

            actualizarTextos() {
                const g = this.state;
                this.tieneGeometria = !!(g && g.type);

                if (!this.tieneGeometria) {
                    this.info = '';
                    this.advertencia = '';
                    return;
                }

                if (g.type === 'Point') {
                    const [lng, lat] = g.coordinates;
                    this.info = `Ubicación: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    this.coordenadas = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                } else {
                    this.info = g.type === 'LineString'
                        ? `Longitud: ${G.formatoLongitud(G.longitudM(g))}`
                        : `Área: ${G.areaHa(g).toLocaleString('es-CO', { maximumFractionDigits: 2 })} ha`;
                }

                const fuera = cfg.entidad !== 'finca' && contornoFinca && !G.estaDentro(g, contornoFinca);
                this.advertencia = fuera
                    ? `⚠ ${{ punto: 'El punto', ruta: 'La ruta' }[cfg.entidad] ?? 'El lote'} queda por fuera del contorno de la finca. Puedes guardarlo igual.`
                    : '';
            },

            borrar() {
                if (capaEdicion) {
                    mapa.removeLayer(capaEdicion);
                    capaEdicion = null;
                }
                this.mensajeGpx = '';
                this.sincronizar();
            },

            /* ---------------- Buscador ---------------- */
            async buscar() {
                const q = this.busqueda.trim();
                if (!q || !mapa) return;

                // ¿Son coordenadas (decimal o GMS)? → ir allá sin buscar
                try {
                    const { lat, lng } = G.leerCoordenadas(q);
                    mapa.setView([lat, lng], 17);
                    return;
                } catch (e) {
                    /* no son coordenadas: buscar el lugar */
                }

                this.buscando = true;
                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&accept-language=es&q=${encodeURIComponent(q)}`;
                    const [lugar] = await (await fetch(url)).json();
                    if (!lugar) {
                        alertaSuave(this, 'No se encontró ese lugar.');
                        return;
                    }
                    const [s, n, o, e] = lugar.boundingbox.map(Number);
                    mapa.fitBounds([[s, o], [n, e]]);
                } catch (e) {
                    alertaSuave(this, 'No se pudo buscar el lugar (¿sin conexión?).');
                } finally {
                    this.buscando = false;
                }
            },
        };
    });

    /* ---------------- Utilidades ---------------- */
    function alertaSuave(ctx, texto) {
        const anterior = ctx.info;
        ctx.info = texto;
        setTimeout(() => {
            if (ctx.info === texto) ctx.info = anterior;
        }, 3500);
    }

    function redondearCoords(c) {
        return Array.isArray(c[0]) ? c.map(redondearCoords) : [G.redondear(c[0]), G.redondear(c[1])];
    }

    function esc(v) {
        return String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
    }
});
