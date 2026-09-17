import { SvgMap, Marker } from './svg-map';
import { UZBEKISTAN_CENTER, MAP_ATTRIBUTION, addUzbekistanRegionsLayer } from './constants';

const BRAND_GOLD = '#A6791E';

function round(value) {
    return Math.round(value * 1e7) / 1e7;
}

/**
 * MapMarker admin create/edit/show sahifalarida ishlatiladigan kichik, qayta
 * ishlatiladigan koordinata tanlash preview'i. Click/drag orqali koordinata
 * tanlanadi, `onChange` callback lat/lng inputlarini yangilaydi (Alpine orqali) —
 * hech qanday server so'rovi kerak emas.
 *
 * @param {string} containerId
 * @param {{latitude?: number, longitude?: number, readonly?: boolean, onChange?: (lat:number, lng:number) => void}} options
 */
export function initMapMarkerPicker(containerId, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return null;

    const hasInitial = typeof options.latitude === 'number' && !Number.isNaN(options.latitude)
        && typeof options.longitude === 'number' && !Number.isNaN(options.longitude);
    const readonly = !!options.readonly;
    const center = hasInitial ? [options.longitude, options.latitude] : UZBEKISTAN_CENTER;
    const pad = hasInitial ? 1 : 4.5;

    const map = new SvgMap(containerId, {
        bounds: [[center[0] - pad, center[1] - pad], [center[0] + pad, center[1] + pad]],
        interactive: true,
        attribution: MAP_ATTRIBUTION,
    });

    map.on('load', () => addUzbekistanRegionsLayer(map));

    let marker = null;

    const placeMarker = (lngLat) => {
        if (marker) {
            marker.setLngLat(lngLat);
            return;
        }

        marker = new Marker({ color: BRAND_GOLD, draggable: !readonly }).setLngLat(lngLat).addTo(map);

        if (!readonly) {
            marker.on('dragend', () => {
                const { lat, lng } = marker.getLngLat();
                options.onChange?.(round(lat), round(lng));
            });
        }
    };

    map.on('load', () => {
        if (hasInitial) {
            placeMarker([options.longitude, options.latitude]);
        }
    });

    if (!readonly) {
        map._onMapClick = (lngLat) => {
            placeMarker([lngLat.lng, lngLat.lat]);
            options.onChange?.(round(lngLat.lat), round(lngLat.lng));
        };
    }

    return {
        map,
        /** Lat/lng inputiga qo'lda kiritilganda xaritadagi markerni yangilaydi. */
        setPosition(lat, lng) {
            if (typeof lat !== 'number' || typeof lng !== 'number' || Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            placeMarker([lng, lat]);
            map.fitBounds([[lng - 1, lat - 1], [lng + 1, lat + 1]], { padding: 20 });
        },
    };
}
