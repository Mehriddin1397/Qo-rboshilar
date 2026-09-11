<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use App\Services\UzgolonService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $home,
        private readonly UzgolonService $uzgolonService,
    ) {
    }

    public function index(): View
    {
        return view('pages.home', [
            'qorboshilar' => $this->home->featuredQorboshilar(),
            'uzgolonlar' => $this->home->featuredUzgolonlar(),
            'timelineEvents' => $this->home->timelinePreview(),
            'videos' => $this->home->latestVideos(),
            'blogs' => $this->home->latestBlogs(),
            'literature' => $this->home->latestLiterature(),
            'mapGeoJson' => $this->uzgolonService->publicGeoJson(),
            'seo' => [
                'title' => "Qo'rboshilar.uz — Turkiston ozodligi yo'lida kurashganlar tarixi",
                'description' => "Turkiston tarixidagi qo'rboshilar, qo'zg'olonlar, tarixiy manbalar va videolarni birlashtirgan raqamli tarixiy ensiklopediya va interaktiv arxiv.",
                'canonical' => route('home'),
            ],
        ]);
    }
}
