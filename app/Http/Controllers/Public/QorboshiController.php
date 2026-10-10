<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Services\CommentService;
use App\Services\QorboshiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class QorboshiController extends Controller
{
    public function __construct(
        private readonly QorboshiService $qorboshiService,
        private readonly CommentService $commentService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('qorboshilar.index', [
            'items' => $this->qorboshiService->paginateForPublic($request),
            'regions' => Region::orderBy('name')->get(),
            'filters' => $request->only(['q', 'region']),
            'seo' => [
                'title' => "Qo'rboshilar — Turkiston istiqlolchilik harakati yetakchilari va sarkardalari",
                'description' => "Turkiston ozodligi yo'lida kurashgan qo'rboshilar va yetakchilarning to'liq biografiyasi, faoliyat yillari va tarixiy hududlari bo'yicha ilmiy katalog.",
                'canonical' => route('qorboshilar.index'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $qorboshi = $this->qorboshiService->findPublishedBySlugOrFail($slug);

        return view('qorboshilar.show', [
            'qorboshi' => $qorboshi,
            'related' => $this->qorboshiService->relatedTo($qorboshi),
            'comments' => $this->commentService->approvedFor($qorboshi),
            'seo' => [
                'title' => "{$qorboshi->full_name} — Qo'rboshilar.uz",
                'description' => \Illuminate\Support\Str::limit($qorboshi->meta_description ?? $qorboshi->short_description, 160),
                'canonical' => route('qorboshilar.show', $qorboshi),
                'image' => $qorboshi->og_image ?? ($qorboshi->portrait_path ? $qorboshi->portraitUrl() : null),
            ],
        ]);
    }
}
