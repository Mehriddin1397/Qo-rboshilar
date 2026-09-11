<?php

namespace App\Services;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogService
{
    public function __construct(private readonly BlogMediaService $media)
    {
    }

    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Blog::query()
            ->with('author')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $term = '%'.$request->string('search').'%';
                $sub->where('title', 'like', $term)
                    ->orWhereHas('author', fn ($a) => $a->where('name', 'like', $term));
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function paginateForPublic(Request $request): LengthAwarePaginator
    {
        return Blog::query()
            ->published()
            ->with('author')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function paginateForAuthor(User $author): LengthAwarePaginator
    {
        return Blog::query()
            ->where('author_id', $author->id)
            ->latest()
            ->paginate(15);
    }

    public function findPublishedBySlugOrFail(string $slug): Blog
    {
        return Blog::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'author',
                'qorboshilar' => fn ($q) => $q->published(),
                'uzgolonlar' => fn ($q) => $q->published(),
                'literatures' => fn ($q) => $q->published(),
                'videos' => fn ($q) => $q->published(),
                'sourceReferences',
            ])
            ->firstOrFail();
    }

    public function relatedTo(Blog $blog, int $limit = 3): Collection
    {
        return Blog::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    public function incrementViews(Blog $blog): void
    {
        $blog->increment('views');
    }

    /**
     * Admin panel orqali — barcha maydonlar (status, muallif) to'liq nazorat qilinadi.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAsAdmin(array $data): Blog
    {
        return $this->createInternal($data);
    }

    /**
     * "Mening bloglarim" orqali — status har doim DRAFT, muallif har doim joriy user
     * (xavfsizlik: foydalanuvchi hech qachon o'zini boshqa muallif yoki status
     * sifatida ko'rsata olmaydi, chunki bu maydonlar so'rovdan kelmaydi).
     *
     * @param  array<string, mixed>  $data
     */
    public function createAsAuthor(User $author, array $data): Blog
    {
        $data['author_id'] = $author->id;
        $data['status'] = BlogStatus::Draft;

        return $this->createInternal($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAsAdmin(Blog $blog, array $data): Blog
    {
        return $this->updateInternal($blog, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAsAuthor(Blog $blog, array $data): Blog
    {
        unset($data['status'], $data['author_id']);

        return $this->updateInternal($blog, $data);
    }

    public function submit(Blog $blog): Blog
    {
        $blog->update(['status' => BlogStatus::Pending]);

        return $blog->fresh();
    }

    public function approve(Blog $blog): Blog
    {
        $blog->update(['status' => BlogStatus::Approved, 'published_at' => now()]);

        return $blog->fresh();
    }

    public function reject(Blog $blog): Blog
    {
        $blog->update(['status' => BlogStatus::Rejected]);

        return $blog->fresh();
    }

    public function delete(Blog $blog): void
    {
        $this->media->deleteAllMedia($blog);
        $blog->comments()->delete();
        $blog->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createInternal(array $data): Blog
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title']);

        $blog = Blog::create($this->onlyModelFields($data));

        $this->syncRelations($blog, $data);

        return $blog;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateInternal(Blog $blog, array $data): Blog
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $blog->slug, $data['title'], $blog->id);

        $blog->update($this->onlyModelFields($data));

        $this->syncRelations($blog, $data);

        return $blog->fresh();
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $suffix = 1;

        while (
            Blog::query()
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
    private function syncRelations(Blog $blog, array $data): void
    {
        $blog->qorboshilar()->sync($data['qorboshi_ids'] ?? []);
        $blog->uzgolonlar()->sync($data['uzgolon_ids'] ?? []);
        $blog->literatures()->sync($data['literature_ids'] ?? []);
        $blog->videos()->sync($data['video_ids'] ?? []);
        $blog->sourceReferences()->sync($data['source_reference_ids'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyModelFields(array $data): array
    {
        return array_intersect_key($data, array_flip((new Blog)->getFillable()));
    }
}
