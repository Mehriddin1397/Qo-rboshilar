<?php

namespace App\Services;

use App\Models\TimelineEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimelineEventService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return TimelineEvent::query()
            ->with(['period', 'qorboshi', 'uzgolon', 'historicalRegion'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('title', 'like', $term)->orWhere('slug', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')))
            ->when($request->filled('period_id'), fn ($q) => $q->where('period_id', $request->integer('period_id')))
            ->when($request->filled('year'), fn ($q) => $this->applyYearOverlap($q, $request->integer('year'), $request->integer('year')))
            ->orderBy('sort_order')
            ->orderBy('start_year')
            ->paginate(15)
            ->withQueryString();
    }

    public function paginateForPublic(Request $request): LengthAwarePaginator
    {
        return TimelineEvent::query()
            ->published()
            ->with([
                'period',
                'qorboshi' => fn ($q) => $q->published(),
                'uzgolon' => fn ($q) => $q->published(),
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($sub) => $sub->where('title', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->when($request->filled('period'), fn ($q) => $q->where('period_id', $request->integer('period')))
            ->when($request->filled('from_year'), fn ($q) => $this->applyFromYear($q, $request->integer('from_year')))
            ->when($request->filled('to_year'), fn ($q) => $this->applyToYear($q, $request->integer('to_year')))
            ->orderBy('start_year')
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();
    }

    public function findPublishedBySlugOrFail(string $slug): TimelineEvent
    {
        return TimelineEvent::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'period',
                'qorboshi' => fn ($q) => $q->published(),
                'uzgolon' => fn ($q) => $q->published(),
                'historicalRegion' => fn ($q) => $q->published(),
                'sourceReferences',
            ])
            ->firstOrFail();
    }

    /**
     * §12: xarita bilan sinxronizatsiya uchun — faqat koordinataga ega, published
     * eventlar. GeoJSON formatlash `HistoricalMapService`da (Faza 13 §45 markazi).
     */
    public function getPublishedWithCoordinates(?int $periodId = null): Collection
    {
        return TimelineEvent::query()
            ->published()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['period', 'qorboshi' => fn ($q) => $q->published(), 'uzgolon' => fn ($q) => $q->published()])
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->orderBy('sort_order')
            ->get();
    }

    public function homepagePreview(int $limit = 8): Collection
    {
        return TimelineEvent::query()
            ->published()
            ->orderBy('start_year')
            ->orderBy('sort_order')
            ->take($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TimelineEvent
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title']);

        $event = TimelineEvent::create($this->onlyModelFields($data));

        $this->syncRelations($event, $data);

        return $event;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TimelineEvent $event, array $data): TimelineEvent
    {
        // Qorboshi'da (Faza 8) tuzatilgan bug qayta kiritilmaydi: update'da bo'sh
        // slug mavjud slug'ni saqlaydi.
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $event->slug, $data['title'], $event->id);

        $event->update($this->onlyModelFields($data));

        $this->syncRelations($event, $data);

        return $event->fresh();
    }

    public function delete(TimelineEvent $event): void
    {
        $event->comments()->delete();
        $event->delete();
    }

    private function applyFromYear(Builder $query, int $fromYear): Builder
    {
        return $query->whereRaw('COALESCE(end_year, start_year) >= ?', [$fromYear]);
    }

    private function applyToYear(Builder $query, int $toYear): Builder
    {
        return $query->where('start_year', '<=', $toYear);
    }

    private function applyYearOverlap(Builder $query, int $fromYear, int $toYear): Builder
    {
        return $this->applyToYear($this->applyFromYear($query, $fromYear), $toYear);
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $suffix = 1;

        while (
            TimelineEvent::query()
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
    private function syncRelations(TimelineEvent $event, array $data): void
    {
        $event->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new TimelineEvent)->getFillable()));
    }
}
