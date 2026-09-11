<?php

namespace App\Services;

use App\Models\Qorboshi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QorboshiService
{
    public function __construct(private readonly QorboshiMediaService $media)
    {
    }

    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Qorboshi::query()
            ->with('region')
            ->when($request->filled('search'), fn ($q) => $q->where('full_name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')))
            ->when($request->filled('region_id'), fn ($q) => $q->where('region_id', $request->integer('region_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function paginateForPublic(Request $request): LengthAwarePaginator
    {
        return Qorboshi::query()
            ->published()
            ->with('region')
            ->when($request->filled('q'), fn ($q) => $q->where('full_name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('region'), fn ($q) => $q->where('region_id', $request->integer('region')))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function findPublishedBySlugOrFail(string $slug): Qorboshi
    {
        return Qorboshi::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'region',
                'uzgolonlar' => fn ($q) => $q->published()->with('region'),
                'literatures' => fn ($q) => $q->published(),
                'videos' => fn ($q) => $q->published(),
                'blogs' => fn ($q) => $q->published()->with('author'),
                'images',
                'sourceReferences',
            ])
            ->firstOrFail();
    }

    public function relatedTo(Qorboshi $qorboshi, int $limit = 3): Collection
    {
        $uprisingIds = $qorboshi->uzgolonlar->pluck('id');

        return Qorboshi::query()
            ->published()
            ->where('id', '!=', $qorboshi->id)
            ->where(function ($query) use ($qorboshi, $uprisingIds) {
                $query->where('region_id', $qorboshi->region_id)
                    ->when($uprisingIds->isNotEmpty(), fn ($q) => $q->orWhereHas(
                        'uzgolonlar',
                        fn ($uq) => $uq->whereIn('uzgolonlar.id', $uprisingIds)
                    ));
            })
            ->with('region')
            ->take($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Qorboshi
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['full_name']);

        $qorboshi = Qorboshi::create($this->onlyModelFields($data));

        $this->syncRelations($qorboshi, $data);

        return $qorboshi;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Qorboshi $qorboshi, array $data): Qorboshi
    {
        // Agar admin slug'ni qo'lda o'zgartirmasa, mavjud slug saqlanadi — ism
        // o'zgarganda ham public URL (SEO, tashqi havolalar) buzilmasligi kerak.
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $qorboshi->slug, $data['full_name'], $qorboshi->id);

        $qorboshi->update($this->onlyModelFields($data));

        $this->syncRelations($qorboshi, $data);

        return $qorboshi->fresh();
    }

    public function delete(Qorboshi $qorboshi): void
    {
        $this->media->deleteAllMedia($qorboshi);

        // Comment — polymorphic, DB darajasida FK cascade yo'q (commentable_type/id
        // haqiqiy foreign key emas), shuning uchun orphan qoldirmaslik uchun qo'lda o'chiramiz.
        $qorboshi->comments()->delete();

        $qorboshi->delete();
    }

    private function resolveSlug(?string $slug, string $fullName, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $fullName);
        $candidate = $base;
        $suffix = 1;

        while (
            Qorboshi::query()
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
    private function syncRelations(Qorboshi $qorboshi, array $data): void
    {
        $qorboshi->uzgolonlar()->sync($data['uzgolon_ids'] ?? []);
        $qorboshi->literatures()->sync($data['literature_ids'] ?? []);
        $qorboshi->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new Qorboshi)->getFillable()));
    }
}
