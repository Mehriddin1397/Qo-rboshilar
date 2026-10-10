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
 * Texnik SEO & Google Indexing:
 * Sitemap faqat ommaviy/nashr etilgan sahifalarni to'liq absolyut URL bilan o'z ichiga oladi.
 * Har bir yozuv uchun <loc>, <lastmod>, <changefreq> va <priority> parametrlarini ta'minlaydi.
 */
class SitemapService
{
    /**
     * @return array<int, array{url: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function urls(): array
    {
        $today = now()->toAtomString();

        $entries = collect([
            [
                'url' => $this->canonicalUrl(route('home')),
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'url' => $this->canonicalUrl(route('qorboshilar.index')),
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'url' => $this->canonicalUrl(route('qozgolonlar.index')),
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'url' => $this->canonicalUrl(route('xarita')),
                'lastmod' => $today,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'url' => $this->canonicalUrl(route('xronologiya.index')),
                'lastmod' => $today,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'url' => $this->canonicalUrl(route('adabiyotlar.index')),
                'lastmod' => $today,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'url' => $this->canonicalUrl(route('videolar.index')),
                'lastmod' => $today,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'url' => $this->canonicalUrl(route('bloglar.index')),
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'url' => $this->canonicalUrl(route('about')),
                'lastmod' => $today,
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'url' => $this->canonicalUrl(route('contact')),
                'lastmod' => $today,
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
        ]);

        $entries = $entries
            ->merge($this->fromModel(Qorboshi::class, 'qorboshilar.show', 'weekly', '0.8'))
            ->merge($this->fromModel(Uzgolon::class, 'qozgolonlar.show', 'weekly', '0.8'))
            ->merge($this->fromModel(Blog::class, 'bloglar.show', 'weekly', '0.7'))
            ->merge($this->fromModel(Literature::class, 'adabiyotlar.show', 'monthly', '0.6'))
            ->merge($this->fromModel(Video::class, 'videolar.show', 'monthly', '0.6'))
            ->merge($this->fromModel(TimelineEvent::class, 'xronologiya.show', 'monthly', '0.6'));

        return $entries->values()->all();
    }

    /**
     * @param  class-string  $modelClass
     * @return Collection<int, array{url: string, lastmod: string, changefreq: string, priority: string}>
     */
    private function fromModel(string $modelClass, string $routeName, string $changefreq, string $priority): Collection
    {
        return $modelClass::query()
            ->published()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->map(fn ($model) => [
                'url' => $this->canonicalUrl(route($routeName, $model->slug)),
                'lastmod' => $model->updated_at?->toAtomString() ?? now()->toAtomString(),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ]);
    }

    private function canonicalUrl(string $url): string
    {
        $appUrl = config('app.url');
        if ($appUrl && str_contains($appUrl, 'qorboshilar.uz')) {
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? '/';
            return 'https://qorboshilar.uz' . $path;
        }

        return $url;
    }
}
