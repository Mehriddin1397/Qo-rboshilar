<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Qorboshi;
use App\Models\Region;
use App\Models\Uzgolon;
use App\Services\HistoricalMapService;
use App\Services\MapMarkerService;
use App\Services\UzgolonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(
        private readonly UzgolonService $uzgolonService,
        private readonly HistoricalMapService $historicalMapService,
        private readonly MapMarkerService $mapMarkerService,
    ) {
    }

    public function index(Request $request): View
    {
        $regionId = $request->integer('region') ?: null;
        $periodId = $request->integer('period') ?: null;
        $qorboshiId = $request->integer('qorboshi') ?: null;
        $uzgolonId = $request->integer('uzgolon') ?: null;

        // Qo'zg'olonning "primary" markeri UzgolonService::publicGeoJson() orqali
        // (Uzgolon-boy popup ma'lumoti bilan), qolgan mustaqil/ikkinchi darajali
        // markerlar MapMarkerService::publicGeoJson() orqali qo'shiladi — bitta
        // marker ikki marta chiqmasligi ikkala metodda ham hisobga olingan.
        $uprisingGeojson = $this->uzgolonService->publicGeoJson($regionId, $periodId);
        $standaloneGeojson = $this->mapMarkerService->publicGeoJson($regionId, $periodId);

        return view('pages.map', [
            'geojson' => [
                'type' => 'FeatureCollection',
                'features' => array_merge($uprisingGeojson['features'], $standaloneGeojson['features']),
            ],
            // Faza 13 §53: faqat tanlangan davr uchun ma'lumot yuklanadi — barcha
            // davrlarning geometriyasi birdan yuborilmaydi (lazy-per-period, oddiy
            // GET-filter orqali, alohida AJAX endpoint kerak emas).
            'historicalRegions' => $this->historicalMapService->regionsGeoJson($periodId),
            'historicalLayers' => $this->historicalMapService->layersGeoJson($periodId),
            'rasterLayers' => $this->historicalMapService->rasterLayersData($periodId),
            'timelineEvents' => $this->historicalMapService->timelineEventsGeoJson($periodId),
            // Faza 14 §12: xronologiya detail sahifasidan "Xaritada ko'rish" bosilganda
            // shu voqeaga flyTo qilish uchun (frontend'da initTurkestanMap'ga uzatiladi).
            'focusEventSlug' => $request->string('event')->toString() ?: null,
            // Faza 15 §36: Qo'rboshi/Qo'zg'olon filtri tanlanganda ularga tegishli
            // koordinatalar (viloyat poligonini SVG tomonda "nuqta ichidami"
            // tekshiruvi orqali maxsus belgilash uchun) — frontend'ga xom nuqta
            // ro'yxati sifatida uzatiladi, aniq geometriyaga bog'liqlik yo'q.
            'highlightPoints' => $this->highlightPointsFor($qorboshiId, $uzgolonId),
            'regions' => Region::orderBy('name')->get(),
            'periods' => Period::orderBy('start_year')->get(),
            'qorboshilar' => Qorboshi::published()->orderBy('full_name')->get(['id', 'full_name']),
            'uzgolonlarList' => Uzgolon::published()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['region', 'period', 'qorboshi', 'uzgolon']),
            'seo' => [
                'title' => "Interaktiv xarita — Qo'rboshilar.uz",
                'description' => "Turkiston tarixidagi qo'zg'olonlarning interaktiv xaritasi va tarixiy xarita qatlamlari.",
                'canonical' => route('xarita'),
            ],
        ]);
    }

    /**
     * @return array<int, array{0: float, 1: float}>
     */
    private function highlightPointsFor(?int $qorboshiId, ?int $uzgolonId): array
    {
        $points = [];

        if ($qorboshiId) {
            $qorboshi = Qorboshi::with('uzgolonlar.primaryMarker')->find($qorboshiId);

            foreach ($qorboshi?->uzgolonlar ?? [] as $uzgolon) {
                if ($uzgolon->primaryMarker) {
                    $points[] = [(float) $uzgolon->primaryMarker->longitude, (float) $uzgolon->primaryMarker->latitude];
                }
            }
        }

        if ($uzgolonId) {
            $uzgolon = Uzgolon::with('primaryMarker')->find($uzgolonId);

            if ($uzgolon?->primaryMarker) {
                $points[] = [(float) $uzgolon->primaryMarker->longitude, (float) $uzgolon->primaryMarker->latitude];
            }
        }

        return $points;
    }
}
