// MapLibre GL (WebGL) o'rniga — WebGL ba'zi kompyuterlarda (masalan uskunaviy
// tezlashtirish o'chirilgan yoki cheklangan korporativ/institut muhitlarida)
// umuman ishlamasligi tasdiqlandi: xarita konteyneri to'g'ri o'lchamda
// yaratiladi, lekin hech qachon chizilmaydi (WebGL kontekst yoki uning render
// tsikli — requestAnimationFrame — muzlab qoladi). SVG esa oddiy DOM elementi
// sifatida darhol, sinxron tarzda chiziladi — GPU/WebGL'ga hech qanday
// bog'liqlik yo'q, shuning uchun BARCHA brauzer/muhitlarda ishlaydi.
//
// Bu modul MapLibre'ning kichik bir qismini ataylab taqlid qiladi
// (`addSource`/`addLayer`/`on`/`fitBounds`/`flyTo`) — shu orqali xarita
// chaqiruvchi kod (`turkestan-map.js`, `uzgolon-map.js` va h.k.) deyarli
// o'zgarishsiz qoladi, faqat "dvigatel" almashtiriladi.

const SVG_NS = 'http://www.w3.org/2000/svg';

function resolve(value, props) {
    return typeof value === 'function' ? value(props ?? {}) : value;
}

function pointsToSegment(coords, project, close) {
    const parts = coords.map(([lng, lat], i) => {
        const [x, y] = project(lng, lat);
        return `${i === 0 ? 'M' : 'L'}${x.toFixed(2)},${y.toFixed(2)}`;
    });
    return parts.join(' ') + (close ? ' Z' : '');
}

function geometryToPath(geometry, project) {
    if (!geometry) return '';

    switch (geometry.type) {
        case 'Polygon':
            return geometry.coordinates.map((ring) => pointsToSegment(ring, project, true)).join(' ');
        case 'MultiPolygon':
            return geometry.coordinates
                .map((polygon) => polygon.map((ring) => pointsToSegment(ring, project, true)).join(' '))
                .join(' ');
        case 'LineString':
            return pointsToSegment(geometry.coordinates, project, false);
        case 'MultiLineString':
            return geometry.coordinates.map((line) => pointsToSegment(line, project, false)).join(' ');
        default:
            return '';
    }
}

function featuresOf(source) {
    if (!source) return [];
    if (source.type === 'FeatureCollection') return source.features ?? [];
    if (source.type === 'Feature') return [source];
    if (source.type) return [{ type: 'Feature', properties: {}, geometry: source }];
    return [];
}

function forEachCoordinate(geometry, cb) {
    if (!geometry?.coordinates) return;

    const walk = (coords) => {
        if (typeof coords[0] === 'number') {
            cb(coords);
        } else {
            coords.forEach(walk);
        }
    };

    walk(geometry.coordinates);
}

export class SvgMap {
    /**
     * @param {string} containerId
     * @param {{bounds: [[number,number],[number,number]], attribution?: string}} options
     */
    constructor(containerId, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) throw new Error(`Konteyner topilmadi: #${containerId}`);

        this.container = container;
        this.container.classList.add('svg-map');

        this.svg = document.createElementNS(SVG_NS, 'svg');
        this.svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        this.container.appendChild(this.svg);

        this._sources = {};
        this._layers = {};
        this._layerOrder = [];
        this._listeners = { load: [], error: [] };

        this._width = 1000;
        this._height = 600;
        this._baseBounds = options.bounds ?? [[-180, -85], [180, 85]];
        this._interactive = options.interactive !== false;
        this._popupRepositioners = [];

        this._render();

        if (this._interactive) {
            this._attachInteractions();
            this._addZoomControls();
        }

        if (options.attribution) {
            const attr = document.createElement('div');
            attr.className = 'svg-map-attribution';
            attr.textContent = options.attribution;
            this.container.appendChild(attr);
        }

