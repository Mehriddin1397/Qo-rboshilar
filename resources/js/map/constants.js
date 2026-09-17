// WebGL (MapLibre GL) ba'zi kompyuterlarda umuman ishlamasligi tasdiqlandi —
// shuning uchun xarita endi SVG asosida (`svg-map.js`) chiziladi, GPU/WebGL'ga
// bog'liq emas. Bazaviy geografik ma'lumot ham tashqi tile-server'dan emas,
// loyihaning o'z serveridan xizmat qiladi (§35, ARCHITECTURE.md).
import { regionNamesContaining, boundsOfFeatures } from './svg-map';

export const UZBEKISTAN_CENTER = [64.5, 41.2];

// O'zbekiston chegaralovchi to'rtburchagi (bir oz padding bilan) — [janubi-g'arb, shimoli-sharq].
export const UZBEKISTAN_BOUNDS = [
    [55.9, 37.0],
    [73.3, 45.7],
];

// geoBoundaries.org (OpenStreetMap ma'lumotlari asosida, ODC-BY litsenziya)dan
// olingan O'zbekiston 14 ta viloyat/respublika/shahar chegarasi — loyiha
// serveridan xizmat qiladi, hech qanday tashqi so'rov kerak emas.
export const UZBEKISTAN_REGIONS_URL = '/geo/uzbekistan-regions.geojson';

export const MAP_ATTRIBUTION = "Chegaralar: © OpenStreetMap hissa qo'shuvchilari, geoBoundaries.org (ODbL)";

const PAPER_DARK = '#EFE4CE';
const BROWN_700 = '#6B4A32';
const GOLD_600 = '#A6791E';

// Har bir viloyat/respublika/shahar uchun alohida, lekin pergament dizayn
// tiliga mos (och, "eski atlas" uslubidagi) rang — sof "rang-barang"
// (neon/SaaS dashboard) emas, tarixiy siyosiy xarita an'anasidagi kabi har
// bir hudud o'z rangida ko'rinadi (§55-56 taqiqiga zid emas).
const REGION_COLORS = {
    'Andijan Region': '#C9855A',
    'Namangan Region': '#B08A55',
    'Fergana Region': '#93A06E',
    'Republic of Karakalpakstan': '#7FA098',
    'Xorazm Region': '#BD9761',
    'Navoiy Region': '#A084A0',
    'Surxondaryo Region': '#B56E5A',
    'Samarqand Region': '#C7AC68',
    'Tashkent Region': '#8393A8',
    'Sirdaryo Region': '#AC9873',
    'Jizzakh Region': '#93789C',
    'Bukhara Region': '#C0955F',
    'Qashqadaryo Region': '#89A07E',
    'Tashkent': '#749296',
};

/**
 * Xaritaga O'zbekiston viloyat chegaralarini pastki (bazaviy) qatlam sifatida
 * qo'shadi — har bir hudud alohida rangda (eski atlas uslubi). Boshqa barcha
 * overlay/marker qatlamlari BUNDAN KEYIN qo'shilishi kerak (ular ustida
 * chiqishi uchun).
 */
export function addUzbekistanRegionsLayer(map) {
    map.addSource('uzbekistan-regions', { type: 'geojson', data: UZBEKISTAN_REGIONS_URL });

    map.addLayer({
        id: 'uzbekistan-regions-fill',
        type: 'fill',
        source: 'uzbekistan-regions',
        paint: {
            'fill-color': (props) => REGION_COLORS[props.name] ?? PAPER_DARK,
            'fill-opacity': 0.6,
        },
    });

    map.addLayer({
        id: 'uzbekistan-regions-outline',
        type: 'line',
        source: 'uzbekistan-regions',
        paint: { 'line-color': BROWN_700, 'line-width': 1 },
    });
}

/**
 * Tanlangan qo'rboshi/qo'zg'olon filtriga tegishli hudud(lar)ni oltin rangda
 * qalin chegara bilan maxsus belgilaydi va shu hududlarga moslab xaritani
 * markazlashtiradi (Faza 15 §36).
 *
 * @param {import('./svg-map').SvgMap} map
 * @param {Array<[number, number]>} points - [lng, lat] juftliklar
 */
export async function highlightRegionsForPoints(map, points) {
    if (!points?.length) return;

    const regionsData = await fetch(UZBEKISTAN_REGIONS_URL).then((r) => r.json());
    const highlightedNames = regionNamesContaining(regionsData, points);

    if (highlightedNames.size === 0) return;

    map.addSource('uzbekistan-regions-highlight', { type: 'geojson', data: regionsData });
    map.addLayer({
        id: 'uzbekistan-regions-highlight',
        type: 'line',
        source: 'uzbekistan-regions-highlight',
        filter: (feature) => highlightedNames.has(feature.properties?.name),
        paint: { 'line-color': GOLD_600, 'line-width': 3.5 },
    });

    const highlightedFeatures = regionsData.features.filter((f) => highlightedNames.has(f.properties?.name));
    const bounds = boundsOfFeatures(highlightedFeatures);
    if (bounds) map.fitBounds(bounds, { padding: 40 });
}
