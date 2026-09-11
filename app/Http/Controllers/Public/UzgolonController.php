<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Region;
use App\Services\CommentService;
use App\Services\UzgolonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UzgolonController extends Controller
{
    public function __construct(
        private readonly UzgolonService $uzgolonService,
        private readonly CommentService $commentService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('qozgolonlar.index', [
            'items' => $this->uzgolonService->paginateForPublic($request),
            'regions' => Region::orderBy('name')->get(),
            'periods' => Period::orderBy('start_year')->get(),
            'filters' => $request->only(['q', 'region', 'period']),
            'seo' => [
                'title' => "Qo'zg'olonlar — Qo'rboshilar.uz",
                'description' => "Turkiston tarixidagi muhim qo'zg'olonlar ro'yxati.",
                'canonical' => route('qozgolonlar.index'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $uzgolon = $this->uzgolonService->findPublishedBySlugOrFail($slug);

        return view('qozgolonlar.show', [
            'uzgolon' => $uzgolon,
            'related' => $this->uzgolonService->relatedTo($uzgolon),
            'comments' => $this->commentService->approvedFor($uzgolon),
            'seo' => [
                'title' => "{$uzgolon->name} — Qo'rboshilar.uz",
                'description' => $uzgolon->meta_description ?? $uzgolon->short_description,
                'canonical' => route('qozgolonlar.show', $uzgolon),
                'image' => $uzgolon->og_image ?? $uzgolon->coverImageUrl(),
            ],
        ]);
    }
}