        // MapLibre bilan bir xil "asinxron load" xatti-harakatini saqlash —
        // chaqiruvchi kod `map.on('load', ...)` ichida qatlam qo'shishga
        // odatlangan, shuning uchun bir tick keyin fire qilamiz.
        setTimeout(() => this._listeners.load.forEach((cb) => cb()), 0);
    }

    // --- Proyeksiya -------------------------------------------------------

    _project(lng, lat) {
        const { west, north, cosLat, scale, offsetX, offsetY } = this._projection;
        const x = offsetX + (lng - west) * cosLat * scale;
        const y = offsetY + (north - lat) * scale;
        return [x, y];
    }

    _computeFitView(bounds, padding) {
        const [[west, south], [east, north]] = bounds;
        const midLatRad = ((north + south) / 2) * Math.PI / 180;
        const cosLat = Math.max(Math.cos(midLatRad), 0.1);

        const lngSpan = Math.max((east - west) * cosLat, 0.0001);
        const latSpan = Math.max(north - south, 0.0001);

        const innerW = this._width - padding * 2;
        const innerH = this._height - padding * 2;
        const scale = Math.min(innerW / lngSpan, innerH / latSpan);

        const usedW = lngSpan * scale;
        const usedH = latSpan * scale;
        const offsetX = padding + (innerW - usedW) / 2;
        const offsetY = padding + (innerH - usedH) / 2;

        return { west, north, cosLat, scale, offsetX, offsetY, bounds };
    }

    _applyProjection(projection) {
        this._projection = projection;
        this._redrawAll();
        this._popupRepositioners.forEach((fn) => fn());
    }

    // --- Manba/qatlam -------------------------------------------------------

    addSource(id, definition) {
        const entry = { definition, data: null };
        this._sources[id] = entry;

        if (definition.type === 'geojson') {
            if (typeof definition.data === 'string') {
                fetch(definition.data)
                    .then((r) => r.json())
                    .then((json) => {
                        entry.data = json;
                        this._redrawLayersForSource(id);
                    })
                    .catch((e) => this._listeners.error.forEach((cb) => cb({ error: e })));
            } else {
                entry.data = definition.data;
            }
        }

        return this;
    }

    getSource(id) {
        return this._sources[id] ? { setData: (data) => { this._sources[id].data = data; this._redrawLayersForSource(id); } } : undefined;
    }

    addLayer(layer) {
        this._layers[layer.id] = layer;
        this._layerOrder.push(layer.id);

        const group = document.createElementNS(SVG_NS, 'g');
        group.setAttribute('id', layer.id);
        group.setAttribute('data-layer-type', layer.type);
        if (layer.layout?.visibility === 'none') group.style.display = 'none';
        this.svg.appendChild(group);
        layer._group = group;

        this._drawLayer(layer);

        return this;
    }

    getLayer(id) {
        return this._layers[id];
    }

    removeLayer(id) {
        const layer = this._layers[id];
        if (layer?._group) layer._group.remove();
        delete this._layers[id];
        this._layerOrder = this._layerOrder.filter((l) => l !== id);
    }

    setLayoutProperty(id, prop, value) {
        const layer = this._layers[id];
        if (!layer?._group) return;
        if (prop === 'visibility') layer._group.style.display = value === 'none' ? 'none' : '';
    }

    _redrawLayersForSource(sourceId) {
        Object.values(this._layers)
            .filter((layer) => layer.source === sourceId)
            .forEach((layer) => this._drawLayer(layer));
    }

    _redrawAll() {
        this._layerOrder.forEach((id) => this._drawLayer(this._layers[id]));
    }

    _drawLayer(layer) {
        if (!layer?._group || !this._projection) return;
        const group = layer._group;
        group.innerHTML = '';

        if (layer.type === 'background') {
            const rect = document.createElementNS(SVG_NS, 'rect');
            rect.setAttribute('x', '0');
            rect.setAttribute('y', '0');
            rect.setAttribute('width', String(this._width));
            rect.setAttribute('height', String(this._height));
            rect.setAttribute('fill', resolve(layer.paint?.['background-color'], {}) ?? '#FBF6EC');
            group.appendChild(rect);
            return;
        }

        if (layer.type === 'raster') {
            const src = this._sources[layer.source]?.definition;
            if (!src || src.type !== 'image') return;

            const coords = src.coordinates; // [NW, NE, SE, SW]
            const [nwX, nwY] = this._project(coords[0][0], coords[0][1]);
            const [seX, seY] = this._project(coords[2][0], coords[2][1]);

            const img = document.createElementNS(SVG_NS, 'image');
            img.setAttributeNS('http://www.w3.org/1999/xlink', 'href', src.url);
            img.setAttribute('href', src.url);
            img.setAttribute('x', Math.min(nwX, seX).toFixed(2));
            img.setAttribute('y', Math.min(nwY, seY).toFixed(2));
            img.setAttribute('width', Math.abs(seX - nwX).toFixed(2));
            img.setAttribute('height', Math.abs(seY - nwY).toFixed(2));
            img.setAttribute('opacity', String(resolve(layer.paint?.['raster-opacity'], {}) ?? 1));
            img.setAttribute('preserveAspectRatio', 'none');
            group.appendChild(img);
            return;
        }

        const features = featuresOf(this._sources[layer.source]?.data);

        features.forEach((feature) => {
            const props = feature.properties ?? {};

            if (layer.filter) {
                const passes = typeof layer.filter === 'function'
                    ? layer.filter(feature)
                    : this._matchesFilter(layer.filter, feature);
                if (!passes) return;
            }

            if (layer.type === 'fill' || layer.type === 'line') {
                const geometryType = feature.geometry?.type ?? '';
                const isPolygonish = geometryType.includes('Polygon');
                if (layer.type === 'fill' && !isPolygonish) return;

                const d = geometryToPath(feature.geometry, (lng, lat) => this._project(lng, lat));
                if (!d) return;

                const path = document.createElementNS(SVG_NS, 'path');
                path.setAttribute('d', d);
                path.setAttribute('vector-effect', 'non-scaling-stroke');

                if (layer.type === 'fill') {
                    path.setAttribute('fill', resolve(layer.paint?.['fill-color'], props) ?? '#EFE4CE');
                    path.setAttribute('fill-opacity', String(resolve(layer.paint?.['fill-opacity'], props) ?? 1));
                    path.setAttribute('stroke', 'none');
                } else {
                    path.setAttribute('fill', 'none');
                    path.setAttribute('stroke', resolve(layer.paint?.['line-color'], props) ?? '#6B4A32');
                    path.setAttribute('stroke-width', String(resolve(layer.paint?.['line-width'], props) ?? 1));
                    const dash = resolve(layer.paint?.['line-dasharray'], props);
                    if (dash) path.setAttribute('stroke-dasharray', Array.isArray(dash) ? dash.join(',') : dash);
                }

                path.dataset.featureIndex = String(features.indexOf(feature));
                group.appendChild(path);
            }

            if (layer.type === 'circle') {
                const featureIndex = String(features.indexOf(feature));

                forEachCoordinate(feature.geometry, ([lng, lat]) => {
                    const [x, y] = this._project(lng, lat);
                    const radius = resolve(layer.paint?.['circle-radius'], props) ?? 6;

                    // Ko'rinadigan nuqta kichkina bo'lganda (masalan xarita
                    // kichraytirilganda) uni aniq bosish qiyin bo'ladi —
                    // shuning uchun ko'zga ko'rinmas, lekin kattaroq "bosish
                    // maydoni" alohida qo'shiladi (foydalanuvchi tajribasi
                    // uchun, vizual ko'rinishga ta'sir qilmaydi).
                    const hitArea = document.createElementNS(SVG_NS, 'circle');
                    hitArea.setAttribute('cx', x.toFixed(2));
                    hitArea.setAttribute('cy', y.toFixed(2));
                    hitArea.setAttribute('r', String(Math.max(radius + 10, 16)));
                    hitArea.setAttribute('fill', 'transparent');
                    hitArea.dataset.featureIndex = featureIndex;
                    group.appendChild(hitArea);

                    const circle = document.createElementNS(SVG_NS, 'circle');
                    circle.setAttribute('cx', x.toFixed(2));
                    circle.setAttribute('cy', y.toFixed(2));
                    circle.setAttribute('r', String(radius));
                    circle.setAttribute('fill', resolve(layer.paint?.['circle-color'], props) ?? '#A6791E');
                    circle.setAttribute('stroke', resolve(layer.paint?.['circle-stroke-color'], props) ?? 'none');
                    circle.setAttribute('stroke-width', String(resolve(layer.paint?.['circle-stroke-width'], props) ?? 0));
                    circle.setAttribute('vector-effect', 'non-scaling-stroke');
                    circle.dataset.featureIndex = featureIndex;
                    group.appendChild(circle);
                });
            }
        });

        this._wireLayerEvents(layer);
    }

    _matchesFilter(filter, feature) {
        // Loyihada ishlatiladigan yagona filtr shakli: ['==', ['geometry-type'], 'Polygon']
        if (Array.isArray(filter) && filter[0] === '==' && filter[1]?.[0] === 'geometry-type') {
            const type = feature.geometry?.type ?? '';
            return type === filter[2] || (filter[2] === 'Polygon' && type === 'MultiPolygon');
        }
        return true;
    }

    _wireLayerEvents(layer) {
        const group = layer._group;
        const handlers = layer._eventHandlers ?? {};
        if (!handlers.click && !handlers.mouseenter && !handlers.mouseleave) return;

        Array.from(group.children).forEach((el, i) => {
            if (handlers.click) {
                el.style.cursor = 'pointer';
                el.addEventListener('click', (domEvent) => {
                    domEvent.stopPropagation();
                    const feature = featuresOf(this._sources[layer.source]?.data)[Number(el.dataset.featureIndex ?? i)];
                    handlers.click({ features: [feature], lngLat: this._domEventToLngLat(domEvent) });
                });
            }
            if (handlers.mouseenter) el.addEventListener('mouseenter', handlers.mouseenter);
            if (handlers.mouseleave) el.addEventListener('mouseleave', handlers.mouseleave);
        });
    }

    _domEventToLngLat(domEvent) {
        const rect = this.svg.getBoundingClientRect();
        const x = ((domEvent.clientX - rect.left) / rect.width) * this._width;
        const y = ((domEvent.clientY - rect.top) / rect.height) * this._height;
        const { west, north, cosLat, scale, offsetX, offsetY } = this._projection;
        const lng = west + (x - offsetX) / (cosLat * scale);
        const lat = north - (y - offsetY) / scale;
        return { lng, lat };
    }

    // --- Voqealar (MapLibre bilan mos API) --------------------------------

    on(event, layerIdOrHandler, handler) {
        if (typeof layerIdOrHandler === 'function') {
            if (event === 'load' || event === 'error') {
                this._listeners[event].push(layerIdOrHandler);
            }
            return this;
        }

        const layerId = layerIdOrHandler;
        const attach = () => {
            const layer = this._layers[layerId];
            if (!layer) return;
            layer._eventHandlers = layer._eventHandlers ?? {};
            layer._eventHandlers[event] = handler;
            this._drawLayer(layer);
        };

        if (this._layers[layerId]) attach();
        else this._listeners.load.push(attach);

        return this;
    }

    getCanvas() {
        return this.svg;
    }

    // --- Ko'rinish (pan/zoom) ----------------------------------------------

    fitBounds(bounds, opts = {}) {
        const padding = opts.padding ?? 20;
        this._applyProjection(this._computeFitView(bounds, padding));
    }

    flyTo({ center, zoom }) {
        const spanLng = 360 / Math.pow(2, zoom ?? 5);
        const spanLat = spanLng * (this._height / this._width);
        const bounds = [
            [center[0] - spanLng / 2, center[1] - spanLat / 2],
            [center[0] + spanLng / 2, center[1] + spanLat / 2],
        ];
        this.fitBounds(bounds, { padding: 20 });
    }

    resize() {
        this._render();
    }

    triggerRepaint() {
        // SVG uchun kerak emas — DOM darhol chiziladi.
    }

    loaded() {
        return true;
    }

    // --- Ichki render/interaktivlik ----------------------------------------

    _render() {
        const rect = this.container.getBoundingClientRect();
        this._width = Math.max(rect.width || 800, 200);
        this._height = Math.max(rect.height || 480, 160);
        this.svg.setAttribute('viewBox', `0 0 ${this._width} ${this._height}`);

        if (!this._projection) {
            this._projection = this._computeFitView(this._baseBounds, 20);
        }
    }

    _attachInteractions() {
        let dragging = false;
        let last = null;

        this.svg.addEventListener('wheel', (e) => {
            e.preventDefault();
            const factor = e.deltaY < 0 ? 1.15 : 1 / 1.15;
            this._zoomAt(e.offsetX, e.offsetY, factor);
        }, { passive: false });

        this.svg.addEventListener('mousedown', (e) => {
            dragging = true;
            last = [e.clientX, e.clientY];
        });

        window.addEventListener('mousemove', (e) => {
            if (!dragging) return;
            const dx = e.clientX - last[0];
            const dy = e.clientY - last[1];
            last = [e.clientX, e.clientY];
            this._panBy(dx, dy);
        });

        window.addEventListener('mouseup', () => { dragging = false; });

        this.svg.addEventListener('click', (e) => {
            if (this._onMapClick && !dragging) this._onMapClick(this._domEventToLngLat(e));
        });
    }

    _zoomAt(px, py, factor) {
        const p = this._projection;
        const rect = this.svg.getBoundingClientRect();
        const sx = (px / rect.width) * this._width;
        const sy = (py / rect.height) * this._height;

        const lng = p.west + (sx - p.offsetX) / (p.cosLat * p.scale);
        const lat = p.north - (sy - p.offsetY) / p.scale;

        const newScale = p.scale * factor;
        const newOffsetX = sx - (lng - p.west) * p.cosLat * newScale;
        const newOffsetY = sy - (p.north - lat) * newScale;

        this._applyProjection({ ...p, scale: newScale, offsetX: newOffsetX, offsetY: newOffsetY });
    }

    _panBy(dx, dy) {
        const p = this._projection;
        this._applyProjection({ ...p, offsetX: p.offsetX + dx, offsetY: p.offsetY + dy });
    }

    zoomIn() { this._zoomAt(this._width / 2, this._height / 2, 1.4); }
    zoomOut() { this._zoomAt(this._width / 2, this._height / 2, 1 / 1.4); }

    _addZoomControls() {
        const controls = document.createElement('div');
        controls.className = 'svg-map-controls';

        const inBtn = document.createElement('button');
        inBtn.type = 'button';
        inBtn.textContent = '+';
        inBtn.setAttribute('aria-label', "Xaritani kattalashtirish");
        inBtn.addEventListener('click', () => this.zoomIn());

        const outBtn = document.createElement('button');
        outBtn.type = 'button';
        outBtn.textContent = '−';
        outBtn.setAttribute('aria-label', "Xaritani kichiklashtirish");
        outBtn.addEventListener('click', () => this.zoomOut());

        controls.appendChild(inBtn);
        controls.appendChild(outBtn);
        this.container.appendChild(controls);
    }
}

