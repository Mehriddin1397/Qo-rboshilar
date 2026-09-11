<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\TimelineEvent;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Support\Collection;

/**
 * Production QA §12: sitemap faqat public/published sahifalarni o'z ichiga
 * oladi — draft/pending/rejected URL'lar hech qachon kiritilmaydi. Faqat
 * `slug`/`updated_at` tanlab olinadi (select minimal — katta jadvallarda ham
 * arzon so'rov).
 */
class SitemapService
{
    /**
     * @return array<int, array{url: string, lastmod: ?string}>
     */
    public function urls(): array
    {
        $entries = collect([
            ['url' => route('home'), 'lastmod' => null],
            ['url' => route('qorboshilar.index'), 'lastmod' => null],
            ['url' => route('qozgolonlar.index'), 'lastmod' => null],
            ['url' => route('adabiyotlar.index'), 'lastmod' => null],
            ['url' => route('videolar.index'), 'lastmod' => null],
            ['url' => route('bloglar.index'), 'lastmod' => null],
            ['url' => route('xronologiya.index'), 'lastmod' => null],
            ['url' => route('xarita'), 'lastmod' => null],
            ['url' => route('contact'), 'lastmod' => null],
        ]);

        $entries = $entries
            ->merge($this->fromModel(Qorboshi::class, 'qorboshilar.show'))
            ->merge($this->fromModel(Uzgolon::class, 'qozgolonlar.show'))
            ->merge($this->fromModel(Literature::class, 'adabiyotlar.show'))
            ->merge($this->fromModel(Video::class, 'videolar.show'))
            ->merge($this->fromModel(Blog::class, 'bloglar.show'))
            ->merge($this->fromModel(TimelineEvent::class, 'xronologiya.show'));

        return $entries->values()->all();
    }

    /**
     * @param  class-string  $modelClass
     * @return Collection<int, array{url: string, lastmod: string}>
     */
    private function fromModel(string $modelClass, string $routeName): Collection
    {
        return $modelClass::query()
            ->published()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->map(fn ($model) => [
                'url' => route($routeName, $model->slug),
                'lastmod' => $model->updated_at?->toAtomString(),
            ]);
    }
}
