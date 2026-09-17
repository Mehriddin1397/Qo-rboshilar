import { SvgMap, Popup } from './svg-map';
import { UZBEKISTAN_BOUNDS, MAP_ATTRIBUTION, addUzbekistanRegionsLayer, highlightRegionsForPoints } from './constants';

const BRAND_GOLD = '#A6791E';
const BRAND_BROWN = '#6B4A32';
const BRAND_INK = '#1A1512';

/**
 * Feature.properties ichidagi qiymatlarni HTML sifatida hech qachon qurmaydi —
 * har bir matn `textContent`/`createTextNode` orqali DOM'ga qo'yiladi, shuning
 * uchun `<script>` kabi qiymatlar avtomatik ravishda oddiy matn sifatida
 * ko'rsatiladi (Faza 13 §24, §50).
 */
function isSafeUrl(url) {
    if (!url) return false;
    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch (e) {
        return false;
    }
}

function buildPopupElement(title, rows) {
    const container = document.createElement('div');
    container.className = 'map-popup';

    if (title) {
        const heading = document.createElement('p');
        heading.className = 'map-popup-title';
        heading.appendChild(document.createTextNode(title));
        container.appendChild(heading);
    }

    rows.forEach(({ label, value, href, long }) => {
        if (!value && !href) return;

        const row = document.createElement('p');
        row.className = long ? 'map-popup-desc' : 'map-popup-meta';

        if (label) {
            const strong = document.createElement('strong');
            strong.appendChild(document.createTextNode(label + ': '));
            row.appendChild(strong);
        }

        if (href && isSafeUrl(href)) {
            const link = document.createElement('a');
            link.href = href;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.className = 'map-popup-link';
            link.appendChild(document.createTextNode(value || "Manbani ko'rish"));
            row.appendChild(link);
        } else if (value) {
            row.appendChild(document.createTextNode(value));
        }

        container.appendChild(row);
    });

    return container;
}

function historicalFeaturePopupRows(props) {
    return [
        { label: 'Tarixiy nom', value: props.historicalName },
        { label: 'Zamonaviy nom', value: props.modernName },
        { label: 'Tarixiy hudud', value: props.historicalRegion },
        { label: 'Tur', value: props.regionType },
        { label: 'Davr', value: props.period },
        { label: 'Aniqlik', value: props.accuracyLabel },
        { label: 'Tavsif', value: props.description, long: true },
        { label: 'Manba', value: props.sourceSummary || 'Manba kiritilmagan', href: props.sourceUrl },
    ];
}

function emptyFeatureCollection() {
    return { type: 'FeatureCollection', features: [] };
}

// Data-driven uzuq chiziq: geometriya "verified" deb belgilanmagan bo'lsa
// taxminiy chegara indikatori (§23, §41 — false precision yaratilmasin).
const accuracyDasharray = (props) => (props.accuracyStatus === 'verified' ? null : [4, 3]);

/**
 * @param {string} containerId
 * @param {{
 *   markers: object,
 *   historicalRegions: object,
 *   historicalLayers: object,
 *   rasterLayers: Array<{id:number,title:string,imageUrl:string,bounds:{north:number,south:number,east:number,west:number},opacity:number}>,
 *   timelineEvents: object,
 * }} data - barchasi Laravel tomonidan @js() orqali server-rendered
 * @param {{onError?: (message: string) => void, focusEventSlug?: string, highlightPoints?: Array<[number, number]>}} [options]
 */
