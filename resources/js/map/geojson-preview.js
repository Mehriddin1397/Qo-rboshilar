import { Map, NavigationControl, LngLatBounds } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const DEFAULT_STYLE = 'https://demotiles.maplibre.org/style.json';
const FALLBACK_CENTER = [64.5, 41.2];

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

    const map = new Map({
        container: containerId,
        style: DEFAULT_STYLE,
        center: FALLBACK_CENTER,
        zoom: 4,
        interactive: true,
        attributionControl: { compact: true },
    });

    map.addControl(new NavigationControl({ showCompass: false }), 'top-right');

    map.on('load', () => {
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

        try {
            const bounds = new LngLatBounds();
            let hasCoords = false;

            const extend = (coords) => {
                if (typeof coords[0] === 'number') {
                    bounds.extend(coords);
                    hasCoords = true;
                } else {
                    coords.forEach(extend);
                }
            };

            const features = geojson.type === 'FeatureCollection' ? geojson.features
                : geojson.type === 'Feature' ? [geojson]
                : [{ geometry: geojson }];

            features.forEach((feature) => {
                if (feature?.geometry?.coordinates) {
                    extend(feature.geometry.coordinates);
                }
            });

            if (hasCoords) {
                map.fitBounds(bounds, { padding: 40, maxZoom: 9 });
            }
        } catch (e) {
            // Preview — fitBounds ishlamasa ham xarita bazaviy view bilan ko'rinadi.
        }
    });

    return map;
}
