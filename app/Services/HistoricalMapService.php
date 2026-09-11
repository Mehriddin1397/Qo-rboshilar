<?php

namespace App\Services;

use App\Models\HistoricalMapLayer;
use App\Models\HistoricalRegion;
use App\Models\TimelineEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Faza 13 §45: public tarixiy xarita uchun barcha query/formatlash logikasi shu
 * yerda — Controller faqat chaqiradi. Faqat `published` + haqiqiy geometriyaga
 * ega yozuvlar qaytariladi (draft/pending/rejected hech qachon public'ga chiqmaydi,
 * §14, §39). Faza 14 §12-13: TimelineEvent nuqta-geometriyasi shu yerga qo'shildi
 * — bir xil "controller faqat chaqiradi" tamoyili.
 */
class HistoricalMapService
{
    public function __construct(private readonly TimelineEventService $timelineEventService)
    {
    }

    public function getPublishedRegions(?int $periodId = null): Collection
    {
        return HistoricalRegion::query()
            ->published()
            ->whereNotNull('geojson')
            ->with(['period', 'region', 'sourceReferences'])
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->orderBy('sort_order')
            ->get();
    }

    public function getPublishedLayers(?int $periodId = null): Collection
    {
        return HistoricalMapLayer::query()
            ->published()
            ->whereNotNull('geojson')
            ->with(['period', 'historicalRegion', 'sourceReferences'])
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->orderBy('sort_order')
            ->get();
    }

    public function getPublishedRasterLayers(?int $periodId = null): Collection
    {
        return HistoricalMapLayer::query()
            ->published()
            ->whereNotNull('image_path')
            ->whereNotNull('bounds')
            ->with('period')
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{type: string, features: array<int, mixed>}
     */
    public function regionsGeoJson(?int $periodId = null): array
    {
        $features = $this->getPublishedRegions($periodId)
            ->flatMap(fn (HistoricalRegion $region) => $this->toFeatures($region->geojson, [
                'id' => $region->id,
                'name' => $region->name,
                'historicalName' => $region->historical_name,
                'modernName' => $region->modern_name,
                'regionType' => $region->region_type->label(),
                'period' => $region->period?->name,
                'accuracyStatus' => $region->accuracy_status->value,
                'accuracyLabel' => $region->accuracy_status->label(),
                'description' => $region->description,
                'sourceSummary' => $this->sourceSummary($region->sourceReferences),
                'sourceUrl' => $this->firstSafeSourceUrl($region->sourceReferences),
            ]))
            ->values()
            ->all();

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /**
     * @return array{type: string, features: array<int, mixed>}
     */
    public function layersGeoJson(?int $periodId = null): array
    {
        $features = $this->getPublishedLayers($periodId)
            ->flatMap(fn (HistoricalMapLayer $layer) => $this->toFeatures($layer->geojson, [
                'id' => $layer->id,
                'name' => $layer->title,
                'historicalRegion' => $layer->historicalRegion?->name,
                'period' => $layer->period?->name,
                'accuracyStatus' => $layer->accuracy_status->value,
                'accuracyLabel' => $layer->accuracy_status->label(),
                'description' => $layer->description,
                'sourceSummary' => $this->sourceSummary($layer->sourceReferences),
                'sourceUrl' => $this->firstSafeSourceUrl($layer->sourceReferences),
            ]))
            ->values()
            ->all();

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /**
     * Faza 14 §12: TimelineEvent nuqta-geometriyasi — event.latitude/longitude
     * mavjud bo'lgan published eventlar Point Feature sifatida. Bog'liq Qorboshi/
     * Uzgolon draft bo'lsa, eager-load'dagi `published()` cheklovi tufayli null
     * bo'lib qoladi — public sahifada hech qachon draft entityga havola chiqmaydi
     * (§23, Faza 10 bug oldini olish qoidasi).
     *
     * @return array{type: string, features: array<int, mixed>}
     */
    public function timelineEventsGeoJson(?int $periodId = null): array
    {
        $features = $this->timelineEventService->getPublishedWithCoordinates($periodId)
            ->map(fn (TimelineEvent $event) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $event->longitude, (float) $event->latitude],
                ],
                'properties' => [
                    'id' => $event->id,
                    'slug' => $event->slug,
                    'title' => $event->title,
                    'yearRange' => $event->yearRangeLabel(),
                    'period' => $event->period?->name,
                    'qorboshi' => $event->qorboshi?->full_name,
                    'uzgolon' => $event->uzgolon?->name,
                    'accuracyStatus' => $event->accuracy_status->value,
                    'accuracyLabel' => $event->accuracy_status->label(),
                    'description' => $event->description,
                    'url' => route('xronologiya.show', $event->slug),
                ],
            ])
            ->values()
            ->all();

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /**
     * Raster overlaylar GeoJSON emas — MapLibre `ImageSource` uchun mos kichik
     * massiv sifatida qaytariladi (§36-37).
     *
     * @return array<int, array<string, mixed>>
     */
    public function rasterLayersData(?int $periodId = null): array
    {
        return $this->getPublishedRasterLayers($periodId)
            ->map(fn (HistoricalMapLayer $layer) => [
                'id' => $layer->id,
                'title' => $layer->title,
                'imageUrl' => $layer->imageUrl(),
                'bounds' => $layer->bounds,
                'opacity' => $layer->opacity,
                'accuracyStatus' => $layer->accuracy_status->value,
            ])
            ->values()
            ->all();
    }

    /**
     * Bitta modelning (o'zi Feature/FeatureCollection/Polygon/MultiPolygon bo'lishi
     * mumkin bo'lgan) geojson ustunini bitta yoki bir nechta MapLibre Feature'ga
     * aylantiradi, har biriga bir xil $properties'ni qo'shib.
     *
     * @param  array<string, mixed>|null  $geojson
     * @param  array<string, mixed>  $properties
     * @return array<int, array<string, mixed>>
     */
    private function toFeatures(?array $geojson, array $properties): array
    {
        if (! $geojson) {
            return [];
        }

        return match ($geojson['type'] ?? null) {
            'FeatureCollection' => collect($geojson['features'] ?? [])
                ->map(fn ($feature) => [
                    'type' => 'Feature',
                    'geometry' => $feature['geometry'] ?? null,
                    'properties' => array_merge($properties, $feature['properties'] ?? []),
                ])
                ->all(),
            'Feature' => [[
                'type' => 'Feature',
                'geometry' => $geojson['geometry'] ?? null,
                'properties' => array_merge($properties, $geojson['properties'] ?? []),
            ]],
            'Polygon', 'MultiPolygon' => [[
                'type' => 'Feature',
                'geometry' => $geojson,
                'properties' => $properties,
            ]],
            default => [],
        };
    }

    /**
     * @param  Collection<int, Model&\App\Models\SourceReference>  $sources
     */
    private function sourceSummary(Collection $sources): ?string
    {
        if ($sources->isEmpty()) {
            return null;
        }

        return $sources->map(fn ($source) => trim("{$source->author} — {$source->title}"))->implode('; ');
    }

    /**
     * @param  Collection<int, Model&\App\Models\SourceReference>  $sources
     */
    private function firstSafeSourceUrl(Collection $sources): ?string
    {
        return $sources->pluck('url')->first(fn (?string $url) => $this->isSafeUrl($url));
    }

    /**
     * §25: `javascript:` yoki boshqa xavfli sxema hech qachon public'ga chiqmasin —
     * faqat http(s) qabul qilinadi.
     */
    private function isSafeUrl(?string $url): bool
    {
        return $url !== null && (bool) preg_match('#^https?://#i', $url);
    }
}
