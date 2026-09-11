<?php

namespace App\Services;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\TimelineEvent;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Support\Collection;

class HomeService
{
    private const PREVIEW_COUNT = 3;

    public function featuredQorboshilar(): Collection
    {
        return Qorboshi::query()->published()->featured()->with('region')->latest()->take(self::PREVIEW_COUNT)->get();
    }

    public function featuredUzgolonlar(): Collection
    {
        return Uzgolon::query()->published()->featured()->with('region')->latest()->take(self::PREVIEW_COUNT)->get();
    }

    /**
     * Faza 14 §18: bu metod avval `status` ustuni mavjud bo'lmagani uchun barcha
     * (draft ham) eventlarni ko'rsatardi — endi TimelineEvent'da `published()`
     * scope mavjud, shuning uchun tuzatildi (aks holda draft voqealar bosh
     * sahifada oshkor bo'lardi).
     */
    public function timelinePreview(): Collection
    {
        return TimelineEvent::query()->published()->orderBy('start_year')->orderBy('sort_order')->take(8)->get();
    }

    public function latestVideos(): Collection
    {
        return Video::query()->published()->latest()->take(self::PREVIEW_COUNT)->get();
    }

    public function latestBlogs(): Collection
    {
        return Blog::query()
            ->with('author')
            ->where('status', BlogStatus::Approved)
            ->latest('published_at')
            ->take(self::PREVIEW_COUNT)
            ->get();
    }

    public function latestLiterature(): Collection
    {
        return Literature::query()->published()->latest()->take(self::PREVIEW_COUNT)->get();
    }
}
