/*
 * Mapa público de una finca.
 * - Pasa el cursor: ícono del tipo junto al puntero.
 * - Clic: el panel derecho carga la descripción (HTML generado por Laravel).
 * - Capas por tipo para prender/apagar. Enlaces directos con ?lote=, ?punto=, ?ruta= o ?recorrido=.
 * - Recorridos: se dibujan al seleccionarlos y se siguen parada por parada desde el panel.
 * Requiere Leaflet cargado antes (CDN en fincas/show.blade.php).
 */
(() => {
    const panel = document.getElementById('panel');
    if (!panel) return;

    const URL_DATOS = panel.dataset.urlDatos;
    const URL_PANEL = {
        lote: panel.dataset.urlPanelLote,
        punto: panel.dataset.urlPanelPunto,
        ruta: panel.dataset.urlPanelRuta,
        recorrido: panel.dataset.urlPanelRecorrido,
    };
    const CLASES = ['lote', 'punto', 'ruta', 'recorrido'];
    const HTML_RESUMEN = panel.innerHTML; // resumen de la finca ya renderizado por el servidor

    const ESTILO_BASE = { weight: 2, opacity: 1, fillOpacity: 0.3 };
    const ESTILO_HOVER = { weight: 3, fillOpacity: 0.55 };
    const ESTILO_SELECCION = { weight: 4, fillOpacity: 0.6, color: '#ffffff' };
    const RUTA_HOVER = { weight: 7, opacity: 1 };
    const RUTA_SELECCION = { weight: 8, opacity: 1 };

    /* ---------------- Mapa base ---------------- */
    const mapa = L.map('mapa', { maxZoom: 21 }).setView([4.6, -74.1], 6);

    // Satélite (Esri), satélite nítido (Esri Clarity) y mapa de calles: ver public/js/capas-base.js
    const { bases } = CapasBase.agregar(mapa, { conControl: false });
    L.control.scale({ imperial: false }).addTo(mapa);

    /* ---------------- Estado ---------------- */
    // id -> capa Leaflet. Los recorridos son grupos que solo se muestran al seleccionarlos.
    const capas = { lote: new Map(), punto: new Map(), ruta: new Map(), recorrido: new Map() };
    let seleccion = null;                                // { clase, id }
    let peticion = 0;

    cargar();

    async function cargar() {
        let datos;
        try {
            const r = await fetch(URL_DATOS, { headers: { Accept: 'application/json' } });
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            datos = await r.json();
        } catch (e) {
            console.error(e);
            panel.insertAdjacentHTML('afterbegin', '<p class="panel__error">No se pudo cargar el mapa de la finca.</p>');
            return;
        }

        // Una capa por tipo (más "Sin tipo") para el control de capas.
        const grupos = new Map();
        const grupoDe = (tipoId) => {
            const clave = tipoId ?? 'sin-tipo';
            if (!grupos.has(clave)) grupos.set(clave, L.featureGroup().addTo(mapa));
            return grupos.get(clave);
        };

        // Contorno de la finca
        let contorno = null;
        if (datos.finca.geometria) {
            contorno = L.geoJSON(datos.finca.geometria, {
                style: { color: '#ffffff', weight: 3, dashArray: '8 6', fill: false },
                interactive: false,
            }).addTo(mapa);
        }

        // Lotes
        L.geoJSON(datos.lotes, {
            style: (f) => estiloLote(f.properties),
            onEachFeature: (f, capa) => {
                const p = f.properties;
                capas.lote.set(p.id, capa);
                capa.bindTooltip(htmlTooltip(p), { sticky: true, direction: 'top', offset: [0, -14], className: 'tooltip-mapa', opacity: 1 });
                capa.on('mouseover', () => {
                    if (!esSeleccion('lote', p.id)) capa.setStyle(ESTILO_HOVER);
                });
                capa.on('mouseout', () => {
                    if (!esSeleccion('lote', p.id)) capa.setStyle(estiloLote(p));
                });
                capa.on('click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    seleccionar('lote', p.id);
                });
                grupoDe(p.tipo_id).addLayer(capa);
            },
        });

        // Puntos
        datos.puntos.features.forEach((f) => {
            const p = f.properties;
            const [lng, lat] = f.geometry.coordinates;
            const marcador = L.marker([lat, lng], { icon: iconoPunto(p), riseOnHover: true, keyboard: true, title: p.nombre });
            marcador.feature = f;
            marcador.bindTooltip(htmlTooltip(p), { direction: 'top', offset: [0, -20], className: 'tooltip-mapa', opacity: 1 });
            marcador.on('click', (e) => {
                L.DomEvent.stopPropagation(e);
                seleccionar('punto', p.id);
            });
            capas.punto.set(p.id, marcador);
            grupoDe(p.tipo_id).addLayer(marcador);
        });

        // Rutas (líneas)
        L.geoJSON(datos.rutas ?? { type: 'FeatureCollection', features: [] }, {
            style: (f) => estiloRuta(f.properties),
            onEachFeature: (f, capa) => {
                const p = f.properties;
                capas.ruta.set(p.id, capa);
                capa.bindTooltip(htmlTooltip(p), { sticky: true, direction: 'top', offset: [0, -14], className: 'tooltip-mapa', opacity: 1 });
                capa.on('mouseover', () => {
                    if (!esSeleccion('ruta', p.id)) capa.setStyle(RUTA_HOVER);
                });
                capa.on('mouseout', () => {
                    if (!esSeleccion('ruta', p.id)) capa.setStyle(estiloRuta(p));
                });
                capa.on('click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    seleccionar('ruta', p.id);
                });
                grupoDe(p.tipo_id).addLayer(capa);
            },
        });

        // Recorridos: línea que une sus paradas + marcas numeradas (se crean ocultos)
        (datos.recorridos ?? []).forEach((r) => {
            const coords = r.paradas.map((id) => capas.punto.get(id)?.getLatLng()).filter(Boolean);
            if (coords.length < 2) return;
            const grupo = L.featureGroup([
                L.polyline(coords, { color: '#ffffff', weight: 8, opacity: 0.7, interactive: false }),
                L.polyline(coords, { color: r.color, weight: 4, dashArray: '10 8', interactive: false }),
                ...coords.map((ll, i) =>
                    L.marker(ll, {
                        interactive: false,
                        zIndexOffset: 1000,
                        icon: L.divIcon({
                            className: 'numero-parada',
                            html: `<span style="background:${esc(r.color)}">${i + 1}</span>`,
                            iconSize: [22, 22],
                            iconAnchor: [-6, 28],
                        }),
                    }),
                ),
            ]);
            grupo.recorrido = r;
            capas.recorrido.set(r.id, grupo);
        });

        // Control de capas: satélite/mapa + un interruptor por tipo
        const overlays = {};
        datos.tipos.forEach((t) => {
            if (grupos.has(t.id)) overlays[etiquetaCapa(t.nombre, t.icono, t.color)] = grupos.get(t.id);
        });
        if (grupos.has('sin-tipo')) overlays[etiquetaCapa('Sin tipo', null, '#9e9e9e')] = grupos.get('sin-tipo');
        L.control.layers(bases, overlays, { position: 'topright', collapsed: window.innerWidth < 900 || Object.keys(overlays).length > 8 }).addTo(mapa);

        // Encuadre inicial
        const limites = L.latLngBounds([]);
        if (contorno) limites.extend(contorno.getBounds());
        grupos.forEach((g) => g.getLayers().length && limites.extend(g.getBounds()));
        if (limites.isValid()) mapa.fitBounds(limites, { padding: [30, 30] });

        // Enlace directo ?lote=ID / ?punto=ID / ?ruta=ID / ?recorrido=ID
        const params = new URLSearchParams(location.search);
        for (const clase of CLASES) {
            const id = Number(params.get(clase));
            if (id && capas[clase].has(id)) {
                seleccionar(clase, id, { centrar: true, actualizarUrl: false });
                break;
            }
        }
    }

    /* ---------------- Selección ---------------- */
    function esSeleccion(clase, id) {
        return seleccion && seleccion.clase === clase && seleccion.id === id;
    }

    function desmarcar() {
        if (!seleccion) return;
        const capa = capas[seleccion.clase].get(seleccion.id);
        if (!capa) return;
        if (seleccion.clase === 'lote') capa.setStyle(estiloLote(capa.feature.properties));
        else if (seleccion.clase === 'ruta') capa.setStyle(estiloRuta(capa.feature.properties));
        else if (seleccion.clase === 'recorrido') {
            mapa.removeLayer(capa);
            marcarParada(null);
        } else capa.getElement()?.classList.remove('marcador--seleccionado');
    }

    async function seleccionar(clase, id, { centrar = false, actualizarUrl = true } = {}) {
        desmarcar();
        seleccion = { clase, id };

        const capa = capas[clase].get(id);
        if (capa) {
            if (clase === 'lote' || clase === 'ruta') {
                capa.setStyle(clase === 'lote' ? ESTILO_SELECCION : RUTA_SELECCION);
                capa.bringToFront();
                if (centrar) mapa.flyToBounds(capa.getBounds(), { padding: [60, 60], maxZoom: 19, duration: 0.6 });
            } else if (clase === 'recorrido') {
                capa.addTo(mapa);
                mapa.flyToBounds(capa.getBounds(), { padding: [60, 60], maxZoom: 19, duration: 0.6 });
            } else {
                capa.getElement()?.classList.add('marcador--seleccionado');
                if (centrar) mapa.flyTo(capa.getLatLng(), Math.max(mapa.getZoom(), 18), { duration: 0.6 });
            }
        }

        if (actualizarUrl) cambiarUrl(clase, id);

        const ticket = ++peticion;
        panel.innerHTML = '<p class="panel__vacio">Cargando…</p>';
        try {
            const r = await fetch(`${URL_PANEL[clase]}/${id}`, { headers: { Accept: 'text/html' } });
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            const html = await r.text();
            if (ticket !== peticion) return;
            panel.innerHTML = html;
            panel.scrollTop = 0;
            if (clase === 'recorrido') irAParada(0, { mover: false });
            if (window.matchMedia('(max-width: 900px)').matches) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (e) {
            console.error(e);
            if (ticket !== peticion) return;
            panel.innerHTML = '<button class="panel__volver" type="button" data-accion="volver">← Volver</button><p class="panel__error">No se pudo cargar la información.</p>';
        }
    }

    function limpiarSeleccion() {
        if (!seleccion) return;
        desmarcar();
        seleccion = null;
        peticion++;
        panel.innerHTML = HTML_RESUMEN;
        panel.scrollTop = 0;
        cambiarUrl(null);
    }

    function cambiarUrl(clase, id) {
        const url = new URL(location.href);
        CLASES.forEach((c) => url.searchParams.delete(c));
        if (clase) url.searchParams.set(clase, id);
        history.replaceState(null, '', url);
    }

    mapa.on('click', limpiarSeleccion);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') limpiarSeleccion();
    });

    /* ---------------- Eventos del panel (delegados) ---------------- */
    panel.addEventListener('click', (e) => {
        const el = e.target.closest('[data-accion]');
        if (!el) return;

        switch (el.dataset.accion) {
            case 'volver':
                limpiarSeleccion();
                break;
            case 'ver':
                seleccionar(el.dataset.clase, Number(el.dataset.id), { centrar: true });
                break;
            case 'paso':
                irAParada(pasoActual() + Number(el.dataset.dir));
                break;
            case 'ir-paso':
                irAParada(Number(el.dataset.paso));
                break;
            case 'miniatura': {
                // Puede haber varias galerías en el panel (recorridos): usar la del botón.
                const galeria = el.closest('.galeria');
                const img = galeria.querySelector('.galeria__principal img');
                const pie = galeria.querySelector('.galeria__principal figcaption');
                img.src = el.dataset.src;
                img.alt = el.dataset.desc || '';
                if (pie) {
                    pie.textContent = el.dataset.desc || '';
                    pie.classList.toggle('oculto', !el.dataset.desc);
                }
                galeria.querySelectorAll('.galeria__mini').forEach((b) => b.classList.toggle('activa', b === el));
                break;
            }
        }
    });

    // Resaltar en el mapa al pasar el cursor por la lista del resumen.
    panel.addEventListener('mouseover', (e) => resaltarDesdeLista(e, true));
    panel.addEventListener('mouseout', (e) => resaltarDesdeLista(e, false));

    function resaltarDesdeLista(e, activo) {
        const item = e.target.closest('[data-accion="ver"]');
        if (!item) return;
        const clase = item.dataset.clase;
        const id = Number(item.dataset.id);
        const capa = capas[clase]?.get(id);
        if (!capa || esSeleccion(clase, id)) return;
        if (clase === 'lote') capa.setStyle(activo ? ESTILO_HOVER : estiloLote(capa.feature.properties));
        else if (clase === 'ruta') capa.setStyle(activo ? RUTA_HOVER : estiloRuta(capa.feature.properties));
        else if (clase === 'punto') capa.getElement()?.classList.toggle('marcador--hover', activo);
    }

    /* ---------------- Recorridos: paso a paso ---------------- */
    function pasoActual() {
        const visible = panel.querySelector('.paso:not([hidden])');
        return visible ? Number(visible.dataset.paso) : 0;
    }

    function irAParada(indice, { mover = true } = {}) {
        const pasos = [...panel.querySelectorAll('.paso')];
        if (!pasos.length) return;
        const i = Math.max(0, Math.min(indice, pasos.length - 1));

        pasos.forEach((p, j) => (p.hidden = j !== i));
        const actual = panel.querySelector('[data-rol="actual"]');
        if (actual) actual.textContent = i + 1;
        panel.querySelector('[data-accion="paso"][data-dir="-1"]')?.toggleAttribute('disabled', i === 0);
        panel.querySelector('[data-accion="paso"][data-dir="1"]')?.toggleAttribute('disabled', i === pasos.length - 1);
        panel.querySelectorAll('[data-accion="ir-paso"]').forEach((b) => b.classList.toggle('activo', Number(b.dataset.paso) === i));

        const marcador = capas.punto.get(Number(pasos[i].dataset.punto));
        marcarParada(marcador);
        if (mover && marcador) {
            mapa.flyTo(marcador.getLatLng(), Math.max(mapa.getZoom(), 18), { duration: 0.6 });
            pasos[i].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    let paradaMarcada = null;
    function marcarParada(marcador) {
        paradaMarcada?.getElement()?.classList.remove('marcador--seleccionado');
        paradaMarcada = marcador ?? null;
        paradaMarcada?.getElement()?.classList.add('marcador--seleccionado');
    }

    /* ---------------- Utilidades ---------------- */
    function estiloLote(p) {
        return { ...ESTILO_BASE, color: p.color, fillColor: p.color };
    }

    function estiloRuta(p) {
        return { color: p.color, weight: 4, opacity: 0.9, lineCap: 'round', lineJoin: 'round' };
    }

    function iconoPunto(p) {
        const contenido = p.icono ? `<img src="${esc(p.icono)}" alt="">` : '';
        return L.divIcon({
            className: 'marcador',
            html: `<span class="marcador__circulo" style="--color:${esc(p.color)}">${contenido}</span>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        });
    }

    function htmlTooltip(p) {
        return `${p.icono ? `<img src="${esc(p.icono)}" alt="" width="36" height="36">` : ''}
            <span><strong>${esc(p.tipo ?? 'Sin tipo')}</strong><small>${esc(p.nombre)}</small></span>`;
    }

    function etiquetaCapa(nombre, icono, color) {
        const marca = icono
            ? `<img src="${esc(icono)}" alt="" width="18" height="18">`
            : `<span class="punto-color" style="background:${esc(color)}"></span>`;
        return `<span class="capa-etiqueta">${marca}${esc(nombre)}</span>`;
    }

    function esc(v) {
        return String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
    }
})();
