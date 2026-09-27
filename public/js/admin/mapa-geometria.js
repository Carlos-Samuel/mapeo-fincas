/*
 * Campo "MapaGeometria" del admin (Filament).
 * Dibuja/edita un polígono (fincas, lotes), una línea (rutas) o ubica un punto (puntos) y lo guarda como GeoJSON.
 * Requiere Leaflet y Leaflet-Geoman cargados antes (ver resources/views/filament/mapa-assets.blade.php).
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('mapaGeometria', (cfg) => {
        // Objetos de Leaflet fuera del estado reactivo de Alpine (los proxies los rompen).
        let mapa = null;
        let capaEdicion = null;   // polígono o marcador que se está editando
        let capaRef = null;       // finca, lotes y puntos de referencia
        let contornoFinca = null; // GeoJSON del contorno para validar
        let peticion = 0;

        const COLOR_EDICION = '#16a34a';

        return {
            state: cfg.state,
            fincaId: cfg.fincaId,
            busqueda: '',
            buscando: false,
            tieneGeometria: false,
            info: '',
            advertencia: '',
            ayuda: '',

            init() {
                this.ayuda = {
                    punto: 'Haz clic en el mapa para ubicar el punto. Puedes arrastrar el marcador para moverlo.',
                    linea: 'Usa «Dibujar línea» (izquierda): clic en cada vértice y clic en el último punto para terminar. Luego puedes editar vértices o mover la línea.',
                    poligono: 'Usa las herramientas de la izquierda para dibujar (polígono o rectángulo), editar vértices o mover la forma. Los lotes existentes se muestran como referencia.',
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
                mapa = L.map(this.$refs.mapa, { maxZoom: 21 }).setView([4.6, -74.1], 6);

                const satelite = L.layerGroup([
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 21,
                        maxNativeZoom: 19,
                        attribution: 'Imágenes &copy; Esri',
                    }),
                    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 21,
                        maxNativeZoom: 19,
                    }),
                ]).addTo(mapa);
                const calles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 21,
                    maxNativeZoom: 19,
                    attribution: '&copy; OpenStreetMap',
                });
                L.control.layers({ 'Satélite': satelite, 'Mapa': calles }, null, { position: 'topright' }).addTo(mapa);
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
                    mapa.pm.setPathOptions({ color: COLOR_EDICION, fillColor: COLOR_EDICION, fillOpacity: 0.3, weight: cfg.modo === 'linea' ? 5 : 3 });

                    mapa.on('pm:create', (e) => {
                        // Solo una forma: la nueva reemplaza a la anterior.
                        if (capaEdicion) mapa.removeLayer(capaEdicion);
                        capaEdicion = e.layer;
                        this.escucharEdicion(capaEdicion);
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
                    capaEdicion = L.geoJSON(g, {
                        style: { color: COLOR_EDICION, fillColor: COLOR_EDICION, fillOpacity: 0.3, weight: cfg.modo === 'linea' ? 5 : 3 },
                    }).getLayers()[0];
                    capaEdicion.addTo(mapa);
                    this.escucharEdicion(capaEdicion);
                    mapa.fitBounds(capaEdicion.getBounds(), { padding: [30, 30] });
                }
                this.actualizarTextos();
            },

            /* ---------------- Referencias (finca, otros lotes y puntos) ---------------- */
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
                    this.state = { type: 'Point', coordinates: [redondear(lng), redondear(lat)] };
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
                } else {
                    this.info = g.type === 'LineString'
                        ? `Longitud: ${formatoLongitud(longitudM(g))}`
                        : `Área: ${areaHa(g).toLocaleString('es-CO', { maximumFractionDigits: 2 })} ha`;
                }

                const fuera = cfg.entidad !== 'finca' && contornoFinca && !estaDentro(g, contornoFinca);
                this.advertencia = fuera
                    ? `⚠ ${{ punto: 'El punto', ruta: 'La ruta' }[cfg.entidad] ?? 'El lote'} queda por fuera del contorno de la finca. Puedes guardarlo igual.`
                    : '';
            },

            borrar() {
                if (capaEdicion) {
                    mapa.removeLayer(capaEdicion);
                    capaEdicion = null;
                }
                this.sincronizar();
            },

            /* ---------------- Buscador ---------------- */
            async buscar() {
                const q = this.busqueda.trim();
                if (!q || !mapa) return;

                const coords = q.match(/^\s*(-?\d+(?:\.\d+)?)\s*[,;\s]\s*(-?\d+(?:\.\d+)?)\s*$/);
                if (coords) {
                    mapa.setView([parseFloat(coords[1]), parseFloat(coords[2])], 17);
                    return;
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

    function redondear(n) {
        return Math.round(n * 1e7) / 1e7;
    }

    function redondearCoords(c) {
        return Array.isArray(c[0]) ? c.map(redondearCoords) : [redondear(c[0]), redondear(c[1])];
    }

    function anillosDe(g) {
        if (!g) return [];
        if (g.type === 'Polygon') return [g.coordinates];
        if (g.type === 'MultiPolygon') return g.coordinates;
        return [];
    }

    function areaHa(g) {
        const R = 6378137;
        const anillo = (pts) => {
            let s = 0;
            for (let i = 0; i < pts.length - 1; i++) {
                const [x1, y1] = pts[i];
                const [x2, y2] = pts[i + 1];
                s += ((x2 - x1) * Math.PI / 180) * (2 + Math.sin(y1 * Math.PI / 180) + Math.sin(y2 * Math.PI / 180));
            }
            return Math.abs((s * R * R) / 2);
        };
        let total = 0;
        anillosDe(g).forEach((anillos) => anillos.forEach((a, i) => (total += i === 0 ? anillo(a) : -anillo(a))));
        return total / 10000;
    }

    function longitudM(g) {
        const R = 6378137;
        const rad = (x) => (x * Math.PI) / 180;
        let total = 0;
        for (let i = 0; i < g.coordinates.length - 1; i++) {
            const [lng1, lat1] = g.coordinates[i];
            const [lng2, lat2] = g.coordinates[i + 1];
            const a = Math.sin(rad(lat2 - lat1) / 2) ** 2 + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(rad(lng2 - lng1) / 2) ** 2;
            total += 2 * R * Math.asin(Math.min(1, Math.sqrt(a)));
        }
        return total;
    }

    function formatoLongitud(m) {
        return m >= 1000
            ? `${(m / 1000).toLocaleString('es-CO', { maximumFractionDigits: 2 })} km`
            : `${Math.round(m).toLocaleString('es-CO')} m`;
    }

    function enAnillo([x, y], anillo) {
        let dentro = false;
        for (let i = 0, j = anillo.length - 1; i < anillo.length; j = i++) {
            const [xi, yi] = anillo[i];
            const [xj, yj] = anillo[j];
            if (yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi || 1e-12) + xi) dentro = !dentro;
        }
        return dentro;
    }

    function puntoDentro(p, contorno) {
        return anillosDe(contorno).some((anillos) => enAnillo(p, anillos[0]) && !anillos.slice(1).some((h) => enAnillo(p, h)));
    }

    function estaDentro(g, contorno) {
        const vertices = g.type === 'Point' ? [g.coordinates] : g.type === 'LineString' ? g.coordinates : anillosDe(g).flatMap((a) => a[0]);
        return vertices.every((v) => puntoDentro(v, contorno));
    }

    function esc(v) {
        return String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
    }
});