export function initTurkestanMap(containerId, data, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return null;

    const markers = data.markers ?? emptyFeatureCollection();
    const historicalRegions = data.historicalRegions ?? emptyFeatureCollection();
    const historicalLayers = data.historicalLayers ?? emptyFeatureCollection();
    const rasterLayers = (data.rasterLayers ?? []).filter((layer) => layer.bounds && layer.imageUrl);
    const timelineEvents = data.timelineEvents ?? emptyFeatureCollection();

    let map;

    try {
        map = new SvgMap(containerId, {
            bounds: UZBEKISTAN_BOUNDS,
            interactive: true,
            attribution: MAP_ATTRIBUTION,
        });
    } catch (e) {
        options.onError?.('Xarita bazasini yuklashda xatolik yuz berdi.');
        return null;
    }

    map.on('error', (event) => {
        // Bitta manba/tile xatosi butun xaritani yiqitmasin (§59) — faqat log.
        console.warn('Xarita xatosi:', event?.error?.message ?? event);
    });

    const raster = { addedIds: [] };

    map.on('load', () => {
        try {
            // 0) O'zbekiston viloyat chegaralari — eng pastki (bazaviy) qatlam,
            // hech qanday tashqi tile-server'ga muhtoj emas (§35).
            addUzbekistanRegionsLayer(map);

            // 0.1) Qo'rboshi/Qo'zg'olon filtri tanlangan bo'lsa — tegishli
            // hudud(lar) oltin chegara bilan maxsus belgilanadi (§36).
            if (options.highlightPoints?.length) {
                highlightRegionsForPoints(map, options.highlightPoints);
            }

            // 1) Tarixiy hududlar (fill + outline) — pastki qatlam.
            map.addSource('historical-regions', { type: 'geojson', data: historicalRegions });
            map.addLayer({
                id: 'historical-regions-fill',
                type: 'fill',
                source: 'historical-regions',
                paint: { 'fill-color': BRAND_GOLD, 'fill-opacity': 0.18 },
            });
            map.addLayer({
                id: 'historical-regions-outline',
                type: 'line',
                source: 'historical-regions',
                paint: {
                    'line-color': BRAND_GOLD,
                    'line-width': 1.5,
                    'line-dasharray': accuracyDasharray,
                },
            });

            // 2) Tarixiy chegaralar/qatlamlar (line-only, standart holatda o'chiq).
            map.addSource('historical-layers', { type: 'geojson', data: historicalLayers });
            map.addLayer({
                id: 'historical-layers-line',
                type: 'line',
                source: 'historical-layers',
                layout: { visibility: 'none' },
                paint: {
                    'line-color': BRAND_BROWN,
                    'line-width': 2,
                    'line-dasharray': accuracyDasharray,
                },
            });

            // 3) Raster overlaylar (georeferenced tarixiy rasm, agar mavjud bo'lsa).
            rasterLayers.forEach((layer) => {
                const sourceId = `raster-${layer.id}`;
                map.addSource(sourceId, {
                    type: 'image',
                    url: layer.imageUrl,
                    coordinates: [
                        [layer.bounds.west, layer.bounds.north],
                        [layer.bounds.east, layer.bounds.north],
                        [layer.bounds.east, layer.bounds.south],
                        [layer.bounds.west, layer.bounds.south],
                    ],
                });
                map.addLayer({
                    id: sourceId,
                    type: 'raster',
                    source: sourceId,
                    paint: { 'raster-opacity': layer.opacity ?? 0.7 },
                });
                raster.addedIds.push(sourceId);
            });

            // 4) Xronologiya (Timeline) voqea nuqtalari — Faza 14 §12-13. Qo'zg'olon
            // markerlaridan ATAYLAB farqli uslubda (kichikroq, to'q ink rangli, oltin
            // konturli) — ikkalasi bir joyda chalkashib ketmasligi uchun (§13).
            map.addSource('timeline-events', { type: 'geojson', data: timelineEvents });
            map.addLayer({
                id: 'timeline-events-points',
                type: 'circle',
                source: 'timeline-events',
                paint: {
                    'circle-radius': 6,
                    'circle-color': BRAND_INK,
                    'circle-stroke-width': 2,
                    'circle-stroke-color': BRAND_GOLD,
                },
            });

            // 5) Qo'zg'olon markerlari — eng ustida (Faza 9 arxitekturasi bilan bir xil
            // vizual uslub), tarixiy qatlamlar ostida yo'qolib ketmasligi uchun oxirida
            // qo'shiladi (§26).
            map.addSource('uprising-markers', { type: 'geojson', data: markers });
            map.addLayer({
                id: 'uprising-markers-points',
                type: 'circle',
                source: 'uprising-markers',
                paint: {
                    'circle-radius': 8,
                    'circle-color': BRAND_GOLD,
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#FBF6EC',
                },
            });

            // Popup + cursor xatti-harakati.
            const interactiveLayers = ['historical-regions-fill', 'historical-layers-line', 'timeline-events-points', 'uprising-markers-points'];

            interactiveLayers.forEach((layerId) => {
                map.on('mouseenter', layerId, () => { map.getCanvas().style.cursor = 'pointer'; });
                map.on('mouseleave', layerId, () => { map.getCanvas().style.cursor = ''; });
            });

            map.on('click', 'historical-regions-fill', (event) => {
                const props = event.features[0].properties;
                new Popup({ closeButton: true, maxWidth: '300px' })
                    .setLngLat(event.lngLat)
                    .setDOMContent(buildPopupElement(props.name, historicalFeaturePopupRows(props)))
                    .addTo(map);
            });

            map.on('click', 'historical-layers-line', (event) => {
                const props = event.features[0].properties;
                new Popup({ closeButton: true, maxWidth: '300px' })
                    .setLngLat(event.lngLat)
                    .setDOMContent(buildPopupElement(props.name, historicalFeaturePopupRows(props)))
                    .addTo(map);
            });

            map.on('click', 'uprising-markers-points', (event) => {
                const props = event.features[0].properties;
                new Popup({ closeButton: true, maxWidth: '280px' })
                    .setLngLat(event.lngLat)
                    .setDOMContent(buildPopupElement(props.title, [
                        { label: 'Yil', value: props.endYear && props.endYear !== props.startYear ? `${props.startYear}–${props.endYear}` : `${props.startYear ?? ''}` },
                        { label: 'Hudud', value: props.region },
                        { label: 'Tavsif', value: props.shortDescription, long: true },
                        { label: null, value: 'Batafsil', href: props.url },
                    ]))
                    .addTo(map);
            });

            const openTimelineEventPopup = (lngLat, props) => {
                new Popup({ closeButton: true, maxWidth: '280px' })
                    .setLngLat(lngLat)
                    .setDOMContent(buildPopupElement(props.title, [
                        { label: 'Yil', value: props.yearRange },
                        { label: 'Davr', value: props.period },
                        { label: 'Aniqlik', value: props.accuracyLabel },
                        { label: "Qo'rboshi", value: props.qorboshi },
                        { label: "Qo'zg'olon", value: props.uzgolon },
                        { label: 'Tavsif', value: props.description, long: true },
                        { label: null, value: 'Batafsil', href: props.url },
                    ]))
                    .addTo(map);
            };

            map.on('click', 'timeline-events-points', (event) => {
                openTimelineEventPopup(event.lngLat, event.features[0].properties);
            });

            // Faza 14 §12: Timeline detail sahifasidan "Xaritada ko'rish" bosilganda
            // — shu voqeaning nuqtasiga flyTo qilinadi va popup avtomatik ochiladi.
            if (options.focusEventSlug) {
                const focusFeature = (timelineEvents.features ?? []).find(
                    (feature) => feature.properties?.slug === options.focusEventSlug
                );

                if (focusFeature?.geometry?.coordinates) {
                    const [lng, lat] = focusFeature.geometry.coordinates;
                    map.flyTo({ center: [lng, lat], zoom: 9 });
                    openTimelineEventPopup([lng, lat], focusFeature.properties);
                }
            }

            // Barcha mavjud nuqta/geometriyalarga moslab fitBounds — lekin faqat
            // bitta voqeaga focus qilinmagan yoki hudud highlight qilinmagan
            // bo'lsa (aks holda ularning natijasini darhol bekor qilib qo'yardi).
            if (!options.focusEventSlug && !options.highlightPoints?.length) {
                let west = Infinity, south = Infinity, east = -Infinity, north = -Infinity;
                let hasBounds = false;

                const extend = (coords) => {
                    if (typeof coords[0] === 'number') {
                        const [lng, lat] = coords;
                        west = Math.min(west, lng); east = Math.max(east, lng);
                        south = Math.min(south, lat); north = Math.max(north, lat);
                        hasBounds = true;
                    } else {
                        coords.forEach(extend);
                    }
                };

                [markers, historicalRegions, historicalLayers, timelineEvents].forEach((collection) => {
                    (collection.features ?? []).forEach((feature) => {
                        if (feature.geometry?.coordinates) {
                            try { extend(feature.geometry.coordinates); } catch (e) { /* geometriyasi bo'lmagan feature — o'tkazib yuboriladi */ }
                        }
                    });
                });

                if (hasBounds) {
                    const padLng = Math.max((east - west) * 0.15, 0.3);
                    const padLat = Math.max((north - south) * 0.15, 0.3);
                    map.fitBounds([[west - padLng, south - padLat], [east + padLng, north + padLat]], { padding: 60 });
                } else {
                    // Hech qanday geometriya yo'q (masalan tanlangan davr uchun
                    // ma'lumot kiritilmagan) — bo'sh dunyo xaritasi o'rniga
                    // O'zbekiston hududini ko'rsatadi.
                    map.fitBounds(UZBEKISTAN_BOUNDS, { padding: 20 });
                }
            }
        } catch (e) {
            options.onError?.('Tarixiy xarita qatlamlarini yuklashda xatolik yuz berdi.');
        }
    });

    return {
        map,
        /**
         * Layer switcher checkboxlari shu orqali qatlamlarni ko'rsatadi/yashiradi
         * — server so'rovi kerak emas (§16).
         */
        setLayerVisibility(layerIds, visible) {
            (Array.isArray(layerIds) ? layerIds : [layerIds]).forEach((layerId) => {
                if (map.getLayer(layerId)) {
                    map.setLayoutProperty(layerId, 'visibility', visible ? 'visible' : 'none');
                }
            });
        },
        hasRasterLayers: rasterLayers.length > 0,
        rasterLayerIds: raster.addedIds,
    };
}
