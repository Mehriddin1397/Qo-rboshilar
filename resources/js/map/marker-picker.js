import { Map, NavigationControl, Marker } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const DEFAULT_STYLE = 'https://demotiles.maplibre.org/style.json';
const FALLBACK_CENTER = [64.5, 41.2];
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
    const center = hasInitial ? [options.longitude, options.latitude] : FALLBACK_CENTER;

    const map = new Map({
        container: containerId,
        style: DEFAULT_STYLE,
        center,
        zoom: hasInitial ? 8 : 4.5,
        interactive: true,
        attributionControl: { compact: true },
    });

    map.addControl(new NavigationControl({ showCompass: false }), 'top-right');

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
        map.on('click', (event) => {
            placeMarker([event.lngLat.lng, event.lngLat.lat]);
            options.onChange?.(round(event.lngLat.lat), round(event.lngLat.lng));
        });
    }

    return {
        map,
        /** Lat/lng inputiga qo'lda kiritilganda xaritadagi markerni yangilaydi. */
        setPosition(lat, lng) {
            if (typeof lat !== 'number' || typeof lng !== 'number' || Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            placeMarker([lng, lat]);
            map.flyTo({ center: [lng, lat] });
        },
    };
}
