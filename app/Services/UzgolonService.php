<?php

namespace App\Services;

use App\Enums\MapMarkerStatus;
use App\Enums\MapMarkerType;
use App\Models\Uzgolon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UzgolonService
{
    public function __construct(private readonly UzgolonMediaService $media)
    {
    }

    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Uzgolon::query()
            ->with(['region', 'period'])
            ->withCount('qorboshilar')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')))
            ->when($request->filled('region_id'), fn ($q) => $q->where('region_id', $request->integer('region_id')))
            ->when($request->filled('period_id'), fn ($q) => $q->where('period_id', $request->integer('period_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function paginateForPublic(Request $request): LengthAwarePaginator
    {
        return Uzgolon::query()
            ->published()
            ->with(['region', 'period'])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('region'), fn ($q) => $q->where('region_id', $request->integer('region')))
            ->when($request->filled('period'), fn ($q) => $q->where('period_id', $request->integer('period')))
            ->orderBy('start_year')
            ->paginate(12)
            ->withQueryString();
    }

    public function findPublishedBySlugOrFail(string $slug): Uzgolon
    {
        return Uzgolon::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'region', 'period', 'primaryMarker',
                'qorboshilar' => fn ($q) => $q->published()->with('region'),
                'literatures' => fn ($q) => $q->published(),
                'videos' => fn ($q) => $q->published(),
                'blogs' => fn ($q) => $q->published()->with('author'),
                'images', 'sourceReferences', 'timelineEvents',
            ])
            ->firstOrFail();
    }

    public function relatedTo(Uzgolon $uzgolon, int $limit = 3): Collection
    {
        $qorboshiIds = $uzgolon->qorboshilar->pluck('id');

        return Uzgolon::query()
            ->published()
            ->where('id', '!=', $uzgolon->id)
            ->where(function ($query) use ($uzgolon, $qorboshiIds) {
                $query->where('region_id', $uzgolon->region_id)
                    ->when($qorboshiIds->isNotEmpty(), fn ($q) => $q->orWhereHas(
                        'qorboshilar',
                        fn ($qq) => $qq->whereIn('qorboshilar.id', $qorboshiIds)
                    ));
            })
            ->with('region')
            ->take($limit)
            ->get();
    }

    /**
     * Xaritada ko'rsatiladigan barcha nashr etilgan qo'zg'olonlarni GeoJSON
     * FeatureCollection ko'rinishida qaytaradi (faqat markeri bor yozuvlar).
     *
     * @return array<string, mixed>
     */
    public function publicGeoJson(?int $regionId = null, ?int $periodId = null): array
    {
        $uzgolonlar = Uzgolon::query()
            ->published()
            ->whereHas('primaryMarker')
            ->with(['primaryMarker', 'region'])
            ->when($regionId, fn ($q) => $q->where('region_id', $regionId))
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->get();

        return [
            'type' => 'FeatureCollection',
            'features' => $uzgolonlar->map(fn (Uzgolon $uzgolon) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $uzgolon->primaryMarker->longitude, (float) $uzgolon->primaryMarker->latitude],
                ],
                'properties' => [
                    'title' => $uzgolon->name,
                    'startYear' => $uzgolon->start_year,
                    'endYear' => $uzgolon->end_year,
                    'region' => $uzgolon->region?->name,
                    'shortDescription' => $uzgolon->short_description,
                    'url' => route('qozgolonlar.show', $uzgolon),
                ],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Uzgolon
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);

        $uzgolon = Uzgolon::create($this->onlyModelFields($data));

        $this->syncRelations($uzgolon, $data);
        $this->syncPrimaryMarker($uzgolon, $data);

        return $uzgolon;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Uzgolon $uzgolon, array $data): Uzgolon
    {
        // Qorboshi'dagi kabi: agar admin slug'ni qo'lda o'zgartirmasa, mavjud slug
        // saqlanadi — nom o'zgarganda ham public URL barqaror qoladi.
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $uzgolon->slug, $data['name'], $uzgolon->id);

        $uzgolon->update($this->onlyModelFields($data));

        $this->syncRelations($uzgolon, $data);
        $this->syncPrimaryMarker($uzgolon, $data);

        return $uzgolon->fresh();
    }

    public function delete(Uzgolon $uzgolon): void
    {
        $this->media->deleteAllMedia($uzgolon);

        // Comment — polymorphic, DB FK cascade yo'q, orphan qoldirmaslik uchun qo'lda o'chiriladi.
        $uzgolon->comments()->delete();

        $uzgolon->delete();
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 1;

        while (
            Uzgolon::query()
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
     */
    private function syncRelations(Uzgolon $uzgolon, array $data): void
    {
        $uzgolon->qorboshilar()->sync($data['qorboshi_ids'] ?? []);
        $uzgolon->literatures()->sync($data['literature_ids'] ?? []);
        $uzgolon->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncPrimaryMarker(Uzgolon $uzgolon, array $data): void
    {
        $hasCoordinates = ! empty($data['latitude']) && ! empty($data['longitude']);

        if (! $hasCoordinates) {
            $uzgolon->primaryMarker?->delete();

            return;
        }

        $uzgolon->primaryMarker()->updateOrCreate([], [
            'title' => $uzgolon->name,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'type' => MapMarkerType::Uprising,
            'description' => $uzgolon->short_description,
            'is_primary' => true,
            'status' => MapMarkerStatus::Published,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new Uzgolon)->getFillable()));
    }
}
