<?php

namespace App\Services;

use App\Models\Video;
use App\Support\YoutubeUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoService
{
    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Video::query()
            ->with(['qorboshi', 'uzgolon'])
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function paginateForPublic(Request $request): LengthAwarePaginator
    {
        return Video::query()
            ->published()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function findPublishedBySlugOrFail(string $slug): Video
    {
        return Video::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'qorboshi' => fn ($q) => $q->published()->with('region'),
                'uzgolon' => fn ($q) => $q->published()->with('region'),
                'literature' => fn ($q) => $q->published(),
                'sourceReferences',
            ])
            ->firstOrFail();
    }

    public function relatedTo(Video $video, int $limit = 3): Collection
    {
        return Video::query()
            ->published()
            ->where('id', '!=', $video->id)
            ->where('category', $video->category)
            ->take($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Video
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title']);
        $data['youtube_id'] = YoutubeUrl::extractId($data['youtube_url']);

        $video = Video::create($this->onlyModelFields($data));

        $this->syncSourceReferences($video, $data);

        return $video;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Video $video, array $data): Video
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $video->slug, $data['title'], $video->id);
        $data['youtube_id'] = YoutubeUrl::extractId($data['youtube_url']);

        $video->update($this->onlyModelFields($data));

        $this->syncSourceReferences($video, $data);

        return $video->fresh();
    }

    public function delete(Video $video): void
    {
        $video->comments()->delete();
        $video->delete();
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $suffix = 1;

        while (
            Video::query()
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
    private function syncSourceReferences(Video $video, array $data): void
    {
        $video->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new Video)->getFillable()));
    }
}
