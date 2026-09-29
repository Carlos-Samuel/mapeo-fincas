/*
 * Herramientas geográficas compartidas (admin y sitio público). Sin dependencias.
 * Expone window.GeoHerramientas. Coordenadas GeoJSON: [longitud, latitud].
 *
 *  - leerCoordenadas(texto)  → { lat, lng } | lanza Error con mensaje en español
 *      Acepta decimal («4.15123, -74.88456») y grados/minutos/segundos
 *      («4°09'04.4"N 74°53'04.4"W», «N 4 09 04 O 74 53 04», «4°09.074'N 74°53.07'W»).
 *  - leerGpx(textoXml)      → { tracks: [{nombre, puntos}], rutas: [...], waypoints: [{nombre, lng, lat, descripcion}] }
 *  - geometriaDesdeGpx(gpx, 'poligono'|'linea') → { geometria, origen, original, final } | lanza Error
 *  - simplificar(puntos, toleranciaM) → puntos (Douglas-Peucker en metros)
 *  - areaHa, longitudM, estaDentro (mismas fórmulas que app/Support/Geo.php)
 */
(function (global) {
    'use strict';

    const R = 6378137;
    const rad = (x) => (x * Math.PI) / 180;

    /* ---------------- Coordenadas escritas a mano ---------------- */

    function leerCoordenadas(texto) {
        let limpio = String(texto ?? '')
            .trim()
            .toUpperCase()
            .replace(/[°º˚]/g, ' ')
            .replace(/[′'’]/g, ' ')
            .replace(/[″"”]/g, ' ');

        // Coma decimal («4,15123 -74,88456» o «4,15123; -74,88456»): solo si no hay puntos y los dos
        // números están separados por espacio o punto y coma, para no confundirla con el separador.
        const conComaDecimal = limpio.match(/^([-+]?\d+,\d+)\s*[;\s]\s*([-+]?\d+,\d+)$/);
        if (conComaDecimal && !limpio.includes('.')) {
            limpio = `${conComaDecimal[1].replace(',', '.')} ${conComaDecimal[2].replace(',', '.')}`;
        }

        if (!limpio) throw new Error('Escribe las coordenadas.');

        const tokens = limpio.match(/[-+]?\d+(?:\.\d+)?|[NSEWO]/g) || [];
        if (!tokens.some((t) => /\d/.test(t))) {
            throw new Error('No entiendo el formato. Ejemplos: «4.15123, -74.88456» o «4°09\'04"N 74°53\'04"W».');
        }
        const hayLetras = tokens.some((t) => /^[NSEWO]$/.test(t));
        const grupos = [];

        if (!hayLetras) {
            const nums = tokens.map(Number);
            if (nums.length % 2 !== 0 || nums.length === 0 || nums.length > 6) {
                throw new Error('No entiendo el formato. Ejemplos: «4.15123, -74.88456» o «4°09\'04"N 74°53\'04"W».');
            }
            const mitad = nums.length / 2;
            grupos.push({ nums: nums.slice(0, mitad), letra: null });
            grupos.push({ nums: nums.slice(mitad), letra: null });
        } else {
            const letraPrimero = /^[NSEWO]$/.test(tokens[0]);
            let actual = null;
            for (const t of tokens) {
                const esLetra = /^[NSEWO]$/.test(t);
                if (letraPrimero) {
                    if (esLetra) {
                        actual = { nums: [], letra: t };
                        grupos.push(actual);
                    } else if (actual) actual.nums.push(Number(t));
                } else if (esLetra) {
                    if (!actual) throw new Error('Formato no válido.');
                    actual.letra = t;
                    actual = null;
                } else {
                    if (!actual) {
                        actual = { nums: [], letra: null };
                        grupos.push(actual);
                    }
                    actual.nums.push(Number(t));
                }
            }
        }

        if (grupos.length !== 2 || grupos.some((g) => g.nums.length < 1 || g.nums.length > 3)) {
            throw new Error('Necesito dos coordenadas (latitud y longitud).');
        }

        const valores = grupos.map((g) => {
            const [d, m = 0, s = 0] = g.nums;
            if (m < 0 || m >= 60 || s < 0 || s >= 60) throw new Error('Minutos y segundos deben estar entre 0 y 59.');
            let v = Math.abs(d) + m / 60 + s / 3600;
            if (d < 0 || String(d).startsWith('-') || g.letra === 'S' || g.letra === 'W' || g.letra === 'O') v = -v;
            return { v, letra: g.letra };
        });

        let lat;
        let lng;
        const [a, b] = valores;
        if (a.letra && 'EWO'.includes(a.letra)) {
            lng = a.v;
            lat = b.v;
        } else if (b.letra && 'NS'.includes(b.letra)) {
            lat = b.v;
            lng = a.v;
        } else {
            lat = a.v;
            lng = b.v;
        }

        if (Math.abs(lat) > 90) throw new Error('La latitud debe estar entre -90 y 90 (¿están invertidas?).');
        if (Math.abs(lng) > 180) throw new Error('La longitud debe estar entre -180 y 180.');

        return { lat: redondear(lat), lng: redondear(lng) };
    }

    /* ---------------- GPX ---------------- */

    function leerGpx(texto) {
        const doc = new DOMParser().parseFromString(texto, 'application/xml');
        if (doc.getElementsByTagName('parsererror').length) throw new Error('El archivo no es un GPX válido.');
        if (!porNombre(doc, 'gpx').length) throw new Error('El archivo no parece un GPX (falta la etiqueta <gpx>).');

        const punto = (el) => {
            const lat = parseFloat(el.getAttribute('lat'));
            const lng = parseFloat(el.getAttribute('lon'));
            return Number.isFinite(lat) && Number.isFinite(lng) ? [redondear(lng), redondear(lat)] : null;
        };
        const nombreDe = (el, def) => textoHijo(el, 'name') || def;

        const tracks = porNombre(doc, 'trk').map((trk, i) => ({
            nombre: nombreDe(trk, `Recorrido ${i + 1}`),
            // todos los segmentos seguidos
            puntos: porNombre(trk, 'trkpt').map(punto).filter(Boolean),
        }));
        const rutas = porNombre(doc, 'rte').map((rte, i) => ({
            nombre: nombreDe(rte, `Ruta ${i + 1}`),
            puntos: porNombre(rte, 'rtept').map(punto).filter(Boolean),
        }));
        const waypoints = porNombre(doc, 'wpt')
            .map((w, i) => {
                const p = punto(w);
                return p && { nombre: nombreDe(w, `Punto ${i + 1}`), lng: p[0], lat: p[1], descripcion: textoHijo(w, 'desc') || textoHijo(w, 'cmt') || '' };
            })
            .filter(Boolean);

        return {
            tracks: tracks.filter((t) => t.puntos.length),
            rutas: rutas.filter((r) => r.puntos.length),
            waypoints,
        };
    }

    /**
     * Elige la mejor fuente del GPX (track más largo → ruta más larga → waypoints en orden)
     * y la convierte en Polygon o LineString simplificado.
     */
    function geometriaDesdeGpx(gpx, modo) {
        const masLargo = (lista) => lista.slice().sort((x, y) => y.puntos.length - x.puntos.length)[0];
        let fuente = masLargo(gpx.tracks);
        let origen = fuente && `recorrido «${fuente.nombre}»`;
        if (!fuente) {
            fuente = masLargo(gpx.rutas);
            origen = fuente && `ruta «${fuente.nombre}»`;
        }
        if (!fuente && gpx.waypoints.length) {
            fuente = { puntos: gpx.waypoints.map((w) => [w.lng, w.lat]) };
            origen = `${gpx.waypoints.length} waypoints unidos en orden`;
        }
        if (!fuente) throw new Error('El GPX no trae recorridos, rutas ni waypoints.');

        let puntos = quitarRepetidos(fuente.puntos);
        const original = puntos.length;

        if (modo === 'poligono') {
            if (puntos.length > 2 && distanciaM(puntos[0], puntos[puntos.length - 1]) < 0.5) puntos.pop();
            if (puntos.length < 3) throw new Error('Se necesitan al menos 3 puntos para formar un polígono.');
            puntos = simplificarAMaximo(puntos, 300);
            if (puntos.length < 3) throw new Error('Tras simplificar quedaron menos de 3 puntos.');
            return { geometria: { type: 'Polygon', coordinates: [[...puntos, puntos[0]]] }, origen, original, final: puntos.length };
        }

        if (puntos.length < 2) throw new Error('Se necesitan al menos 2 puntos para formar una línea.');
        puntos = simplificarAMaximo(puntos, 500);
        return { geometria: { type: 'LineString', coordinates: puntos }, origen, original, final: puntos.length };
    }

    /* ---------------- Simplificación (Douglas-Peucker en metros) ---------------- */

    function simplificarAMaximo(puntos, maximo) {
        let tolerancia = 0.5; // metros: elimina ruido del GPS sin deformar
        let res = simplificar(puntos, tolerancia);
        while (res.length > maximo && tolerancia < 50) {
            tolerancia *= 1.6;
            res = simplificar(puntos, tolerancia);
        }
        return res;
    }

    function simplificar(puntos, toleranciaM) {
        if (puntos.length <= 2) return puntos.slice();
        const lat0 = rad(puntos.reduce((s, p) => s + p[1], 0) / puntos.length);
        const xy = puntos.map(([lng, lat]) => [rad(lng) * R * Math.cos(lat0), rad(lat) * R]);
        const conservar = new Uint8Array(puntos.length);
        conservar[0] = conservar[puntos.length - 1] = 1;
        const pila = [[0, puntos.length - 1]];
        while (pila.length) {
            const [i, j] = pila.pop();
            let maxD = 0;
            let idx = -1;
            for (let k = i + 1; k < j; k++) {
                const d = distanciaSegmento(xy[k], xy[i], xy[j]);
                if (d > maxD) {
                    maxD = d;
                    idx = k;
                }
            }
            if (idx !== -1 && maxD > toleranciaM) {
                conservar[idx] = 1;
                pila.push([i, idx], [idx, j]);
            }
        }
        return puntos.filter((_, k) => conservar[k]);
    }

    function distanciaSegmento(p, a, b) {
        const dx = b[0] - a[0];
        const dy = b[1] - a[1];
        const largo2 = dx * dx + dy * dy;
        let t = largo2 ? ((p[0] - a[0]) * dx + (p[1] - a[1]) * dy) / largo2 : 0;
        t = Math.max(0, Math.min(1, t));
        return Math.hypot(p[0] - (a[0] + t * dx), p[1] - (a[1] + t * dy));
    }

    /* ---------------- Medidas (mismas fórmulas que Geo.php) ---------------- */

    function anillosDe(g) {
        if (!g) return [];
        if (g.type === 'Polygon') return [g.coordinates];
        if (g.type === 'MultiPolygon') return g.coordinates;
        return [];
    }

    function areaHa(g) {
        const anillo = (pts) => {
            let s = 0;
            for (let i = 0; i < pts.length - 1; i++) {
                const [x1, y1] = pts[i];
                const [x2, y2] = pts[i + 1];
                s += rad(x2 - x1) * (2 + Math.sin(rad(y1)) + Math.sin(rad(y2)));
            }
            return Math.abs((s * R * R) / 2);
        };
        let total = 0;
        anillosDe(g).forEach((anillos) => anillos.forEach((a, i) => (total += i === 0 ? anillo(a) : -anillo(a))));
        return total / 10000;
    }

    function distanciaM([lng1, lat1], [lng2, lat2]) {
        const a = Math.sin(rad(lat2 - lat1) / 2) ** 2 + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(rad(lng2 - lng1) / 2) ** 2;
        return 2 * R * Math.asin(Math.min(1, Math.sqrt(a)));
    }

    function longitudM(g) {
        let total = 0;
        for (let i = 0; i < g.coordinates.length - 1; i++) total += distanciaM(g.coordinates[i], g.coordinates[i + 1]);
        return total;
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

    /* ---------------- Utilidades ---------------- */

    function redondear(n) {
        return Math.round(n * 1e7) / 1e7;
    }

    function quitarRepetidos(puntos) {
        return puntos.filter((p, i) => i === 0 || p[0] !== puntos[i - 1][0] || p[1] !== puntos[i - 1][1]);
    }

    // Busca por nombre local ignorando el namespace (GPX 1.0 y 1.1).
    function porNombre(raiz, nombre) {
        return Array.from(raiz.getElementsByTagNameNS('*', nombre));
    }

    function textoHijo(el, nombre) {
        const hijo = Array.from(el.children).find((c) => c.localName === nombre);
        return hijo ? hijo.textContent.trim() : '';
    }

    function formatoLongitud(m) {
        return m >= 1000
            ? `${(m / 1000).toLocaleString('es-CO', { maximumFractionDigits: 2 })} km`
            : `${Math.round(m).toLocaleString('es-CO')} m`;
    }

    global.GeoHerramientas = {
        leerCoordenadas,
        leerGpx,
        geometriaDesdeGpx,
        simplificar,
        areaHa,
        longitudM,
        distanciaM,
        estaDentro,
        formatoLongitud,
        redondear,
    };
})(window);
