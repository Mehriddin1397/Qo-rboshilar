<?php

namespace App\Services;

use App\Models\Literature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiteratureService
{
    public function __construct(private readonly LiteratureMediaService $media)
    {
    }

    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Literature::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $term = '%'.$request->string('search').'%';
                $sub->where('title', 'like', $term)->orWhere('author', 'like', $term);
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function paginateForPublic(Request $request): LengthAwarePaginator
    {
        return Literature::query()
            ->published()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
                $term = '%'.$request->string('q').'%';
                $sub->where('title', 'like', $term)->orWhere('author', 'like', $term);
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function findPublishedBySlugOrFail(string $slug): Literature
    {
        return Literature::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'qorboshilar' => fn ($q) => $q->published()->with('region'),
                'uzgolonlar' => fn ($q) => $q->published()->with('region'),
                'videos' => fn ($q) => $q->published(),
                'sourceReferences',
            ])
            ->firstOrFail();
    }

    public function relatedTo(Literature $literature, int $limit = 3): Collection
    {
        return Literature::query()
            ->published()
            ->where('id', '!=', $literature->id)
            ->where('type', $literature->type)
            ->take($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Literature
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title']);

        $literature = Literature::create($this->onlyModelFields($data));

        $this->syncRelations($literature, $data);

        return $literature;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Literature $literature, array $data): Literature
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $literature->slug, $data['title'], $literature->id);

        $literature->update($this->onlyModelFields($data));

        $this->syncRelations($literature, $data);

        return $literature->fresh();
    }

    public function delete(Literature $literature): void
    {
        $this->media->deleteAllMedia($literature);
        $literature->comments()->delete();
        $literature->delete();
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $suffix = 1;

        while (
            Literature::query()
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
    private function syncRelations(Literature $literature, array $data): void
    {
        $literature->qorboshilar()->sync($data['qorboshi_ids'] ?? []);
        $literature->uzgolonlar()->sync($data['uzgolon_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new Literature)->getFillable()));
    }
}
