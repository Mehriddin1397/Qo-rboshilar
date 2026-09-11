// maplibre-gl@6 default export'ni olib tashladi (faqat named exportlar) — bu Faza 9'da
// yozilgandan keyin package.json'dagi "^6.7.0" bilan bog'liq holda paydo bo'lgan mavjud
// build xatosi edi (Faza 12 buni tuzatdi, chunki u umuman `npm run build`ni blokladi).
import { Map, NavigationControl, Popup, LngLatBounds } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

// MapLibre'ning o'z bepul demo uslubi — API kalitisiz ishlaydi. Faza 9 — birinchi
// ishlaydigan versiya; kelajakda bu shu yerga tarixiy Turkiston overlay/uslub bilan
// almashtiriladi (ARCHITECTURE.md §18 ga qarang).
const DEFAULT_STYLE = 'https://demotiles.maplibre.org/style.json';

// Turkiston mintaqasi taxminiy markazi — hech qanday marker bo'lmaganda fallback.
const FALLBACK_CENTER = [64.5, 41.2];

function escapeHtml(value) {
    if (!value) return '';
    return String(value).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function truncate(value, length) {
    if (!value) return '';
    return value.length > length ? value.slice(0, length) + '…' : value;
}

function buildPopupHtml(props) {
    const yearRange = props.endYear && props.endYear !== props.startYear
        ? `${props.startYear}–${props.endYear}`
        : `${props.startYear ?? ''}`;

    return `
        <div class="map-popup">
            <p class="map-popup-title">${escapeHtml(props.title)}</p>
            <p class="map-popup-meta">${escapeHtml(yearRange)}${props.region ? ' · ' + escapeHtml(props.region) : ''}</p>
            ${props.shortDescription ? `<p class="map-popup-desc">${escapeHtml(truncate(props.shortDescription, 120))}</p>` : ''}
            ${props.url ? `<a href="${props.url}" class="map-popup-link">Batafsil &rarr;</a>` : ''}
        </div>
    `;
}

/**
 * @param {string} containerId
 * @param {{type: string, features: array}} geojson - qo'zg'olon markerlari (server-rendered)
 * @param {{zoom?: number, interactive?: boolean, fitBounds?: boolean}} options
 */
export function initUzgolonMap(containerId, geojson, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return null;

    const features = geojson?.features ?? [];
    const interactive = options.interactive !== false;
    const center = features.length ? features[0].geometry.coordinates : FALLBACK_CENTER;

    const map = new Map({
        container: containerId,
        style: DEFAULT_STYLE,
        center,
        zoom: options.zoom ?? (features.length ? 5.5 : 4.5),
        interactive,
        attributionControl: { compact: true },
    });

    if (interactive) {
        map.addControl(new NavigationControl({ showCompass: false }), 'top-right');
    }

    map.on('load', () => {
        if (features.length === 0) {
            return;
        }

        map.addSource('qozgolonlar', { type: 'geojson', data: geojson });

        map.addLayer({
            id: 'qozgolonlar-points',
            type: 'circle',
            source: 'qozgolonlar',
            paint: {
                'circle-radius': 8,
                'circle-color': '#A6791E',
                'circle-stroke-width': 2,
                'circle-stroke-color': '#FBF6EC',
            },
        });

        map.on('mouseenter', 'qozgolonlar-points', () => { map.getCanvas().style.cursor = 'pointer'; });
        map.on('mouseleave', 'qozgolonlar-points', () => { map.getCanvas().style.cursor = ''; });

        map.on('click', 'qozgolonlar-points', (event) => {
            const feature = event.features[0];

            new Popup({ closeButton: true, offset: 12 })
                .setLngLat(feature.geometry.coordinates.slice())
                .setHTML(buildPopupHtml(feature.properties))
                .addTo(map);
        });

        if (features.length > 1 && options.fitBounds !== false) {
            const bounds = new LngLatBounds();
            features.forEach((feature) => bounds.extend(feature.geometry.coordinates));
            map.fitBounds(bounds, { padding: 60, maxZoom: 9 });
        }
    });

    return map;
}
