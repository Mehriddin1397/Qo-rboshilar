import { SvgMap } from './svg-map';
import { UZBEKISTAN_BOUNDS, MAP_ATTRIBUTION, addUzbekistanRegionsLayer } from './constants';

/**
 * Faza 12 §32-33: admin panelda GeoJSON qatorini ko'rish uchun oddiy, xavfsiz preview.
 * Hech qanday popup/HTML property render qilinmaydi (feature.properties ichidagi
 * qiymatlar HTML sifatida hech qachon chiqarilmaydi — XSS xavfi yo'q). Faqat mavjud
 * geometriya chiziladi, avtomatik geometriya yaratilmaydi.
 *
 * @param {string} containerId
 * @param {object} geojson - server tomonidan @js() orqali xavfsiz encode qilingan
 */
export function initGeoJsonPreview(containerId, geojson) {
    const container = document.getElementById(containerId);
    if (!container || !geojson) return null;

    const bounds = boundsFromGeojson(geojson) ?? UZBEKISTAN_BOUNDS;

    const map = new SvgMap(containerId, {
        bounds,
        interactive: true,
        attribution: MAP_ATTRIBUTION,
    });

    map.on('load', () => {
        addUzbekistanRegionsLayer(map);

        map.addSource('preview', { type: 'geojson', data: geojson });

        map.addLayer({
            id: 'preview-fill',
            type: 'fill',
            source: 'preview',
            filter: ['==', ['geometry-type'], 'Polygon'],
            paint: { 'fill-color': '#A6791E', 'fill-opacity': 0.25 },
        });

        map.addLayer({
            id: 'preview-outline',
            type: 'line',
            source: 'preview',
            paint: { 'line-color': '#A6791E', 'line-width': 2 },
        });

        map.addLayer({
            id: 'preview-points',
            type: 'circle',
            source: 'preview',
            filter: ['==', ['geometry-type'], 'Point'],
            paint: { 'circle-radius': 6, 'circle-color': '#A6791E' },
        });
    });

    return map;
}

function boundsFromGeojson(geojson) {
    try {
        let west = Infinity, south = Infinity, east = -Infinity, north = -Infinity;
        let hasCoords = false;

        const extend = (coords) => {
            if (typeof coords[0] === 'number') {
                const [lng, lat] = coords;
                west = Math.min(west, lng); east = Math.max(east, lng);
                south = Math.min(south, lat); north = Math.max(north, lat);
                hasCoords = true;
            } else {
                coords.forEach(extend);
            }
        };

        const features = geojson.type === 'FeatureCollection' ? geojson.features
            : geojson.type === 'Feature' ? [geojson]
            : [{ geometry: geojson }];

        features.forEach((feature) => {
            if (feature?.geometry?.coordinates) extend(feature.geometry.coordinates);
        });

        if (!hasCoords) return null;

        const padLng = Math.max((east - west) * 0.2, 0.5);
        const padLat = Math.max((north - south) * 0.2, 0.5);
        return [[west - padLng, south - padLat], [east + padLng, north + padLat]];
    } catch (e) {
        return null;
    }
}
