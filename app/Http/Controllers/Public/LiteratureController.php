<?php

namespace App\Http\Controllers\Public;

use App\Enums\LiteratureType;
use App\Http\Controllers\Controller;
use App\Services\CommentService;
use App\Services\LiteratureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LiteratureController extends Controller
{
    public function __construct(
        private readonly LiteratureService $literatureService,
        private readonly CommentService $commentService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('adabiyotlar.index', [
            'items' => $this->literatureService->paginateForPublic($request),
            'types' => LiteratureType::cases(),
            'filters' => $request->only(['q', 'type']),
            'seo' => [
                'title' => "Adabiyotlar — Qo'rboshilar.uz",
                'description' => "Turkiston tarixiga oid kitoblar, maqolalar va arxiv hujjatlari.",
                'canonical' => route('adabiyotlar.index'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $literature = $this->literatureService->findPublishedBySlugOrFail($slug);

        return view('adabiyotlar.show', [
            'literature' => $literature,
            'related' => $this->literatureService->relatedTo($literature),
            'comments' => $this->commentService->approvedFor($literature),
            'seo' => [
                'title' => "{$literature->title} — Qo'rboshilar.uz",
                'description' => $literature->meta_description ?? $literature->description,
                'canonical' => route('adabiyotlar.show', $literature),
                'image' => $literature->og_image ?? $literature->coverUrl(),
            ],
        ]);
    }
}
