<?php

namespace App\Services;

use App\Models\Period;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeriodService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Period::query()
            ->withCount(['uzgolonlar', 'historicalRegions', 'historicalMapLayers', 'timelineEvents'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('slug', 'like', $term));
            })
            ->orderBy('start_year')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Period
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);

        return Period::create($this->onlyModelFields($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Period $period, array $data): Period
    {
        // Region/Uzgolon'dagi kabi: bo'sh slug mavjud slug'ni saqlaydi, faqat admin
        // qo'lda o'zgartirsa o'zgaradi — bog'liq entitylarning public URL'i barqaror qoladi.
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $period->slug, $data['name'], $period->id);

        $period->update($this->onlyModelFields($data));

        return $period->fresh();
    }

    public function delete(Period $period): void
    {
        $period->delete();
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 1;

        while (
            Period::query()
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
        return array_intersect_key($data, array_flip((new Period)->getFillable()));
    }
}