/**
 * MapLibre'ning `Popup` klassiga mos, zanjirlanadigan (chainable) mini popup —
 * `new Popup().setLngLat(...).setHTML(...)/.setDOMContent(...).addTo(map)`.
 */
export class Popup {
    constructor(options = {}) {
        this.options = options;
        this.el = document.createElement('div');
        this.el.className = 'svg-map-popup';

        if (options.closeButton !== false) {
            const close = document.createElement('span');
            close.className = 'svg-map-popup-close';
            close.textContent = '×';
            close.setAttribute('aria-label', 'Yopish');
            close.addEventListener('click', () => this.remove());
            this.el.appendChild(close);
        }

        this.content = document.createElement('div');
        this.el.appendChild(this.content);

        if (options.maxWidth) this.el.style.maxWidth = options.maxWidth;
    }

    setLngLat(lngLat) {
        this._lngLat = Array.isArray(lngLat) ? { lng: lngLat[0], lat: lngLat[1] } : lngLat;
        this._reposition();
        return this;
    }

    setHTML(html) {
        this.content.innerHTML = html;
        return this;
    }

    setDOMContent(node) {
        this.content.innerHTML = '';
        this.content.appendChild(node);
        return this;
    }

    addTo(map) {
        this._map = map;
        map.container.querySelectorAll('.svg-map-popup').forEach((p) => p.remove());
        map.container.appendChild(this.el);
        map._popupRepositioners.push(() => this._reposition());
        this._reposition();
        return this;
    }

