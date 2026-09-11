<?php

namespace App\Services;

use App\Models\MapMarker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapMarkerService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return MapMarker::query()
            ->with('uzgolon')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('uzgolon_id'), fn ($q) => $q->where('uzgolon_id', $request->integer('uzgolon_id')))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MapMarker
    {
        return DB::transaction(function () use ($data) {
            $data = $this->normalizePrimaryFlag($data);

            $marker = MapMarker::create($this->onlyModelFields($data));

            $this->demoteOtherPrimaries($marker);

            return $marker;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MapMarker $marker, array $data): MapMarker
    {
        return DB::transaction(function () use ($marker, $data) {
            $data = $this->normalizePrimaryFlag($data);

            $marker->update($this->onlyModelFields($data));

            $this->demoteOtherPrimaries($marker);

            return $marker->fresh();
        });
    }

    public function delete(MapMarker $marker): void
    {
        $marker->delete();
    }

    /**
     * Bog'liq Qo'zg'olon tanlanmagan bo'lsa, "asosiy marker" tushunchasi ma'nosiz —
     * shu holatda is_primary har doim false'ga tushiriladi.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePrimaryFlag(array $data): array
    {
        if (empty($data['uzgolon_id'])) {
            $data['is_primary'] = false;
        }

        return $data;
    }

    /**
     * §6: bitta Qo'zg'olon uchun bir vaqtning o'zida faqat bitta primary marker
     * bo'lishi kerak — yangi marker primary qilib belgilansa, o'sha uzgolon_id'ga
     * tegishli boshqa barcha markerlar avtomatik ravishda primary emas qilinadi.
     */
    private function demoteOtherPrimaries(MapMarker $marker): void
    {
        if (! $marker->is_primary || ! $marker->uzgolon_id) {
            return;
        }

        MapMarker::query()
            ->where('uzgolon_id', $marker->uzgolon_id)
            ->where('id', '!=', $marker->id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    /**
     * Mustaqil (standalone) markerlar uchun public GeoJSON — Uzgolon'ning "primary"
     * markeri allaqachon UzgolonService::publicGeoJson() orqali chiqariladi, shu
     * uchun bu yerda faqat is_primary=false yoki uzgolon'ga bog'lanmagan, published
     * va valid koordinataga ega markerlar qaytariladi (dublikat oldini olish uchun).
     *
     * @return array<string, mixed>
     */
    public function publicGeoJson(?int $regionId = null, ?int $periodId = null): array
    {
        $markers = MapMarker::query()
            ->published()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(fn ($q) => $q->where('is_primary', false)->orWhereNull('uzgolon_id'))
            ->where(fn ($q) => $q->whereNull('uzgolon_id')->orWhereHas('uzgolon', fn ($u) => $u->published()))
            ->when($regionId, fn ($q) => $q->whereHas('uzgolon', fn ($u) => $u->where('region_id', $regionId)))
            ->when($periodId, fn ($q) => $q->whereHas('uzgolon', fn ($u) => $u->where('period_id', $periodId)))
            ->with(['uzgolon.region'])
            ->get();

        return [
            'type' => 'FeatureCollection',
            'features' => $markers->map(fn (MapMarker $marker) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $marker->longitude, (float) $marker->latitude],
                ],
                'properties' => [
                    'title' => $marker->title,
                    'startYear' => $marker->uzgolon?->start_year,
                    'endYear' => $marker->uzgolon?->end_year,
                    'region' => $marker->uzgolon?->region?->name,
                    'shortDescription' => $marker->description,
                    'url' => $marker->uzgolon ? route('qozgolonlar.show', $marker->uzgolon) : null,
                ],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new MapMarker)->getFillable()));
    }
}
