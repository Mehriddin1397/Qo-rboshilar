import './bootstrap';

import Alpine from 'alpinejs';
import { initUzgolonMap } from './map/uzgolon-map';
import { initGeoJsonPreview } from './map/geojson-preview';
import { initTurkestanMap } from './map/turkestan-map';
import { initMapMarkerPicker } from './map/marker-picker';

window.Alpine = Alpine;
window.initUzgolonMap = initUzgolonMap;
window.initGeoJsonPreview = initGeoJsonPreview;
window.initTurkestanMap = initTurkestanMap;
window.initMapMarkerPicker = initMapMarkerPicker;

Alpine.start();