    remove() {
        this.el.remove();
    }

    _reposition() {
        if (!this._map || !this._lngLat) return;
        const [x, y] = this._map._project(this._lngLat.lng, this._lngLat.lat);
        const scaleX = this._map.svg.clientWidth / this._map._width;
        const scaleY = this._map.svg.clientHeight / this._map._height;
        this.el.style.left = `${x * scaleX}px`;
        this.el.style.top = `${y * scaleY}px`;
    }
}

/**
 * MapLibre'ning `Marker` klassiga mos, oddiy DOM-based (SVG emas) draggable
 * nuqta belgisi — admin koordinata tanlash formasi uchun.
 */
export class Marker {
    constructor(options = {}) {
        this.options = options;
        this.el = document.createElement('div');
        this.el.className = 'svg-map-marker';
        this.el.style.backgroundColor = options.color || '#A6791E';
        if (options.draggable) this.el.style.cursor = 'grab';
        this._draggable = !!options.draggable;
        this._listeners = {};
    }

    setLngLat(lngLat) {
        this._lngLat = Array.isArray(lngLat) ? { lng: lngLat[0], lat: lngLat[1] } : lngLat;
        this._reposition();
        return this;
    }

    getLngLat() {
        return this._lngLat;
    }

    addTo(map) {
        this._map = map;
        map.container.appendChild(this.el);
        map._popupRepositioners.push(() => this._reposition());
        this._reposition();
        if (this._draggable) this._attachDrag();
        return this;
    }

