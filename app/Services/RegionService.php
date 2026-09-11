<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegionService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Region::query()
            ->withCount(['qorboshilar', 'uzgolonlar'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('slug', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Region
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);

        return Region::create($this->onlyModelFields($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Region $region, array $data): Region
    {
        // Uzgolon/HistoricalRegion'dagi kabi: bo'sh slug mavjud slug'ni saqlaydi,
        // faqat admin qo'lda o'zgartirsa o'zgaradi — public URL barqaror qoladi.
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $region->slug, $data['name'], $region->id);

        $region->update($this->onlyModelFields($data));

        return $region->fresh();
    }

    public function delete(Region $region): void
    {
        $region->delete();
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 1;

        while (
            Region::query()
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
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new Region)->getFillable()));
    }
}
