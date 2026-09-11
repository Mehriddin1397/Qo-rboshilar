<?php

namespace App\Services;

use App\Models\HistoricalMapLayer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HistoricalMapLayerService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return HistoricalMapLayer::query()
            ->with(['period', 'historicalRegion'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('title', 'like', $term)->orWhere('slug', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('period_id'), fn ($q) => $q->where('period_id', $request->integer('period_id')))
            ->when($request->filled('historical_region_id'), fn ($q) => $q->where('historical_region_id', $request->integer('historical_region_id')))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): HistoricalMapLayer
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title']);
        $data = $this->normalizeGeoJson($data);
        $data = $this->normalizeBounds($data);

        $layer = HistoricalMapLayer::create($this->onlyModelFields($data));

        $this->syncRelations($layer, $data);

        return $layer;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(HistoricalMapLayer $layer, array $data): HistoricalMapLayer
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $layer->slug, $data['title'], $layer->id);
        $data = $this->normalizeGeoJson($data);
        $data = $this->normalizeBounds($data);

        $layer->update($this->onlyModelFields($data));

        $this->syncRelations($layer, $data);

        return $layer->fresh();
    }

    public function delete(HistoricalMapLayer $layer): void
    {
        if ($layer->image_path) {
            Storage::disk('public')->delete($layer->image_path);
        }

        $layer->delete();
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $suffix = 1;

        while (
            HistoricalMapLayer::query()
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
     * @return array<string, mixed>
     */
    private function normalizeBounds(array $data): array
    {
        if (array_key_exists('bounds', $data) && is_array($data['bounds'])) {
            $hasAnyValue = collect($data['bounds'])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
            $data['bounds'] = $hasAnyValue ? $data['bounds'] : null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRelations(HistoricalMapLayer $layer, array $data): void
    {
        $layer->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new HistoricalMapLayer)->getFillable()));
    }
}
