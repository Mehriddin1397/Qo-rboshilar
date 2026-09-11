<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use App\Services\CommentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService,
        private readonly CommentService $commentService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('bloglar.index', [
            'items' => $this->blogService->paginateForPublic($request),
            'filters' => $request->only(['q']),
            'seo' => [
                'title' => "Bloglar — Qo'rboshilar.uz",
                'description' => "Foydalanuvchilar tomonidan yozilgan tarixiy maqolalar.",
                'canonical' => route('bloglar.index'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $blog = $this->blogService->findPublishedBySlugOrFail($slug);
        $this->blogService->incrementViews($blog);

        return view('bloglar.show', [
            'blog' => $blog,
            'related' => $this->blogService->relatedTo($blog),
            'comments' => $this->commentService->approvedFor($blog),
            'seo' => [
                'title' => "{$blog->title} — Qo'rboshilar.uz",
                'description' => $blog->meta_description ?? $blog->excerpt,
                'canonical' => route('bloglar.show', $blog),
                'image' => $blog->og_image ?? $blog->coverUrl(),
            ],
        ]);
    }
}
