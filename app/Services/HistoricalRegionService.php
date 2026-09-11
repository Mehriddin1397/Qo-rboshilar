<?php

namespace App\Services;

use App\Models\HistoricalRegion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HistoricalRegionService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return HistoricalRegion::query()
            ->with(['period', 'region'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('slug', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('period_id'), fn ($q) => $q->where('period_id', $request->integer('period_id')))
            ->when($request->filled('region_id'), fn ($q) => $q->where('region_id', $request->integer('region_id')))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): HistoricalRegion
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);
        $data = $this->normalizeGeoJson($data);

        $historicalRegion = HistoricalRegion::create($this->onlyModelFields($data));

        $this->syncRelations($historicalRegion, $data);

        return $historicalRegion;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(HistoricalRegion $historicalRegion, array $data): HistoricalRegion
    {
        // Qorboshi'da (Faza 8) tuzatilgan bug qayta kiritilmasin: update'da bo'sh
        // slug mavjud slug'ni saqlaydi, faqat admin qo'lda o'zgartirsa o'zgaradi.
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $historicalRegion->slug, $data['name'], $historicalRegion->id);
        $data = $this->normalizeGeoJson($data);

        $historicalRegion->update($this->onlyModelFields($data));

        $this->syncRelations($historicalRegion, $data);

        return $historicalRegion->fresh();
    }

    public function delete(HistoricalRegion $historicalRegion): void
    {
        // §43: bog'langan HistoricalMapLayer'lar DB darajasida nullOnDelete orqali
        // faqat historical_region_id'ni null qiladi — tarixiy layer ma'lumoti
        // (geojson, manba, davr) tasodifan cascade bilan yo'qolmaydi.
        $historicalRegion->delete();
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 1;

        while (
            HistoricalRegion::query()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeGeoJson(array $data): array
    {
        if (array_key_exists('geojson', $data)) {
            $data['geojson'] = filled($data['geojson']) ? json_decode($data['geojson'], true) : null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRelations(HistoricalRegion $historicalRegion, array $data): void
    {
        $historicalRegion->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new HistoricalRegion)->getFillable()));
    }
}
