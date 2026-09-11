<?php

namespace App\Services;

use App\Models\SourceReference;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class SourceReferenceService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return SourceReference::query()
            ->with('literature')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('author', 'like', $term)->orWhere('title', 'like', $term));
            })
            ->when($request->filled('source_type'), fn ($q) => $q->where('source_type', $request->string('source_type')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SourceReference
    {
        return SourceReference::create($this->onlyModelFields($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SourceReference $sourceReference, array $data): SourceReference
    {
        $sourceReference->update($this->onlyModelFields($data));

        return $sourceReference->fresh();
    }

    public function delete(SourceReference $sourceReference): void
    {
        $sourceReference->delete();
    }

    /**
     * §"Manba talab qilinadi" qoidasi (HistoricalRegion/HistoricalMapLayer publish
     * validatsiyasi) faqat yozish vaqtida tekshiriladi — shu manba o'chirilsa, unga
     * asoslangan published yozuvlar sukut bo'yicha manbasiz qolib ketishi mumkin.
     * Shu uchun biror sourceable'ga bog'langan manbani o'chirish taqiqlanadi.
     *
     * @return array<string, int>
     */
    public function relatedCounts(SourceReference $sourceReference): array
    {
        return [
            "qo'rboshi" => $sourceReference->qorboshilar()->count(),
            "qo'zg'olon" => $sourceReference->uzgolonlar()->count(),
            'video' => $sourceReference->videos()->count(),
            'blog' => $sourceReference->blogs()->count(),
            'tarixiy hudud' => $sourceReference->historicalRegions()->count(),
            'xarita qatlami' => $sourceReference->historicalMapLayers()->count(),
            'xronologiya voqeasi' => $sourceReference->timelineEvents()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new SourceReference)->getFillable()));
    }
}
