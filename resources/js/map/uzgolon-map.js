import { SvgMap, Popup } from './svg-map';
import { UZBEKISTAN_BOUNDS, MAP_ATTRIBUTION, addUzbekistanRegionsLayer } from './constants';

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

/** Nuqtalar to'plamidan (biror padding bilan) bounding box hisoblaydi. */
function boundsFromFeatures(features) {
    if (features.length === 1) {
        const [lng, lat] = features[0].geometry.coordinates;
        return [[lng - 1.5, lat - 1.5], [lng + 1.5, lat + 1.5]];
    }

    let west = Infinity, south = Infinity, east = -Infinity, north = -Infinity;
    features.forEach((f) => {
        const [lng, lat] = f.geometry.coordinates;
        west = Math.min(west, lng); east = Math.max(east, lng);
        south = Math.min(south, lat); north = Math.max(north, lat);
    });

    const padLng = Math.max((east - west) * 0.15, 0.3);
    const padLat = Math.max((north - south) * 0.15, 0.3);
    return [[west - padLng, south - padLat], [east + padLng, north + padLat]];
}

/**
 * @param {string} containerId
 * @param {{type: string, features: array}} geojson - qo'zg'olon markerlari (server-rendered)
 * @param {{interactive?: boolean}} options
 */
export function initUzgolonMap(containerId, geojson, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return null;

    const features = geojson?.features ?? [];
    const interactive = options.interactive !== false;
    const bounds = features.length ? boundsFromFeatures(features) : UZBEKISTAN_BOUNDS;

    const map = new SvgMap(containerId, {
        bounds,
        interactive,
        attribution: MAP_ATTRIBUTION,
    });

    map.on('load', () => {
        addUzbekistanRegionsLayer(map);

        if (features.length === 0) return;

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

            new Popup({ closeButton: true })
                .setLngLat(feature.geometry.coordinates.slice())
                .setHTML(buildPopupHtml(feature.properties))
                .addTo(map);
        });
    });

    return map;
}