    remove() {
        this.el.remove();
    }

    on(event, handler) {
        this._listeners[event] = handler;
        return this;
    }

    _reposition() {
        if (!this._map || !this._lngLat) return;
        const [x, y] = this._map._project(this._lngLat.lng, this._lngLat.lat);
        const scaleX = this._map.svg.clientWidth / this._map._width;
        const scaleY = this._map.svg.clientHeight / this._map._height;
        this.el.style.left = `${x * scaleX}px`;
        this.el.style.top = `${y * scaleY}px`;
    }

    _attachDrag() {
        let dragging = false;

        this.el.addEventListener('mousedown', (e) => {
            dragging = true;
            e.stopPropagation();
        });

        window.addEventListener('mousemove', (e) => {
            if (!dragging) return;
            this.setLngLat(this._map._domEventToLngLat(e));
        });

        window.addEventListener('mouseup', () => {
            if (!dragging) return;
            dragging = false;
            this._listeners.dragend?.();
        });
    }
}

function rayCastInRing([x, y], ring) {
    let inside = false;
    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
        const [xi, yi] = ring[i];
        const [xj, yj] = ring[j];
        const intersect = ((yi > y) !== (yj > y)) && (x < ((xj - xi) * (y - yi)) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
}

/** Nuqta geometriya (Polygon/MultiPolygon) ichidami — teshiklar (holes) e'tiborga olinmaydi (loyihadagi viloyat poligonlari uchun yetarli). */
function pointInGeometry([lng, lat], geometry) {
    if (!geometry) return false;
    if (geometry.type === 'Polygon') return rayCastInRing([lng, lat], geometry.coordinates[0]);
    if (geometry.type === 'MultiPolygon') return geometry.coordinates.some((polygon) => rayCastInRing([lng, lat], polygon[0]));
    return false;
}

/**
 * Berilgan nuqtalar (masalan tanlangan qo'rboshi/qo'zg'olon markerlari)
 * qaysi viloyat poligonlari ichida joylashganini aniqlaydi — natija shu
 * hududlarni maxsus belgilash (highlight) uchun ishlatiladi.
 */
function regionNamesContaining(featureCollection, points) {
    const names = new Set();
    (featureCollection?.features ?? []).forEach((feature) => {
        for (const point of points) {
            if (pointInGeometry(point, feature.geometry)) {
                names.add(feature.properties?.name);
                break;
            }
        }
    });
    return names;
}

function boundsOfFeatures(features, minPad = 0.3) {
    let west = Infinity, south = Infinity, east = -Infinity, north = -Infinity;
    let hasCoords = false;

    features.forEach((feature) => forEachCoordinate(feature.geometry, ([lng, lat]) => {
        west = Math.min(west, lng); east = Math.max(east, lng);
        south = Math.min(south, lat); north = Math.max(north, lat);
        hasCoords = true;
    }));

    if (!hasCoords) return null;

    const padLng = Math.max((east - west) * 0.15, minPad);
    const padLat = Math.max((north - south) * 0.15, minPad);
    return [[west - padLng, south - padLat], [east + padLng, north + padLat]];
}

export { geometryToPath, featuresOf, forEachCoordinate, regionNamesContaining, boundsOfFeatures };
