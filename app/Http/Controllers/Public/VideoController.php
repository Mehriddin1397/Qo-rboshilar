<?php

namespace App\Http\Controllers\Public;

use App\Enums\VideoCategory;
use App\Http\Controllers\Controller;
use App\Services\CommentService;
use App\Services\VideoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        private readonly VideoService $videoService,
        private readonly CommentService $commentService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('videolar.index', [
            'items' => $this->videoService->paginateForPublic($request),
            'categories' => VideoCategory::cases(),
            'filters' => $request->only(['q', 'category']),
            'seo' => [
                'title' => "Videolar — Qo'rboshilar.uz",
                'description' => "Qo'rboshilar va qo'zg'olonlar haqidagi videolar.",
                'canonical' => route('videolar.index'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $video = $this->videoService->findPublishedBySlugOrFail($slug);

        return view('videolar.show', [
            'video' => $video,
            'related' => $this->videoService->relatedTo($video),
            'comments' => $this->commentService->approvedFor($video),
            'seo' => [
                'title' => "{$video->title} — Qo'rboshilar.uz",
                'description' => $video->meta_description ?? $video->description,
                'canonical' => route('videolar.show', $video),
                'image' => $video->thumbnailUrl(),
            ],
        ]);
    }
}
